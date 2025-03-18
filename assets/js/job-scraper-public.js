/**
 * Job Scraper public scripts
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Job details toggle in public listings
        $('.job-scraper-view-details').on('click', function(e) {
            e.preventDefault();
            var jobId = $(this).data('id');
            
            if ($(this).text() === 'View Details') {
                $(this).text('Hide Details');
            } else {
                $(this).text('View Details');
            }
        });
        
    });
    
})(jQuery); 