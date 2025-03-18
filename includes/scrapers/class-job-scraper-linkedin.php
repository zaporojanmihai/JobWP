<?php
/**
 * LinkedIn job scraper.
 *
 * This class handles scraping job listings from LinkedIn.
 */
class Job_Scraper_LinkedIn {

    /**
     * Base URL for LinkedIn job search.
     *
     * @var string
     */
    private $base_url = 'https://www.linkedin.com/jobs/search/?keywords=';

    /**
     * Constructor.
     */
    public function __construct() {
        // Add any initialization here
    }

    /**
     * Scrape LinkedIn for jobs
     *
     * @param array $keywords Array of keywords to search for
     * @param string $date_filter Date filter (day, week, month, all)
     * @return array Array of jobs
     */
    public function scrape($keywords, $date_filter = 'all') {
        // Get options
        $options = get_option('job_scraper_options');
        
        // Get results per page
        $results_per_page = isset($options['results_per_page']) ? intval($options['results_per_page']) : 25;
        
        // Get locations from options
        $locations = isset($options['locations']) && is_array($options['locations']) ? $options['locations'] : array('us');
        
        error_log('LinkedIn Scraper: Starting scrape with locations: ' . implode(', ', $locations));
        
        // Map location codes to geoId parameters for LinkedIn
        $location_params = array(
            'us' => array('geoId' => '103644278', 'name' => 'United States'),
            'uk' => array('geoId' => '101165590', 'name' => 'United Kingdom'),
            'ca' => array('geoId' => '101174742', 'name' => 'Canada'),
            'de' => array('geoId' => '101282230', 'name' => 'Germany'),
            'fr' => array('geoId' => '105015875', 'name' => 'France'),
            'es' => array('geoId' => '105646813', 'name' => 'Spain'),
            'eu' => array('geoId' => '91000000', 'name' => 'Europe'),
            'ro' => array('geoId' => '106670623', 'name' => 'Romania'),
            'se' => array('geoId' => '105117694', 'name' => 'Sweden'),
            'no' => array('geoId' => '103819153', 'name' => 'Norway'),
            'fi' => array('geoId' => '100456013', 'name' => 'Finland'),
            'dk' => array('geoId' => '104514075', 'name' => 'Denmark'),
            'is' => array('geoId' => '103819154', 'name' => 'Iceland'),
            'nl' => array('geoId' => '107413002', 'name' => 'Netherlands'),
            'be' => array('geoId' => '107413003', 'name' => 'Belgium'),
            'pt' => array('geoId' => '107413004', 'name' => 'Portugal'),
            'it' => array('geoId' => '107413005', 'name' => 'Italy'),
            'at' => array('geoId' => '107413006', 'name' => 'Austria')
        );
        
        // Initialize jobs array
        $all_jobs = array();
        
        // Loop through keywords and locations
        foreach ($keywords as $keyword) {
            foreach ($locations as $location_code) {
                // Skip if location code doesn't exist
                if (!isset($location_params[$location_code])) {
                    error_log("LinkedIn Scraper: Invalid location code: {$location_code}. Skipping.");
                    continue;
                }
                
                $location_param = $location_params[$location_code];
                error_log("LinkedIn Scraper: Searching for '{$keyword}' in {$location_param['name']} (geoId: {$location_param['geoId']})");
                
                // Build search URL with location
                $search_url = "https://www.linkedin.com/jobs/search/?keywords=" . urlencode($keyword) . "&geoId=" . $location_param['geoId'] . "&location=" . urlencode($location_param['name']) . "&f_TPR=";
                
                // Add date filter if specified
                if ($date_filter !== 'all') {
                    switch ($date_filter) {
                        case 'day':
                            $search_url .= 'r86400';
                            break;
                        case 'week':
                            $search_url .= 'r604800';
                            break;
                        case 'month':
                            $search_url .= 'r2592000';
                            break;
                    }
                }
                
                // Log the URL for debugging
                error_log("LinkedIn Scraper: Search URL: {$search_url}");
                
                // Make request
                $response = wp_remote_get($search_url, array(
                    'timeout' => 30,
                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                ));
                
                // Check for errors
                if (is_wp_error($response)) {
                    error_log('LinkedIn Scraper: Error fetching jobs - ' . $response->get_error_message());
                    continue;
                }
                
                // Get response body
                $html = wp_remote_retrieve_body($response);
                
                // Save HTML to debug file
                $debug_dir = WP_CONTENT_DIR . '/job-scraper-debug';
                if (!file_exists($debug_dir)) {
                    mkdir($debug_dir, 0755, true);
                }
                
                $debug_file = $debug_dir . '/linkedin-' . sanitize_title($keyword) . '-' . $location_code . '.html';
                file_put_contents($debug_file, $html);
                
                error_log("LinkedIn Scraper: Response length: " . strlen($html) . " bytes saved to {$debug_file}");
                
                // Parse HTML
                $jobs = $this->parse_html($html, $location_code);
                
                // Log results
                $jobs_count = count($jobs);
                if ($jobs_count > 0) {
                    error_log("LinkedIn Scraper: Found {$jobs_count} jobs for '{$keyword}' in {$location_param['name']}");
                } else {
                    error_log("LinkedIn Scraper: No jobs found for '{$keyword}' in {$location_param['name']}");
                }
                
                // Add jobs to all jobs
                $all_jobs = array_merge($all_jobs, $jobs);
            }
        }
        
        // Return all jobs
        return $all_jobs;
    }

    /**
     * Parse HTML to extract job listings.
     *
     * @param string $html The HTML to parse.
     * @param string $location_code The location code used for this search.
     * @return array Array of job listings.
     */
    private function parse_html($html, $location_code = 'us') {
        $jobs = array();
        
        // Create a new DOMDocument
        $dom = new DOMDocument();
        
        // Suppress errors for malformed HTML
        libxml_use_internal_errors(true);
        
        // Load the HTML
        $dom->loadHTML($html);
        
        // Create a new DOMXPath object
        $xpath = new DOMXPath($dom);
        
        // Define location names for verification
        $location_names = array(
            'us' => array('United States', 'USA', 'U.S.', 'US', 'United States of America', 'Remote, United States'),
            'uk' => array('United Kingdom', 'UK', 'England', 'Scotland', 'Wales', 'Northern Ireland', 'Great Britain', 'London', 'Remote, United Kingdom'),
            'ca' => array('Canada', 'CA', 'Toronto', 'Vancouver', 'Montreal', 'Remote, Canada'),
            'de' => array('Germany', 'Deutschland', 'DE', 'Berlin', 'Munich', 'Frankfurt', 'Remote, Germany'),
            'fr' => array('France', 'FR', 'Paris', 'Lyon', 'Marseille', 'Remote, France'),
            'es' => array('Spain', 'España', 'ES', 'Madrid', 'Barcelona', 'Valencia', 'Remote, Spain'),
            'eu' => array('Europe', 'European Union', 'EU', 'Remote, Europe'),
            'ro' => array('Romania', 'RO', 'Bucharest', 'București', 'Cluj', 'Iasi', 'Iași', 'Timisoara', 'Timișoara', 'Remote, Romania'),
            'se' => array('Sweden', 'SE', 'Stockholm', 'Gothenburg', 'Göteborg', 'Malmö', 'Malmo', 'Remote, Sweden'),
            'no' => array('Norway', 'NO', 'Oslo', 'Bergen', 'Trondheim', 'Remote, Norway'),
            'fi' => array('Finland', 'FI', 'Helsinki', 'Espoo', 'Tampere', 'Remote, Finland'),
            'dk' => array('Denmark', 'DK', 'Copenhagen', 'København', 'Aarhus', 'Århus', 'Odense', 'Remote, Denmark'),
            'is' => array('Iceland', 'IS', 'Reykjavik', 'Reykjavík', 'Remote, Iceland'),
            'nl' => array('Netherlands', 'Holland', 'NL', 'Amsterdam', 'Rotterdam', 'The Hague', 'Utrecht', 'Eindhoven', 'Remote, Netherlands'),
            'be' => array('Belgium', 'BE', 'Brussels', 'Bruxelles', 'Antwerp', 'Antwerpen', 'Ghent', 'Gent', 'Bruges', 'Brugge', 'Remote, Belgium'),
            'pt' => array('Portugal', 'PT', 'Lisbon', 'Lisboa', 'Porto', 'Braga', 'Coimbra', 'Remote, Portugal'),
            'it' => array('Italy', 'Italia', 'IT', 'Rome', 'Roma', 'Milan', 'Milano', 'Naples', 'Napoli', 'Turin', 'Torino', 'Remote, Italy'),
            'at' => array('Austria', 'AT', 'Vienna', 'Wien', 'Graz', 'Linz', 'Salzburg', 'Innsbruck', 'Remote, Austria')
        );
        
        // Find job listings
        $job_cards = $xpath->query('//div[contains(@class, "job-search-card")]');
        error_log('LinkedIn Scraper: Found ' . ($job_cards ? $job_cards->length : 0) . ' job cards for location: ' . $location_code);
        
        // Try alternative selectors if no jobs found
        if (!$job_cards || $job_cards->length === 0) {
            error_log('LinkedIn Scraper: No job cards found with primary selector, trying alternative...');
            $job_cards = $xpath->query('//li[contains(@class, "jobs-search-results__list-item")]');
            error_log('LinkedIn Scraper: Found ' . ($job_cards ? $job_cards->length : 0) . ' job cards with alternative selector');
        }
        
        // Process job cards if found
        if ($job_cards && $job_cards->length > 0) {
            $index = 0;
            $jobs_skipped = 0;
            
            foreach ($job_cards as $card) {
                $index++;
                
                // Extract job data
                $job_id = '';
                $data_id = $card->getAttribute('data-id');
                $data_job_id = $card->getAttribute('data-job-id');
                $entity_urn = $card->getAttribute('data-entity-urn');
                
                // Try to extract job_id from various attributes
                if (!empty($data_id)) {
                    $job_id = 'linkedin_' . $data_id;
                } elseif (!empty($data_job_id)) {
                    $job_id = 'linkedin_' . $data_job_id;
                } elseif (!empty($entity_urn)) {
                    // Extract job ID from entity URN
                    preg_match('/urn:li:fs_normalized_jobPosting:(\d+)/', $entity_urn, $matches);
                    if (!empty($matches[1])) {
                        $job_id = 'linkedin_' . $matches[1];
                    }
                }
                
                // If still no job_id, generate one
                if (empty($job_id)) {
                    $job_id = 'linkedin_' . md5($location_code . '_' . $index . '_' . time());
                    error_log('LinkedIn Scraper: Generated job_id for job: ' . $job_id);
                }
                
                // Extract title
                $title_node = $xpath->query('.//h3[contains(@class, "base-search-card__title")]', $card)->item(0);
                if (!$title_node) {
                    $title_node = $xpath->query('.//h3[contains(@class, "job-search-card__title")]', $card)->item(0);
                }
                $title = $title_node ? trim($title_node->textContent) : 'Unknown Title';
                
                // Extract company
                $company_node = $xpath->query('.//h4[contains(@class, "base-search-card__subtitle")]', $card)->item(0);
                if (!$company_node) {
                    $company_node = $xpath->query('.//a[contains(@class, "job-search-card__subtitle-link")]', $card)->item(0);
                }
                $company = $company_node ? trim($company_node->textContent) : 'Unknown Company';
                
                // Extract location
                $location_node = $xpath->query('.//span[contains(@class, "job-search-card__location")]', $card)->item(0);
                $location = $location_node ? trim($location_node->textContent) : 'Unknown Location';
                
                // Verify location matches the specified country
                $location_valid = false;
                
                // Check if we care about location validation for Europe (might include multiple countries)
                if ($location_code === 'eu') {
                    // For Europe, check if it's in any European country
                    $european_locations = array_merge(
                        $location_names['uk'],
                        $location_names['de'],
                        $location_names['fr'],
                        $location_names['es'],
                        $location_names['ro'],
                        $location_names['se'],
                        $location_names['no'],
                        $location_names['fi'],
                        $location_names['dk'],
                        $location_names['is'],
                        $location_names['nl'],
                        $location_names['be'],
                        $location_names['pt'],
                        $location_names['it'],
                        $location_names['at'],
                        $location_names['eu']
                    );
                    
                    foreach ($european_locations as $valid_location) {
                        if (stripos($location, $valid_location) !== false) {
                            $location_valid = true;
                            break;
                        }
                    }
                } else {
                    // For specific countries, check against their location names
                    if (isset($location_names[$location_code])) {
                        foreach ($location_names[$location_code] as $valid_location) {
                            if (stripos($location, $valid_location) !== false) {
                                $location_valid = true;
                                break;
                            }
                        }
                    }
                }
                
                // Skip job if location doesn't match expected location
                if (!$location_valid) {
                    error_log("LinkedIn Scraper: Skipping job '$title' with location '$location' (doesn't match $location_code)");
                    $jobs_skipped++;
                    continue;
                }
                
                // Extract URL
                $url_node = $xpath->query('.//a[contains(@class, "base-card__full-link")]', $card)->item(0);
                if (!$url_node) {
                    $url_node = $xpath->query('.//a[contains(@class, "job-search-card__link")]', $card)->item(0);
                }
                $url = $url_node ? $url_node->getAttribute('href') : '';
                
                // Clean up the URL if needed
                if (strpos($url, 'http') !== 0) {
                    $url = 'https://www.linkedin.com' . $url;
                }
                
                // Create job array
                $job = array(
                    'job_id' => $job_id,
                    'title' => $title,
                    'company' => $company,
                    'location' => $location,
                    'description' => 'View job on LinkedIn for full description.',
                    'url' => $url,
                    'source' => 'LinkedIn',
                    'date_posted' => date('Y-m-d H:i:s')
                );
                
                // Add job to jobs array
                $jobs[] = $job;
            }
            
            error_log("LinkedIn Scraper: Processed {$job_cards->length} job cards, added " . count($jobs) . " jobs, skipped $jobs_skipped jobs due to location mismatch");
        }
        
        // Reset error handling
        libxml_clear_errors();
        
        // Save jobs to database
        $this->save_jobs($jobs);
        
        return $jobs;
    }

    /**
     * Extract text from an element.
     *
     * @param DOMXPath $xpath The XPath object.
     * @param string $query The XPath query.
     * @param DOMNode $context The context node.
     * @return string The extracted text.
     */
    private function extract_text($xpath, $query, $context = null) {
        $nodes = $xpath->query($query, $context);
        
        if ($nodes && $nodes->length > 0) {
            return trim($nodes->item(0)->textContent);
        }
        
        return '';
    }

    /**
     * Extract an attribute from an element.
     *
     * @param DOMXPath $xpath The XPath object.
     * @param string $query The XPath query.
     * @param string $attribute The attribute to extract.
     * @param DOMNode $context The context node.
     * @return string The extracted attribute value.
     */
    private function extract_attribute($xpath, $query, $attribute, $context = null) {
        $nodes = $xpath->query($query, $context);
        
        if ($nodes && $nodes->length > 0) {
            return $nodes->item(0)->getAttribute($attribute);
        }
        
        return '';
    }

    /**
     * Fetch job details from the job page.
     *
     * @param string $url The job page URL.
     * @return array The job details.
     */
    private function fetch_job_details($url) {
        $details = array(
            'description' => '',
            'date_posted' => date('Y-m-d H:i:s')
        );
        
        // Get the HTML
        $response = $this->fetch_url($url);
        
        if ($response) {
            // Load HTML into a DOM document
            $dom = new DOMDocument();
            @$dom->loadHTML($response);
            $xpath = new DOMXPath($dom);
            
            // Extract job description
            $description = $this->extract_text($xpath, '//div[contains(@class, "description__text")]');
            if (!empty($description)) {
                $details['description'] = $description;
            }
            
            // Extract date posted
            $date_text = $this->extract_text($xpath, '//span[contains(@class, "posted-time")]');
            if (!empty($date_text)) {
                // Parse the date text, e.g., "Posted 3 days ago"
                $details['date_posted'] = $this->parse_date($date_text);
            }
        }
        
        return $details;
    }

    /**
     * Parse a date string into a MySQL datetime.
     *
     * @param string $date_text The date text to parse.
     * @return string The MySQL datetime.
     */
    private function parse_date($date_text) {
        $date = new DateTime();
        
        // Check for common patterns
        if (preg_match('/(\d+)\s+day/i', $date_text, $matches)) {
            $days = (int)$matches[1];
            $date->modify("-$days days");
        } elseif (preg_match('/(\d+)\s+hour/i', $date_text, $matches)) {
            $hours = (int)$matches[1];
            $date->modify("-$hours hours");
        } elseif (preg_match('/(\d+)\s+week/i', $date_text, $matches)) {
            $weeks = (int)$matches[1];
            $date->modify("-$weeks weeks");
        } elseif (preg_match('/(\d+)\s+month/i', $date_text, $matches)) {
            $months = (int)$matches[1];
            $date->modify("-$months months");
        } elseif (strpos($date_text, 'just now') !== false || strpos($date_text, 'moments ago') !== false) {
            // Keep the current date
        }
        
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Fetch the content of a URL.
     *
     * @param string $url The URL to fetch.
     * @return string|bool The content of the URL or false on failure.
     */
    private function fetch_url($url) {
        // Initialize cURL
        $ch = curl_init();
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Add more cURL options that might help bypass restrictions
        curl_setopt($ch, CURLOPT_ENCODING, '');  // Accept all encodings
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Skip SSL verification
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Cache-Control: max-age=0',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
        ));
        
        // Execute cURL session
        $response = curl_exec($ch);
        
        // Check for cURL errors
        if (curl_errno($ch)) {
            error_log('LinkedIn Scraper cURL Error: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        // Get HTTP status code
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        error_log('LinkedIn Scraper: HTTP status code: ' . $http_code);
        
        // Close cURL session
        curl_close($ch);
        
        // Check if response is valid (200 OK)
        if ($http_code != 200) {
            error_log('LinkedIn Scraper: Bad HTTP response code: ' . $http_code);
            return false;
        }
        
        return $response;
    }

    /**
     * Save jobs to the database.
     *
     * @param array $jobs The jobs to save.
     * @return int Number of new jobs saved
     */
    private function save_jobs($jobs) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'job_scraper_jobs';
        $count = 0;
        
        error_log('LinkedIn Scraper: Saving ' . count($jobs) . ' jobs to database');
        
        foreach ($jobs as $job) {
            // Check if job already exists
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE job_id = %s",
                $job['job_id']
            ));
            
            if (!$existing) {
                // Insert job
                $wpdb->insert(
                    $table_name,
                    array(
                        'job_id' => $job['job_id'],
                        'title' => $job['title'],
                        'company' => $job['company'],
                        'location' => $job['location'],
                        'description' => $job['description'],
                        'url' => $job['url'],
                        'source' => $job['source'],
                        'date_posted' => $job['date_posted'],
                        'applied' => 0
                    ),
                    array(
                        '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d'
                    )
                );
                
                if ($wpdb->insert_id) {
                    $count++;
                } else {
                    error_log('LinkedIn Scraper: Failed to insert job: ' . $job['title'] . ' - DB Error: ' . $wpdb->last_error);
                }
            } else {
                error_log('LinkedIn Scraper: Job already exists: ' . $job['title']);
            }
        }
        
        error_log('LinkedIn Scraper: Saved ' . $count . ' new jobs to database');
        return $count;
    }
} 