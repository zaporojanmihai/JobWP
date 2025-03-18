<?php
/**
 * The main plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
class Job_Scraper {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Job_Scraper_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->plugin_name = 'job-scraper';
        $this->version = JOB_SCRAPER_VERSION;

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // Load the core plugin classes
        require_once JOB_SCRAPER_PLUGIN_DIR . 'includes/class-job-scraper-admin.php';
        require_once JOB_SCRAPER_PLUGIN_DIR . 'includes/class-job-scraper-public.php';
        
        // Load the scrapers
        require_once JOB_SCRAPER_PLUGIN_DIR . 'includes/scrapers/class-job-scraper-linkedin.php';
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Job_Scraper_Admin($this->plugin_name, $this->version);
        
        // Add menu items
        add_action('admin_menu', array($plugin_admin, 'add_plugin_admin_menu'));
        
        // Add settings link to the plugin
        add_filter('plugin_action_links_' . plugin_basename(JOB_SCRAPER_PLUGIN_DIR . 'job-scraper.php'), 
            array($plugin_admin, 'add_action_links'));
            
        // Save/Update plugin options
        add_action('admin_init', array($plugin_admin, 'options_update'));
        
        // Enqueue admin styles and scripts
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));
        
        // Add AJAX handlers for manual scraping
        add_action('wp_ajax_job_scraper_manual_scrape', array($plugin_admin, 'manual_scrape'));
        
        // Add AJAX handler for toggling application status
        add_action('wp_ajax_job_scraper_toggle_application', array($plugin_admin, 'toggle_application_status'));
        
        // Add AJAX handler for clearing all jobs
        add_action('wp_ajax_job_scraper_clear_jobs', array($plugin_admin, 'clear_jobs'));
        
        // Register AJAX handler for batch update application status
        add_action('wp_ajax_job_scraper_batch_update_application', array($plugin_admin, 'batch_update_application_status'));
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new Job_Scraper_Public($this->plugin_name, $this->version);
        
        // Enqueue public styles and scripts
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_scripts'));
        
        // Register shortcodes
        add_shortcode('job_scraper_listings', array($plugin_public, 'display_job_listings_shortcode'));
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        // All hooks are registered directly with WordPress in define_admin_hooks and define_public_hooks
        // No loader class is required
    }

    /**
     * Function to scrape all jobs from configured sources
     *
     * @since    1.0.0
     */
    public function scrape_all_jobs() {
        $options = get_option('job_scraper_options');
        $keywords = isset($options['keywords']) ? explode(',', $options['keywords']) : array();
        $date_filter = isset($options['date_filter']) ? $options['date_filter'] : 'all';
        
        if (empty($keywords)) {
            return;
        }
        
        if (isset($options['linkedin_enabled']) && $options['linkedin_enabled']) {
            $linkedin_scraper = new Job_Scraper_LinkedIn();
            $linkedin_scraper->scrape($keywords, $date_filter);
        }
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Activation hook.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Create database table
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'job_scraper_jobs';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            job_id varchar(255) NOT NULL,
            title text NOT NULL,
            company varchar(255) NOT NULL,
            location varchar(255) NOT NULL,
            description longtext NOT NULL,
            url varchar(255) NOT NULL,
            source varchar(50) NOT NULL,
            date_posted datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY job_id (job_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Set default options
        $default_options = array(
            'keywords' => 'web developer, programmer, PHP developer',
            'linkedin_enabled' => 1,
            'results_per_page' => 25,
            'date_filter' => 'week',
            'locations' => array('us'),
        );
        
        add_option('job_scraper_options', $default_options);
        
        // Check and update database if needed
        self::update_database();
    }

    /**
     * Check and update database structure if needed.
     *
     * @since    1.0.0
     */
    public static function update_database() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'job_scraper_jobs';
        
        // Check if the applied column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'applied'",
            DB_NAME,
            $table_name
        ));
        
        // If the column doesn't exist, add it
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN applied tinyint(1) NOT NULL DEFAULT 0");
        }
    }

    /**
     * Plugin deactivation
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Plugin deactivation code
    }
} 