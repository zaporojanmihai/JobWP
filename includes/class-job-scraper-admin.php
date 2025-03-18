<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and admin-specific hooks.
 */
class Job_Scraper_Admin {

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
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, JOB_SCRAPER_PLUGIN_URL . 'assets/css/job-scraper-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, JOB_SCRAPER_PLUGIN_URL . 'assets/js/job-scraper-admin.js', array('jquery'), $this->version, false);
        
        // Localize the script with data for AJAX
        wp_localize_script($this->plugin_name, 'job_scraper_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('job_scraper_nonce')
        ));
    }
    
    /**
     * Add an options page under the Settings submenu
     *
     * @since  1.0.0
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __('Job Scraper Settings', 'job-scraper'),
            __('Job Scraper', 'job-scraper'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_setup_page'),
            'dashicons-admin-generic',
            65
        );
        
        add_submenu_page(
            $this->plugin_name,
            __('Settings', 'job-scraper'),
            __('Settings', 'job-scraper'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_setup_page')
        );
        
        add_submenu_page(
            $this->plugin_name,
            __('Job Listings', 'job-scraper'),
            __('Job Listings', 'job-scraper'),
            'manage_options',
            $this->plugin_name . '-listings',
            array($this, 'display_job_listings_page')
        );
        
        // Add diagnostics submenu
        add_submenu_page(
            $this->plugin_name,
            __('Diagnostics', 'job-scraper'),
            __('Diagnostics', 'job-scraper'),
            'manage_options',
            $this->plugin_name . '-diagnostics',
            array($this, 'display_diagnostics_page')
        );
        
        // Add shortcodes submenu
        add_submenu_page(
            $this->plugin_name,
            __('Shortcodes', 'job-scraper'),
            __('Shortcodes', 'job-scraper'),
            'manage_options',
            $this->plugin_name . '-shortcodes',
            array($this, 'display_shortcodes_page')
        );
    }

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */
    public function add_action_links($links) {
        $settings_link = array(
            '<a href="' . admin_url('admin.php?page=' . $this->plugin_name) . '">' . __('Settings', 'job-scraper') . '</a>',
        );
        return array_merge($settings_link, $links);
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_setup_page() {
        include_once JOB_SCRAPER_PLUGIN_DIR . 'includes/partials/job-scraper-admin-display.php';
    }
    
    /**
     * Render the job listings page
     *
     * @since    1.0.0
     */
    public function display_job_listings_page() {
        include_once JOB_SCRAPER_PLUGIN_DIR . 'includes/partials/job-scraper-admin-listings.php';
    }

    /**
     * Render the diagnostics page
     *
     * @since    1.0.0
     */
    public function display_diagnostics_page() {
        include_once JOB_SCRAPER_PLUGIN_DIR . 'includes/partials/job-scraper-admin-diagnostics.php';
    }

    /**
     * Render the shortcodes page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_shortcodes_page() {
        include_once JOB_SCRAPER_PLUGIN_DIR . 'includes/partials/job-scraper-admin-shortcodes.php';
    }

    /**
     * Update options
     *
     * @since    1.0.0
     */
    public function options_update() {
        register_setting(
            'job_scraper_options',  // Option group
            'job_scraper_options',  // Option name
            array($this, 'validate_options')  // Sanitize callback
        );
    }
    
    /**
     * Validate options
     *
     * @since    1.0.0
     */
    public function validate_options($input) {
        $valid = array();
        
        // Keywords
        $valid['keywords'] = sanitize_text_field($input['keywords']);
        
        // Toggle settings
        $valid['linkedin_enabled'] = (isset($input['linkedin_enabled']) && !empty($input['linkedin_enabled'])) ? 1 : 0;
        
        // Date filter
        $valid['date_filter'] = sanitize_text_field($input['date_filter']);
        
        // Locations
        $valid['locations'] = array();
        $allowed_locations = array('us', 'uk', 'ca', 'de', 'fr', 'es', 'eu', 'ro', 'se', 'no', 'fi', 'dk', 'is', 'nl', 'be', 'pt', 'it', 'at');
        
        if (isset($input['locations']) && is_array($input['locations'])) {
            foreach ($input['locations'] as $location) {
                if (in_array($location, $allowed_locations)) {
                    $valid['locations'][] = $location;
                }
            }
        }
        
        // If no locations selected, default to US
        if (empty($valid['locations'])) {
            $valid['locations'] = array('us');
        }
        
        // Results per page
        $valid['results_per_page'] = absint($input['results_per_page']);
        if ($valid['results_per_page'] < 1) {
            $valid['results_per_page'] = 10;
        }
        
        return $valid;
    }
    
    /**
     * Manual scrape action via AJAX
     *
     * @since    1.0.0
     */
    public function manual_scrape() {
        // Check nonce for security
        check_ajax_referer('job_scraper_nonce', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        // Get options
        $options = get_option('job_scraper_options');
        
        // Get keywords
        $keywords = explode(',', $options['keywords']);
        $keywords = array_map('trim', $keywords);
        
        // Get date filter
        $date_filter = isset($options['date_filter']) ? $options['date_filter'] : 'all';
        
        // Get selected locations
        $locations = isset($options['locations']) && is_array($options['locations']) ? $options['locations'] : array('us');
        
        // Log options for debugging
        error_log('Manual Scrape: Starting scrape with the following options:');
        error_log('Manual Scrape: Keywords: ' . implode(', ', $keywords));
        error_log('Manual Scrape: Date filter: ' . $date_filter);
        error_log('Manual Scrape: Locations: ' . implode(', ', $locations));
        
        // Track jobs count
        $jobs_count = 0;
        
        // Scrape LinkedIn
        if (isset($options['linkedin_enabled']) && $options['linkedin_enabled']) {
            $linkedin = new Job_Scraper_LinkedIn();
            $linkedin_jobs = $linkedin->scrape($keywords, $date_filter);
            $jobs_count += count($linkedin_jobs);
        }
        
        // Return response in the format expected by the JavaScript
        wp_send_json_success(array(
            'message' => 'Scrape Jobs Now completed.',
            'count' => $jobs_count,
            'locations' => implode(', ', $locations)
        ));
    }
    
    /**
     * Toggle job application status
     *
     * @since    1.0.0
     */
    public function toggle_application_status() {
        // Check nonce for security
        check_ajax_referer('job_scraper_nonce', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        // Get job ID and current status
        $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
        $current_status = isset($_POST['current_status']) ? intval($_POST['current_status']) : 0;
        
        if ($job_id <= 0) {
            wp_send_json_error('Invalid job ID');
        }
        
        // Toggle the status (if current is 1, set to 0; if current is 0, set to 1)
        $new_status = $current_status ? 0 : 1;
        
        // Update job application status in database
        global $wpdb;
        $table_name = $wpdb->prefix . 'job_scraper_jobs';
        
        // Ensure the applied column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'applied'",
            DB_NAME,
            $table_name
        ));
        
        // If applied column doesn't exist, add it
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN applied TINYINT(1) NOT NULL DEFAULT 0");
        }
        
        $result = $wpdb->update(
            $table_name,
            array('applied' => $new_status),
            array('id' => $job_id),
            array('%d'),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error('Database error: ' . $wpdb->last_error);
        } else {
            // Set appropriate button text based on new status
            $button_text = $new_status ? __('Mark as Not Applied', 'job-scraper') : __('Mark as Applied', 'job-scraper');
            
            wp_send_json_success(array(
                'job_id' => $job_id,
                'new_status' => $new_status,
                'button_text' => $button_text
            ));
        }
    }
    
    /**
     * Clear all job listings from the database
     *
     * @since    1.0.0
     */
    public function clear_jobs() {
        // Check nonce for security
        check_ajax_referer('job_scraper_nonce', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error('You do not have permission to perform this action.');
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'job_scraper_jobs';
        
        // Delete all records
        $result = $wpdb->query("TRUNCATE TABLE $table_name");
        
        if ($result === false) {
            wp_send_json_error('Database error: ' . $wpdb->last_error);
        } else {
            wp_send_json_success(array(
                'message' => __('All jobs have been successfully cleared!', 'job-scraper')
            ));
        }
    }
    
    /**
     * Batch update application status for multiple jobs
     *
     * @since    1.0.0
     */
    public function batch_update_application_status() {
        // Check nonce for security
        check_ajax_referer('job_scraper_nonce', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error('You do not have permission to perform this action.');
            return;
        }
        
        // Get job IDs and status
        $job_ids = isset($_POST['job_ids']) ? array_map('intval', $_POST['job_ids']) : array();
        $new_status = isset($_POST['new_status']) ? (int) $_POST['new_status'] : 0;
        
        // Validate job IDs
        if (empty($job_ids)) {
            wp_send_json_error('No jobs selected.');
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'job_scraper_jobs';
        
        // Verify the applied column exists
        $column_exists = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'applied'",
                DB_NAME,
                $table_name
            )
        );
        
        // If applied column doesn't exist, add it
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN applied TINYINT(1) NOT NULL DEFAULT 0");
        }
        
        // Format job IDs for SQL query
        $placeholders = implode(',', array_fill(0, count($job_ids), '%d'));
        
        // Update all selected jobs
        $query = $wpdb->prepare(
            "UPDATE $table_name SET applied = %d WHERE id IN ($placeholders)",
            array_merge(array($new_status), $job_ids)
        );
        
        $result = $wpdb->query($query);
        
        if ($result === false) {
            wp_send_json_error('Database error: ' . $wpdb->last_error);
        } else {
            $status_text = $new_status ? 'applied' : 'not applied';
            wp_send_json_success(array(
                'message' => sprintf(__('%d jobs have been marked as %s.', 'job-scraper'), $result, $status_text),
                'affected_jobs' => $job_ids,
                'new_status' => $new_status,
                'button_text' => $new_status ? __('Mark as Not Applied', 'job-scraper') : __('Mark as Applied', 'job-scraper')
            ));
        }
    }
} 