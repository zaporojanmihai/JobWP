<div class="job-scraper-container">
    <?php if (!empty($atts['title'])) : ?>
        <h2 class="job-scraper-title"><?php echo esc_html($atts['title']); ?></h2>
    <?php endif; ?>
    
    <?php if (empty($jobs)) : ?>
        <p class="job-scraper-no-results">No job listings found.</p>
    <?php else : ?>
        <div class="job-scraper-count">
            <p>Found <?php echo $total_jobs; ?> job listings.</p>
        </div>
        
        <div class="job-scraper-listings">
            <?php foreach ($jobs as $job) : ?>
                <div class="job-scraper-item" id="job-<?php echo esc_attr($job->id); ?>">
                    <div class="job-scraper-header">
                        <h3 class="job-scraper-title">
                            <a href="<?php echo esc_url($job->url); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo esc_html($job->title); ?>
                            </a>
                        </h3>
                        <div class="job-scraper-meta">
                            <span class="job-scraper-company"><?php echo esc_html($job->company); ?></span>
                            <?php if (!empty($job->location)) : ?>
                                <span class="job-scraper-location">
                                    <span class="job-scraper-separator">•</span>
                                    <?php echo esc_html($job->location); ?>
                                </span>
                            <?php endif; ?>
                            <span class="job-scraper-date">
                                <span class="job-scraper-separator">•</span>
                                <?php 
                                $date_posted = new DateTime($job->date_posted);
                                $now = new DateTime();
                                $interval = $date_posted->diff($now);
                                
                                if ($interval->days == 0) {
                                    echo 'Today';
                                } elseif ($interval->days == 1) {
                                    echo 'Yesterday';
                                } elseif ($interval->days < 7) {
                                    echo $interval->days . ' days ago';
                                } elseif ($interval->days < 30) {
                                    echo floor($interval->days / 7) . ' week' . (floor($interval->days / 7) > 1 ? 's' : '') . ' ago';
                                } elseif ($interval->days < 365) {
                                    echo floor($interval->days / 30) . ' month' . (floor($interval->days / 30) > 1 ? 's' : '') . ' ago';
                                } else {
                                    echo floor($interval->days / 365) . ' year' . (floor($interval->days / 365) > 1 ? 's' : '') . ' ago';
                                }
                                ?>
                            </span>
                            <span class="job-scraper-source">
                                <span class="job-scraper-separator">•</span>
                                <?php 
                                $source_label = ucfirst($job->source);
                                $source_class = 'job-source-' . $job->source;
                                echo '<span class="' . esc_attr($source_class) . '">' . esc_html($source_label) . '</span>';
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="job-scraper-content">
                        <div class="job-scraper-description">
                            <?php 
                            // Get a short excerpt of the description
                            $description = strip_tags($job->description);
                            $words = explode(' ', $description);
                            if (count($words) > 30) {
                                $short_desc = implode(' ', array_slice($words, 0, 30)) . '...';
                            } else {
                                $short_desc = $description;
                            }
                            
                            echo esc_html($short_desc);
                            ?>
                        </div>
                        <div class="job-scraper-actions">
                            <a href="<?php echo esc_url($job->url); ?>" class="job-scraper-apply" target="_blank" rel="noopener noreferrer">Apply</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($total_pages > 1) : ?>
            <div class="job-scraper-pagination">
                <?php
                $big = 999999999;
                echo paginate_links(array(
                    'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                    'format' => '?paged=%#%',
                    'current' => $current_page,
                    'total' => $total_pages,
                    'prev_text' => '&laquo; Previous',
                    'next_text' => 'Next &raquo;',
                ));
                ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.job-scraper-container {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    max-width: 960px;
    margin: 0 auto;
}

.job-scraper-item {
    border: 1px solid #e5e5e5;
    margin-bottom: 20px;
    padding: 20px;
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.job-scraper-item:hover {
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.job-scraper-header {
    margin-bottom: 15px;
}

.job-scraper-title {
    margin: 0 0 10px 0;
    font-size: 18px;
    line-height: 1.3;
}

.job-scraper-title a {
    color: #0073aa;
    text-decoration: none;
}

.job-scraper-title a:hover {
    color: #00a0d2;
    text-decoration: underline;
}

.job-scraper-meta {
    font-size: 14px;
    color: #666;
    line-height: 1.4;
}

.job-scraper-separator {
    margin: 0 5px;
    color: #ccc;
}

.job-scraper-description {
    margin-bottom: 15px;
    font-size: 14px;
    line-height: 1.5;
    color: #333;
}

.job-scraper-actions {
    display: flex;
    justify-content: space-between;
}

.job-scraper-apply {
    display: inline-block;
    padding: 8px 16px;
    text-decoration: none;
    font-size: 14px;
    border-radius: 3px;
    text-align: center;
}

.job-scraper-apply {
    background: #0073aa;
    color: #fff;
    border: 1px solid #0073aa;
}

.job-scraper-apply:hover {
    background: #00a0d2;
    border-color: #00a0d2;
}

.job-scraper-description-full {
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 1.6;
}

.job-scraper-apply-container {
    text-align: center;
    margin: 20px 0;
}

.job-scraper-apply-button {
    display: inline-block;
    padding: 10px 20px;
    background: #0073aa;
    color: #fff;
    text-decoration: none;
    border-radius: 3px;
    font-size: 16px;
    font-weight: 500;
}

.job-scraper-apply-button:hover {
    background: #00a0d2;
}

.job-scraper-pagination {
    margin-top: 30px;
    text-align: center;
}

.job-scraper-pagination .page-numbers {
    display: inline-block;
    padding: 5px 10px;
    margin: 0 2px;
    border: 1px solid #ddd;
    color: #0073aa;
    text-decoration: none;
    border-radius: 3px;
}

.job-scraper-pagination .page-numbers.current {
    background: #0073aa;
    color: #fff;
    border-color: #0073aa;
}

.job-scraper-pagination .page-numbers:hover:not(.current) {
    background: #f5f5f5;
}

.job-source-linkedin {
    color: #0077B5;
    font-weight: 500;
    border-left-color: #0077B5;
}

@media (max-width: 768px) {
    .job-scraper-actions {
        flex-direction: column;
        gap: 10px;
    }
    
    .job-scraper-apply {
        width: 100%;
    }
}
</style>