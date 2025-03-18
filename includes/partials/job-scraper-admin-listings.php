<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php
    // Get jobs from database
    global $wpdb;
    $table_name = $wpdb->prefix . 'job_scraper_jobs';

    // Get options
    $options = get_option('job_scraper_options');
    $per_page = isset($options['results_per_page']) ? intval($options['results_per_page']) : 25;

    // Get current page
    $page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
    $offset = ($page - 1) * $per_page;

    // Get total job count
    $total_jobs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    // Get jobs for current page
    $jobs = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY date_posted DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ),
        ARRAY_A
    );

    // Calculate pagination
    $total_pages = ceil($total_jobs / $per_page);
    ?>

    <div class="job-scraper-admin-jobs-wrapper">
        <div class="job-scraper-jobs-header">
            <p><?php printf(_n('Found %s job', 'Found %s jobs', $total_jobs, 'job-scraper'), number_format_i18n($total_jobs)); ?></p>
            
            <!-- Batch actions -->
            <div class="job-scraper-batch-actions">
                <select id="job-scraper-bulk-action">
                    <option value=""><?php _e('Bulk Actions', 'job-scraper'); ?></option>
                    <option value="mark-applied"><?php _e('Mark as Applied', 'job-scraper'); ?></option>
                    <option value="mark-not-applied"><?php _e('Mark as Not Applied', 'job-scraper'); ?></option>
                </select>
                <button id="job-scraper-apply-bulk-action" class="button"><?php _e('Apply', 'job-scraper'); ?></button>
                <span id="job-scraper-bulk-message"></span>
            </div>
        </div>
        
        <?php if ($jobs && count($jobs) > 0) : ?>
            <?php if ($total_pages > 1) : ?>
                <div class="tablenav top job-scraper-pagination top">
                    <div class="tablenav-pages">
                        <span class="displaying-num">
                            <?php printf(_n('%s item', '%s items', $total_jobs, 'job-scraper'), number_format_i18n($total_jobs)); ?>
                        </span>
                        <span class="pagination-links">
                            <?php
                            $pagination_args = array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total' => $total_pages,
                                'current' => $page
                            );
                            echo paginate_links($pagination_args);
                            ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
            
            <table class="wp-list-table widefat fixed striped job-scraper-jobs-table">
                <thead>
                    <tr>
                        <th class="check-column">
                            <input type="checkbox" id="job-scraper-select-all" />
                        </th>
                        <th><?php _e('Title', 'job-scraper'); ?></th>
                        <th><?php _e('Company', 'job-scraper'); ?></th>
                        <th><?php _e('Location', 'job-scraper'); ?></th>
                        <th><?php _e('Source', 'job-scraper'); ?></th>
                        <th><?php _e('Date Posted', 'job-scraper'); ?></th>
                        <th class="column-actions"><?php _e('Actions', 'job-scraper'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job) : 
                        $applied_class = isset($job['applied']) && $job['applied'] ? 'job-applied' : 'job-not-applied';
                        $row_style = isset($job['applied']) && $job['applied'] ? 'style="background-color: #f0f0f0;"' : '';
                    ?>
                        <tr class="<?php echo esc_attr($applied_class); ?>" <?php echo $row_style; ?>>
                            <td>
                                <input type="checkbox" class="job-scraper-select-job" value="<?php echo esc_attr($job['id']); ?>" />
                            </td>
                            <td>
                                <?php echo esc_html($job['title']); ?>
                            </td>
                            <td><?php echo esc_html($job['company']); ?></td>
                            <td><?php echo esc_html($job['location']); ?></td>
                            <td><?php echo esc_html(ucfirst($job['source'])); ?></td>
                            <td><?php echo esc_html(date('M j, Y', strtotime($job['date_posted']))); ?></td>
                            <td>
                                <?php 
                                    $applied = isset($job['applied']) ? (bool)$job['applied'] : false;
                                ?>
                                <button 
                                    class="button job-scraper-toggle-application <?php echo $applied ? 'button-secondary' : 'button-primary'; ?>" 
                                    data-job-id="<?php echo esc_attr($job['id']); ?>"
                                    data-status="<?php echo $applied ? '1' : '0'; ?>"
                                    style="<?php echo $applied ? 'background-color: #d63638; color: white; border-color: #d63638;' : 'background-color: #46b450; color: white; border-color: #46b450;'; ?>"
                                >
                                    <?php echo $applied ? __('Mark as Not Applied', 'job-scraper') : __('Mark as Applied', 'job-scraper'); ?>
                                </button>
                                
                                <a href="<?php echo esc_url($job['url']); ?>" target="_blank" class="button" style="margin-left: 5px; background-color: #2271b1; color: white; border-color: #2271b1;">
                                    <?php _e('View Job', 'job-scraper'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1) : ?>
                <div class="tablenav bottom job-scraper-pagination bottom">
                    <div class="tablenav-pages">
                        <span class="displaying-num">
                            <?php printf(_n('%s item', '%s items', $total_jobs, 'job-scraper'), number_format_i18n($total_jobs)); ?>
                        </span>
                        <span class="pagination-links">
                            <?php echo paginate_links($pagination_args); ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else : ?>
            <div class="notice notice-info">
                <p><?php _e('No jobs found. Try scraping for jobs using the "Scrape Jobs Now" button on the Settings tab.', 'job-scraper'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.job-applied {
    background-color: #fddcdc !important; /* Gray background for applied jobs */
}
.job-status-applied {
    color: #800000;
    font-weight: bold;
}
.job-status-not-applied {
    color: #888;
}
.column-actions {
    width: 240px; /* Make the actions column wider to fit both buttons */
}
.job-scraper-toggle-application {
    margin-bottom: 5px;
}

/* Improved Pagination Styles */
.job-scraper-pagination {
    margin: 10px 0;
    text-align: right;
}

.job-scraper-pagination .tablenav-pages {
    float: right;
}

.job-scraper-pagination .displaying-num {
    margin-right: 10px;
    font-size: 13px;
    color: #555;
}

.job-scraper-pagination .pagination-links {
    font-size: 13px;
}

.job-scraper-pagination .pagination-links .page-numbers {
    display: inline-block;
    min-width: 28px;
    padding: 0 4px;
    height: 28px;
    margin: 0 1px;
    background: #f7f7f7;
    border: 1px solid #ccc;
    border-radius: 3px;
    line-height: 26px;
    text-align: center;
    text-decoration: none;
    color: #0073aa;
    box-shadow: 0 1px 0 #ccc;
}

.job-scraper-pagination .pagination-links .page-numbers.current {
    background: #0073aa;
    border-color: #006799;
    color: #fff;
    box-shadow: inset 0 1px 0 rgba(0,0,0,.1);
}

.job-scraper-pagination .pagination-links .page-numbers:hover:not(.current) {
    background: #f0f0f0;
    border-color: #999;
    color: #0073aa;
}

.job-scraper-pagination .pagination-links .prev,
.job-scraper-pagination .pagination-links .next {
    padding: 0 7px;
}

.job-scraper-pagination.top {
    float: right;
    clear: none;
    margin-top: 0;
    margin-bottom: 5px;
}

.job-scraper-pagination.bottom {
    margin-top: 10px;
}

/* Fix checkbox alignment */
table.job-scraper-jobs-table .check-column {
    vertical-align: middle;
    text-align: center;
    padding: 8px 4px;
}

table.job-scraper-jobs-table input[type="checkbox"] {
    margin: 0;
    vertical-align: middle;
    position: relative;
    top: 1px;
}

table.job-scraper-jobs-table th.check-column {
    padding-top: 7px;
    padding-bottom: 12px;
}

#job-scraper-select-all {
    margin: 0;
}

/* Batch Actions Styles */
.job-scraper-batch-actions {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

.job-scraper-batch-actions select {
    margin-right: 5px;
}

.job-scraper-batch-actions .button {
    margin-right: 10px;
}

#job-scraper-bulk-message {
    padding-left: 10px;
}

.check-column {
    width: 30px;
}

.job-scraper-jobs-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

/* Table styling improvements */
.job-scraper-jobs-table th,
.job-scraper-jobs-table td {
    vertical-align: top;
    padding: 10px 8px;
}

.job-scraper-jobs-table th {
    font-weight: 600;
}
</style> 