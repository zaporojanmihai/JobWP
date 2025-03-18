<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="options.php">
        <?php
            settings_fields('job_scraper_options');
            $options = get_option('job_scraper_options');
            
            // Default values
            $default_options = array(
                'keywords' => '',
                'results_per_page' => 25,
                'linkedin_enabled' => 1,
                'date_filter' => 'all',
                'locations' => array('us')
            );
            
            // Merge with defaults
            $options = wp_parse_args($options, $default_options);
        ?>
        
        <!-- Settings Section -->
        <div id="job-scraper-settings" class="job-scraper-tab-content">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Keywords', 'job-scraper'); ?></th>
                    <td>
                        <input type="text" name="job_scraper_options[keywords]" value="<?php echo esc_attr($options['keywords']); ?>" class="regular-text" />
                        <p class="description"><?php _e('Comma-separated list of keywords to search for (e.g. "developer, programmer, engineer")', 'job-scraper'); ?></p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php _e('Results Per Page', 'job-scraper'); ?></th>
                    <td>
                        <input type="number" name="job_scraper_options[results_per_page]" value="<?php echo intval($options['results_per_page']); ?>" min="5" max="100" />
                        <p class="description"><?php _e('Number of results per page (5-100)', 'job-scraper'); ?></p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php _e('Date Filter', 'job-scraper'); ?></th>
                    <td>
                        <select name="job_scraper_options[date_filter]">
                            <option value="all" <?php selected($options['date_filter'], 'all'); ?>><?php _e('All Time', 'job-scraper'); ?></option>
                            <option value="day" <?php selected($options['date_filter'], 'day'); ?>><?php _e('Last 24 Hours', 'job-scraper'); ?></option>
                            <option value="week" <?php selected($options['date_filter'], 'week'); ?>><?php _e('Last Week', 'job-scraper'); ?></option>
                            <option value="month" <?php selected($options['date_filter'], 'month'); ?>><?php _e('Last Month', 'job-scraper'); ?></option>
                        </select>
                        <p class="description"><?php _e('Filter jobs by date posted', 'job-scraper'); ?></p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php _e('Locations', 'job-scraper'); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span><?php _e('Locations', 'job-scraper'); ?></span></legend>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="eu" <?php checked(in_array('eu', (array)$options['locations'])); ?> />
                                <?php _e('Europe (All)', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="at" <?php checked(in_array('at', (array)$options['locations'])); ?> />
                                <?php _e('Austria', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="be" <?php checked(in_array('be', (array)$options['locations'])); ?> />
                                <?php _e('Belgium', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="ca" <?php checked(in_array('ca', (array)$options['locations'])); ?> />
                                <?php _e('Canada', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="dk" <?php checked(in_array('dk', (array)$options['locations'])); ?> />
                                <?php _e('Denmark', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="fi" <?php checked(in_array('fi', (array)$options['locations'])); ?> />
                                <?php _e('Finland', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="fr" <?php checked(in_array('fr', (array)$options['locations'])); ?> />
                                <?php _e('France', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="de" <?php checked(in_array('de', (array)$options['locations'])); ?> />
                                <?php _e('Germany', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="is" <?php checked(in_array('is', (array)$options['locations'])); ?> />
                                <?php _e('Iceland', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="it" <?php checked(in_array('it', (array)$options['locations'])); ?> />
                                <?php _e('Italy', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="nl" <?php checked(in_array('nl', (array)$options['locations'])); ?> />
                                <?php _e('Netherlands', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="no" <?php checked(in_array('no', (array)$options['locations'])); ?> />
                                <?php _e('Norway', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="pt" <?php checked(in_array('pt', (array)$options['locations'])); ?> />
                                <?php _e('Portugal', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="ro" <?php checked(in_array('ro', (array)$options['locations'])); ?> />
                                <?php _e('Romania', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="es" <?php checked(in_array('es', (array)$options['locations'])); ?> />
                                <?php _e('Spain', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="se" <?php checked(in_array('se', (array)$options['locations'])); ?> />
                                <?php _e('Sweden', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="uk" <?php checked(in_array('uk', (array)$options['locations'])); ?> />
                                <?php _e('United Kingdom', 'job-scraper'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[locations][]" value="us" <?php checked(in_array('us', (array)$options['locations'])); ?> />
                                <?php _e('United States', 'job-scraper'); ?>
                            </label>
                            
                            <p class="description"><?php _e('Select locations to search for jobs', 'job-scraper'); ?></p>
                        </fieldset>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php _e('Enabled Sources', 'job-scraper'); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span><?php _e('Enabled Sources', 'job-scraper'); ?></span></legend>
                            
                            <label>
                                <input type="checkbox" name="job_scraper_options[linkedin_enabled]" value="1" <?php checked($options['linkedin_enabled'], 1); ?> />
                                <?php _e('LinkedIn', 'job-scraper'); ?>
                            </label>
                            
                            <p class="description"><?php _e('Select which job sources to scrape', 'job-scraper'); ?></p>
                        </fieldset>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
            
            <div class="manual-scrape-section">
                <h3><?php _e('Manual Scrape', 'job-scraper'); ?></h3>
                <p><?php _e('Manually trigger the job scraper to run now.', 'job-scraper'); ?></p>
                
                <button id="manual-scrape-button" class="button button-primary"><?php _e('Scrape Jobs Now', 'job-scraper'); ?></button>
                
                <div id="manual-scrape-progress" style="margin-top: 15px; display: none;">
                    <div id="manual-scrape-message"></div>
                    <p id="manual-scrape-count"></p>
                    <p id="manual-scrape-locations"></p>
                </div>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                    <h3><?php _e('Clear Jobs', 'job-scraper'); ?></h3>
                    <p><?php _e('Delete all job listings from the database. This action cannot be undone.', 'job-scraper'); ?></p>
                    
                    <button id="clear-jobs-button" class="button button-secondary" style="background-color: #d63638; color: white; border-color: #d63638;"><?php _e('Clear All Jobs', 'job-scraper'); ?></button>
                    
                    <div id="clear-jobs-progress" style="margin-top: 15px; display: none;">
                        <div id="clear-jobs-message"></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#job-scraper-manual-button').on('click', function() {
        var $button = $(this);
        var $status = $('#job-scraper-manual-status');
        
        $button.prop('disabled', true);
        $status.html('<span style="color: blue;">Scraping in progress...</span>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'job_scraper_manual_scrape',
                nonce: job_scraper_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $status.html('<span style="color: green;">' + response.data.message + ' Found ' + response.data.count + ' jobs.</span>');
                } else {
                    $status.html('<span style="color: red;">Error: ' + response.data + '</span>');
                }
                $button.prop('disabled', false);
            },
            error: function() {
                $status.html('<span style="color: red;">Server error occurred. Please try again.</span>');
                $button.prop('disabled', false);
            }
        });
    });
    
    // Clear Jobs Button
    $('#clear-jobs-button').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to delete ALL job listings? This action cannot be undone.')) {
            return;
        }
        
        var $button = $(this);
        var $message = $('#clear-jobs-message');
        var $progress = $('#clear-jobs-progress');
        
        // Disable button and show progress
        $button.prop('disabled', true);
        $progress.show();
        $message.html('<span style="color: blue;">Clearing jobs...</span>');
        
        // Make AJAX request
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'job_scraper_clear_jobs',
                nonce: job_scraper_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $message.html('<span style="color: green;">' + response.data.message + '</span>');
                } else {
                    $message.html('<span style="color: red;">Error: ' + (response.data || 'Unknown error') + '</span>');
                }
                $button.prop('disabled', false);
            },
            error: function() {
                $message.html('<span style="color: red;">Server error occurred. Please try again.</span>');
                $button.prop('disabled', false);
            }
        });
    });
});
</script> 