<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="job-scraper-shortcode-generator">
        <h2>Shortcode Generator</h2>
        <p>Use this tool to generate a shortcode for displaying job listings on your site.</p>
        
        <div class="job-scraper-shortcode-form">
            <form id="job-scraper-shortcode-builder">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="shortcode-title">Title</label></th>
                        <td>
                            <input type="text" id="shortcode-title" name="title" value="Job Listings" class="regular-text">
                            <p class="description">The heading displayed above the job listings.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="shortcode-count">Count</label></th>
                        <td>
                            <input type="number" id="shortcode-count" name="count" value="10" min="1" max="100">
                            <p class="description">Number of job listings to display per page.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="shortcode-source">Source</label></th>
                        <td>
                            <select id="shortcode-source" name="source">
                                <option value="all">All Sources</option>
                                <option value="linkedin">LinkedIn Only</option>
                            </select>
                            <p class="description">Filter jobs by source.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="shortcode-keywords">Keywords</label></th>
                        <td>
                            <input type="text" id="shortcode-keywords" name="keywords" value="" class="regular-text">
                            <p class="description">Comma-separated list of keywords to filter jobs (e.g., "Staff Engineer, Senior Developer").</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="shortcode-date-range">Date Range</label></th>
                        <td>
                            <select id="shortcode-date-range" name="date_range">
                                <option value="all">All Time</option>
                                <option value="day">Last 24 Hours</option>
                                <option value="week">Last Week</option>
                                <option value="month">Last Month</option>
                            </select>
                            <p class="description">Filter jobs by posting date.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="shortcode-location">Location</label></th>
                        <td>
                            <select id="shortcode-location" name="location">
                                <!-- Europe (All), Austria, Belgium, Canada, Denmark, Finland, France , Germany, Iceland, Italy, Netherlands, Norway, Portugal, Romania, Spain, Sweden, United Kingdom, United States -->
                                <option value="all">All Locations</option>
                                <option value="Austria">Austria</option>
                                <option value="Belgium">Belgium</option>
                                <option value="Canada">Canada</option>
                                <option value="Denmark">Denmark</option>
                                <option value="Finland">Finland</option>
                                <option value="France">France</option>
                                <option value="Germany">Germany</option>
                                <option value="Iceland">Iceland</option>
                                <option value="Italy">Italy</option>
                                <option value="Netherlands">Netherlands</option>
                                <option value="Norway">Norway</option>
                                <option value="Portugal">Portugal</option>
                                <option value="Romania">Romania</option>
                                <option value="Spain">Spain</option>
                                <option value="Sweden">Sweden</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="United States">United States</option>
                            </select>
                            <p class="description">Filter jobs by location.</p>
                        </td>
                    </tr>
                </table>
                
                <div class="job-scraper-shortcode-preview">
                    <h3>Generated Shortcode</h3>
                    <div class="shortcode-output">
                        <code id="generated-shortcode">[job_scraper_listings]</code>
                        <button type="button" id="copy-shortcode" class="button button-secondary">Copy to Clipboard</button>
                    </div>
                    <p class="description">Copy this shortcode and paste it into any page or post where you want to display job listings.</p>
                </div>
            </form>
        </div>
        
        <div class="job-scraper-shortcode-docs">
            <h3>Shortcode Documentation</h3>
            <p>The <code>[job_scraper_listings]</code> shortcode accepts the following attributes:</p>
            
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Attribute</th>
                        <th>Description</th>
                        <th>Default</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>title</code></td>
                        <td>Custom heading for the job listings section</td>
                        <td>"Job Listings"</td>
                        <td><code>title="Engineering Jobs"</code></td>
                    </tr>
                    <tr>
                        <td><code>count</code></td>
                        <td>Number of job listings to display per page</td>
                        <td>10</td>
                        <td><code>count="20"</code></td>
                    </tr>
                    <tr>
                        <td><code>source</code></td>
                        <td>Filter jobs by source</td>
                        <td>"all"</td>
                        <td><code>source="linkedin"</code></td>
                    </tr>
                    <tr>
                        <td><code>keywords</code></td>
                        <td>Filter jobs by specific keywords (comma-separated)</td>
                        <td>empty (shows all jobs)</td>
                        <td><code>keywords="Staff Engineer, Senior Developer"</code></td>
                    </tr>
                    <tr>
                        <td><code>date_range</code></td>
                        <td>Filter jobs by posting date</td>
                        <td>"all"</td>
                        <td><code>date_range="week"</code></td>
                    </tr>
                    <tr>
                        <td><code>location</code></td>
                        <td>Filter jobs by location</td>
                        <td>"all"</td>
                        <td><code>location="United States"</code></td>
                    </tr>
                </tbody>
            </table>
            
            <h4>Example Shortcodes</h4>
            <ul>
                <li><code>[job_scraper_listings source="linkedin" date_range="week"]</code> - Display only LinkedIn jobs posted in the last week</li>
                <li><code>[job_scraper_listings keywords="Staff Engineer, Senior Developer"]</code> - Show jobs matching specific keywords</li>
                <li><code>[job_scraper_listings location="United States"]</code> - Display jobs from a specific location</li>
                <li><code>[job_scraper_listings title="Recent Engineering Jobs" source="linkedin" keywords="Staff Engineer" date_range="week" location="Romania" count="5"]</code> - Combine multiple filters</li>
            </ul>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Function to generate shortcode based on form values
    function generateShortcode() {
        var title = $('#shortcode-title').val();
        var count = $('#shortcode-count').val();
        var source = $('#shortcode-source').val();
        var keywords = $('#shortcode-keywords').val();
        var dateRange = $('#shortcode-date-range').val();
        var location = $('#shortcode-location').val();
        
        var shortcode = '[job_scraper_listings';
        
        // Add attributes if they're not default values
        if (title && title !== 'Job Listings') {
            shortcode += ' title="' + title + '"';
        }
        
        if (count && count !== '10') {
            shortcode += ' count="' + count + '"';
        }
        
        if (source && source !== 'all') {
            shortcode += ' source="' + source + '"';
        }
        
        if (keywords) {
            shortcode += ' keywords="' + keywords + '"';
        }
        
        if (dateRange && dateRange !== 'all') {
            shortcode += ' date_range="' + dateRange + '"';
        }
        
        if (location && location !== 'all') {
            shortcode += ' location="' + location + '"';
        }
        
        shortcode += ']';
        
        $('#generated-shortcode').text(shortcode);
    }
    
    // Generate shortcode on form change
    $('#job-scraper-shortcode-builder input, #job-scraper-shortcode-builder select').on('change keyup', function() {
        generateShortcode();
    });
    
    // Copy shortcode to clipboard
    $('#copy-shortcode').on('click', function() {
        var shortcodeText = $('#generated-shortcode').text();
        
        // Create temporary textarea element
        var textarea = document.createElement('textarea');
        textarea.value = shortcodeText;
        document.body.appendChild(textarea);
        
        // Select and copy text
        textarea.select();
        document.execCommand('copy');
        
        // Remove temporary element
        document.body.removeChild(textarea);
        
        // Show feedback
        var $button = $(this);
        var originalText = $button.text();
        $button.text('Copied!');
        
        setTimeout(function() {
            $button.text(originalText);
        }, 2000);
    });
    
    // Initialize shortcode on page load
    generateShortcode();
});
</script>

<style>
.job-scraper-shortcode-preview {
    margin: 20px 0;
    padding: 15px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 3px;
}

.shortcode-output {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.shortcode-output code {
    flex: 1;
    padding: 10px;
    background: #fff;
    border: 1px solid #ddd;
    margin-right: 10px;
    font-size: 14px;
}

.job-scraper-shortcode-docs {
    margin-top: 30px;
}

.job-scraper-shortcode-docs table {
    margin: 15px 0;
}

.job-scraper-shortcode-docs code {
    background: #f0f0f0;
    padding: 2px 5px;
    border-radius: 3px;
}
</style> 