<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="job-scraper-diagnostics">
        <h2>Job Scraper Diagnostics</h2>
        
        <?php
        // Check prerequisites
        $prerequisites = array(
            'curl_enabled' => function_exists('curl_init'),
            'dom_enabled' => class_exists('DOMDocument'),
            'wp_cron_enabled' => !defined('DISABLE_WP_CRON') || !DISABLE_WP_CRON,
            'debug_log_writable' => is_writable(WP_CONTENT_DIR . '/debug.log') || is_writable(WP_CONTENT_DIR),
            'uploads_writable' => is_writable(WP_CONTENT_DIR . '/uploads') || !file_exists(WP_CONTENT_DIR . '/uploads')
        );
        
        // Check database table
        global $wpdb;
        $table_name = $wpdb->prefix . 'job_scraper_jobs';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        // Get job counts
        $total_jobs = 0;
        $linkedin_jobs = 0;
        
        if ($table_exists) {
            $total_jobs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            $linkedin_jobs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE source = 'linkedin'");
        }
        
        // Get plugin options
        $options = get_option('job_scraper_options');
        ?>
        
        <div class="job-scraper-diagnostics-section">
            <h3>System Requirements</h3>
            <table class="widefat" style="width: auto;">
                <thead>
                    <tr>
                        <th>Requirement</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>cURL Enabled</td>
                        <td>
                            <?php if ($prerequisites['curl_enabled']): ?>
                                <span style="color: green;">✓ Enabled</span>
                            <?php else: ?>
                                <span style="color: red;">✗ Disabled - cURL is required for scraping</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>DOM Extension</td>
                        <td>
                            <?php if ($prerequisites['dom_enabled']): ?>
                                <span style="color: green;">✓ Enabled</span>
                            <?php else: ?>
                                <span style="color: red;">✗ Disabled - DOM is required for parsing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>WordPress Cron</td>
                        <td>
                            <?php if ($prerequisites['wp_cron_enabled']): ?>
                                <span style="color: green;">✓ Enabled</span>
                            <?php else: ?>
                                <span style="color: orange;">⚠ Disabled - automatic scraping won't work</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Debug Log Writable</td>
                        <td>
                            <?php if ($prerequisites['debug_log_writable']): ?>
                                <span style="color: green;">✓ Writable</span>
                            <?php else: ?>
                                <span style="color: orange;">⚠ Not writable - debug logs may not be saved</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Uploads Directory Writable</td>
                        <td>
                            <?php if ($prerequisites['uploads_writable']): ?>
                                <span style="color: green;">✓ Writable</span>
                            <?php else: ?>
                                <span style="color: orange;">⚠ Not writable - debug HTML files may not be saved</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="job-scraper-diagnostics-section">
            <h3>Database Status</h3>
            <table class="widefat" style="width: auto;">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Jobs Table Exists</td>
                        <td>
                            <?php if ($table_exists): ?>
                                <span style="color: green;">✓ Exists</span>
                            <?php else: ?>
                                <span style="color: red;">✗ Missing - plugin activation may have failed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Total Jobs</td>
                        <td><?php echo esc_html($total_jobs); ?></td>
                    </tr>
                    <tr>
                        <td>LinkedIn Jobs</td>
                        <td><?php echo esc_html($linkedin_jobs); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="job-scraper-diagnostics-section">
            <h3>Plugin Configuration</h3>
            <table class="widefat" style="width: auto;">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Keywords</td>
                        <td><?php echo esc_html($options['keywords']); ?></td>
                    </tr>
                    <tr>
                        <td>LinkedIn Enabled</td>
                        <td>
                            <?php if (isset($options['linkedin_enabled']) && $options['linkedin_enabled']): ?>
                                <span style="color: green;">✓ Yes</span>
                            <?php else: ?>
                                <span style="color: orange;">✗ No</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Date Filter</td>
                        <td>
                            <?php 
                            $date_filter = isset($options['date_filter']) ? $options['date_filter'] : 'all';
                            $date_filter_text = '';
                            switch ($date_filter) {
                                case 'all':
                                    $date_filter_text = 'All Time';
                                    break;
                                case 'day':
                                    $date_filter_text = 'Last 24 Hours';
                                    break;
                                case 'week':
                                    $date_filter_text = 'Last Week';
                                    break;
                                case 'month':
                                    $date_filter_text = 'Last Month';
                                    break;
                            }
                            echo esc_html($date_filter_text);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Locations</td>
                        <td>
                            <?php 
                            $locations = isset($options['locations']) && is_array($options['locations']) ? $options['locations'] : array('us');
                            
                            $location_names = array(
                                'us' => 'United States',
                                'uk' => 'United Kingdom',
                                'ca' => 'Canada',
                                'de' => 'Germany',
                                'fr' => 'France',
                                'es' => 'Spain',
                                'eu' => 'Europe (General)',
                                'nl' => 'Netherlands',
                                'be' => 'Belgium',
                                'pt' => 'Portugal',
                                'it' => 'Italy',
                                'at' => 'Austria'
                            );
                            
                            $location_texts = array();
                            foreach ($locations as $location_code) {
                                if (isset($location_names[$location_code])) {
                                    $location_texts[] = $location_names[$location_code];
                                }
                            }
                            
                            echo esc_html(implode(', ', $location_texts));
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="job-scraper-diagnostics-section">
            <h3>Run Test Scraper</h3>
            <p>Try to scrape a single job source with a specific keyword to diagnose issues.</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('job_scraper_test_scrape', 'job_scraper_test_nonce'); ?>
                
                <select name="test_source">
                    <option value="linkedin">LinkedIn</option>
                </select>
                
                <input type="text" name="test_keyword" placeholder="Enter a test keyword" value="" />
                
                <select name="test_date_filter">
                    <option value="all">All Time</option>
                    <option value="day">Last 24 Hours</option>
                    <option value="week">Last Week</option>
                    <option value="month">Last Month</option>
                </select>
                
                <select name="test_location">
                    <option value="us">United States</option>
                    <option value="uk">United Kingdom</option>
                    <option value="ca">Canada</option>
                    <option value="de">Germany</option>
                    <option value="fr">France</option>
                    <option value="es">Spain</option>
                    <option value="eu">Europe (General)</option>
                    <option value="at">Austria</option>
                    <option value="be">Belgium</option>
                    <option value="dk">Denmark</option>
                    <option value="fi">Finland</option>
                    <option value="is">Iceland</option>
                    <option value="it">Italy</option>
                    <option value="nl">Netherlands</option>
                    <option value="no">Norway</option>
                    <option value="pt">Portugal</option>
                    <option value="ro">Romania</option>
                    <option value="se">Sweden</option>
                </select>
                
                <input type="submit" name="run_test_scrape" class="button button-primary" value="Run Test Scrape" />
            </form>
            
            <?php
            // Handle test scrape
            if (isset($_POST['run_test_scrape']) && isset($_POST['job_scraper_test_nonce']) && wp_verify_nonce($_POST['job_scraper_test_nonce'], 'job_scraper_test_scrape')) {
                $test_source = sanitize_text_field($_POST['test_source']);
                $test_keyword = sanitize_text_field($_POST['test_keyword']);
                $test_date_filter = sanitize_text_field($_POST['test_date_filter']);
                $test_location = sanitize_text_field($_POST['test_location']);
                
                // Enable error logging to debug.log
                if (!defined('WP_DEBUG')) {
                    define('WP_DEBUG', true);
                }
                if (!defined('WP_DEBUG_LOG')) {
                    define('WP_DEBUG_LOG', true);
                }
                
                // Save original locations
                $options = get_option('job_scraper_options');
                $original_locations = isset($options['locations']) ? $options['locations'] : array('us');
                
                // Temporarily set only the test location
                $options['locations'] = array($test_location);
                update_option('job_scraper_options', $options);
                
                echo '<div class="job-scraper-test-results">';
                echo '<h4>Test Results for ' . esc_html($test_source) . ' with keyword "' . esc_html($test_keyword) . '", date filter "' . esc_html($test_date_filter) . '", and location "' . esc_html($test_location) . '"</h4>';
                
                // Run the scraper
                $scraper_class = 'Job_Scraper_' . ucfirst($test_source);
                if (class_exists($scraper_class)) {
                    $scraper = new $scraper_class();
                    $start_time = microtime(true);
                    $jobs = $scraper->scrape(array($test_keyword), $test_date_filter);
                    $execution_time = microtime(true) - $start_time;
                    
                    // Restore original locations
                    $options['locations'] = $original_locations;
                    update_option('job_scraper_options', $options);
                    
                    echo '<p>Execution time: ' . round($execution_time, 2) . ' seconds</p>';
                    echo '<p>Jobs found: ' . count($jobs) . '</p>';
                    
                    if (count($jobs) > 0) {
                        echo '<table class="widefat">';
                        echo '<thead><tr><th>Title</th><th>Company</th><th>Location</th></tr></thead>';
                        echo '<tbody>';
                        foreach ($jobs as $job) {
                            echo '<tr>';
                            echo '<td>' . esc_html($job['title']) . '</td>';
                            echo '<td>' . esc_html($job['company']) . '</td>';
                            echo '<td>' . esc_html($job['location']) . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                    } else {
                        echo '<p>No jobs found. Check the debug log for more information.</p>';
                        
                        // Display debug directory location
                        $debug_dir = WP_CONTENT_DIR . '/uploads/job-scraper-debug';
                        if (file_exists($debug_dir)) {
                            echo '<p>Check debug HTML files in: ' . esc_html($debug_dir) . '</p>';
                        }
                    }
                    
                    if ($test_location !== 'all'): ?>
                    <p><strong>Location Information:</strong></p>
                    <p>
                        Searching for jobs in: <code><?php echo esc_html($test_location); ?></code><br>
                        URL parameter used: <code><?php 
                            $location_params = array(
                                'us' => 'geoId=103644278&location=United%20States',
                                'uk' => 'geoId=101165590&location=United%20Kingdom',
                                'ca' => 'geoId=101174742&location=Canada',
                                'de' => 'geoId=101282230&location=Germany',
                                'fr' => 'geoId=105015875&location=France',
                                'es' => 'geoId=105646813&location=Spain',
                                'eu' => 'geoId=91000000&location=Europe',
                                'ro' => 'geoId=106670623&location=Romania',
                                'se' => 'geoId=105117694&location=Sweden',
                                'no' => 'geoId=103819153&location=Norway',
                                'fi' => 'geoId=100456013&location=Finland',
                                'dk' => 'geoId=104514075&location=Denmark',
                                'is' => 'geoId=103819154&location=Iceland'
                            );
                            echo esc_html($location_params[$test_location] ?? 'Unknown location');
                        ?></code>
                    </p>
                    <?php endif;
                } else {
                    echo '<p>Error: Scraper class not found!</p>';
                }
                echo '</div>';
            }
            ?>
        </div>
        
        <div class="job-scraper-diagnostics-section">
            <h3>Common Issues</h3>
            <dl>
                <dt><strong>No jobs being scraped</strong></dt>
                <dd>
                    <ul>
                        <li>Job sites may have updated their HTML structure, breaking the scraper</li>
                        <li>Your server IP might be blocked by the job sites</li>
                        <li>Try using a proxy service for scraping (requires code customization)</li>
                        <li>Consider using official APIs instead of scraping when available</li>
                    </ul>
                </dd>
                
                <dt><strong>Connection errors</strong></dt>
                <dd>
                    <ul>
                        <li>Your hosting provider might block outbound connections</li>
                        <li>The job sites might have rate-limiting in place</li>
                        <li>Check the debug log for specific cURL error messages</li>
                    </ul>
                </dd>
                
                <dt><strong>Parse errors</strong></dt>
                <dd>
                    <ul>
                        <li>The HTML structure of the job sites may have changed</li>
                        <li>You may need to update the XPath selectors in the scraper classes</li>
                        <li>Check the debug HTML files to see what the actual HTML structure is</li>
                    </ul>
                </dd>
            </dl>
        </div>
    </div>
</div>

<style>
.job-scraper-diagnostics-section {
    margin-bottom: 30px;
    padding: 15px;
    background: #f9f9f9;
    border: 1px solid #e5e5e5;
}

.job-scraper-test-results {
    margin-top: 20px;
    padding: 15px;
    background: #fff;
    border: 1px solid #ccc;
}
</style> 