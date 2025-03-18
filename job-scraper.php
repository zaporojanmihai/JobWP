<?php
/**
 * Plugin Name: Job Scraper
 * Plugin URI: https://example.com/plugins/job-scraper
 * Description: Scrapes job listings from LinkedIn based on keywords and date filters.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: job-scraper
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('JOB_SCRAPER_VERSION', '1.0.0');
define('JOB_SCRAPER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JOB_SCRAPER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once JOB_SCRAPER_PLUGIN_DIR . 'includes/class-job-scraper.php';
require_once JOB_SCRAPER_PLUGIN_DIR . 'includes/class-job-scraper-admin.php';
require_once JOB_SCRAPER_PLUGIN_DIR . 'includes/class-job-scraper-public.php';

// Activation and deactivation hooks
register_activation_hook(__FILE__, array('Job_Scraper', 'activate'));
register_deactivation_hook(__FILE__, array('Job_Scraper', 'deactivate'));

// Initialize the plugin
function job_scraper_init() {
    // Run database update to ensure schema is current
    Job_Scraper::update_database();
    
    // Initialize the plugin
    $job_scraper = new Job_Scraper();
    $job_scraper->run();
}
job_scraper_init(); 