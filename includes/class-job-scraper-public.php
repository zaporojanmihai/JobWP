<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and public-facing hooks.
 */
class Job_Scraper_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, JOB_SCRAPER_PLUGIN_URL . 'assets/css/job-scraper-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, JOB_SCRAPER_PLUGIN_URL . 'assets/js/job-scraper-public.js', array('jquery'), $this->version, false);
    }
    
    /**
     * Shortcode to display job listings on public-facing pages.
     *
     * @since    1.0.0
     */
    public function display_job_listings_shortcode($atts) {
        // Parse atts
        $atts = shortcode_atts(array(
            'title' => 'Job Listings',
            'count' => 10,
            'source' => 'all', // all, linkedin
            'keywords' => '',
            'date_range' => 'all', // all, day, week, month
            'location' => 'all'  // all, or specific location name
        ), $atts);
        
        // Get options
        $options = get_option('job_scraper_options');
        $per_page = isset($options['results_per_page']) ? absint($options['results_per_page']) : 10;
        
        // Override per_page if count attribute is set
        if (!empty($atts['count'])) {
            $per_page = absint($atts['count']);
        }
        
        // Current page
        $current_page = max(1, get_query_var('paged'));
        
        // Get jobs from database
        global $wpdb;
        $table_name = $wpdb->prefix . 'job_scraper_jobs';
        
        // Build query
        $sql = "SELECT * FROM $table_name WHERE 1=1";
        
        // Filter by source if specified
        if (!empty($atts['source']) && $atts['source'] !== 'all') {
            $sql .= $wpdb->prepare(" AND source = %s", $atts['source']);
        }
        
        // Filter by keywords if specified
        if (!empty($atts['keywords'])) {
            $keywords = explode(',', $atts['keywords']);
            if (!empty($keywords)) {
                $sql .= " AND (";
                $keyword_conditions = array();
                foreach ($keywords as $keyword) {
                    $keyword = trim($keyword);
                    $keyword_conditions[] = $wpdb->prepare("title LIKE %s OR description LIKE %s", 
                        '%' . $wpdb->esc_like($keyword) . '%',
                        '%' . $wpdb->esc_like($keyword) . '%'
                    );
                }
                $sql .= implode(" OR ", $keyword_conditions);
                $sql .= ")";
            }
        }
        
        // Filter by date if specified
        if (!empty($atts['date_range']) && $atts['date_range'] !== 'all') {
            $date_filter = '';
            switch ($atts['date_range']) {
                case 'day':
                    $date_filter = $wpdb->prepare(" AND date_posted >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
                    break;
                case 'week':
                    $date_filter = $wpdb->prepare(" AND date_posted >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)");
                    break;
                case 'month':
                    $date_filter = $wpdb->prepare(" AND date_posted >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)");
                    break;
            }
            $sql .= $date_filter;
        }
        
        // Filter by location if specified
        if (!empty($atts['location']) && $atts['location'] !== 'all') {
            // For exact country matching
            $sql .= $wpdb->prepare(" AND (location LIKE %s OR location LIKE %s)", 
                $atts['location'] . '%',
                '%, ' . $atts['location'] . '%'
            );
        }
        
        // Order by date posted descending
        $sql .= " ORDER BY date_posted DESC";
        
        // Add pagination
        $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $per_page, ($current_page - 1) * $per_page);
        
        // Get results
        $jobs = $wpdb->get_results($sql);
        
        // Count total jobs for pagination
        $count_sql = "SELECT COUNT(*) FROM $table_name WHERE 1=1";
        
        // Filter by source if specified
        if (!empty($atts['source']) && $atts['source'] !== 'all') {
            $count_sql .= $wpdb->prepare(" AND source = %s", $atts['source']);
        }
        
        // Filter by keywords if specified
        if (!empty($atts['keywords'])) {
            $keywords = explode(',', $atts['keywords']);
            if (!empty($keywords)) {
                $count_sql .= " AND (";
                $keyword_conditions = array();
                foreach ($keywords as $keyword) {
                    $keyword = trim($keyword);
                    $keyword_conditions[] = $wpdb->prepare("title LIKE %s", '%' . $wpdb->esc_like($keyword) . '%');
                }
                $count_sql .= implode(' OR ', $keyword_conditions);
                $count_sql .= ")";
            }
        }
        
        $total_jobs = $wpdb->get_var($count_sql);
        $total_pages = ceil($total_jobs / $per_page);
        
        // Start output buffering
        ob_start();
        
        // Include the template
        include JOB_SCRAPER_PLUGIN_DIR . 'includes/partials/job-scraper-public-display.php';
        
        // Return the buffered content
        return ob_get_clean();
    }
} 