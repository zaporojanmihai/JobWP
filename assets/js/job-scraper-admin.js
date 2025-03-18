/**
 * Job Scraper admin scripts
 */
jQuery(document).ready(function($) {
    'use strict';
    
    // Handle manual scrape button
    $('#manual-scrape-button').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var originalText = $button.text();
        
        // Disable button and show spinner
        $button.prop('disabled', true).text('Scraping...');
        
        // Show the progress container
        $('#manual-scrape-progress').show();
        
        // Make AJAX request
        $.ajax({
            url: job_scraper_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'job_scraper_manual_scrape',
                nonce: job_scraper_admin.nonce
            },
            success: function(response) {
                // Re-enable button and restore text
                $button.prop('disabled', false).text(originalText);
                
                // Check if the response is successful
                if (response.success) {
                    // Display success message
                    $('#manual-scrape-message').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                    $('#manual-scrape-count').html('Jobs found: ' + response.data.count);
                    
                    // Show locations if available
                    if (response.data.locations) {
                        $('#manual-scrape-locations').html('Locations searched: ' + response.data.locations);
                    }
                } else {
                    // Display error message
                    $('#manual-scrape-message').html('<div class="notice notice-error"><p>Error: ' + (response.data || 'Unknown error') + '</p></div>');
                }
            },
            error: function() {
                // Re-enable button and restore text
                $button.prop('disabled', false).text(originalText);
                
                // Display error message
                $('#manual-scrape-message').html('<div class="notice notice-error"><p>Error: Could not connect to server.</p></div>');
            }
        });
    });
    
    // Apply button styling on page load
    $('.job-scraper-toggle-application').each(function() {
        var $button = $(this);
        var status = $button.data('status');
        
        if (status === '1' || status === 1) {
            // Applied - make button red
            $button.addClass('button-secondary').css({
                'background-color': '#d63638',
                'color': 'white',
                'border-color': '#d63638'
            });
        } else {
            // Not applied - make button green
            $button.addClass('button-primary').css({
                'background-color': '#46b450',
                'color': 'white',
                'border-color': '#46b450'
            });
        }
    });
    
    // Toggle application status
    $(document).on('click', '.job-scraper-toggle-application', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var jobId = $button.data('job-id');
        var currentStatus = $button.data('status');
        
        // Disable button to prevent multiple clicks
        $button.prop('disabled', true);
        
        // Make AJAX request
        $.ajax({
            url: job_scraper_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'job_scraper_toggle_application',
                job_id: jobId,
                current_status: currentStatus,
                nonce: job_scraper_admin.nonce
            },
            success: function(response) {
                // Re-enable button
                $button.prop('disabled', false);
                
                // Check if the response is successful
                if (response.success) {
                    // Update button text and data attribute
                    $button.text(response.data.button_text);
                    $button.data('status', response.data.new_status);
                    
                    // Update button styling based on new status
                    if (response.data.new_status) {
                        // Applied - make button red
                        $button.removeClass('button-primary').addClass('button-secondary').css({
                            'background-color': '#d63638',
                            'color': 'white',
                            'border-color': '#d63638'
                        });
                    } else {
                        // Not applied - make button green
                        $button.removeClass('button-secondary').addClass('button-primary').css({
                            'background-color': '#46b450',
                            'color': 'white',
                            'border-color': '#46b450'
                        });
                    }
                    
                    // Update row class for styling
                    var $row = $button.closest('tr');
                    $row.removeClass('job-applied job-not-applied');
                    $row.addClass(response.data.new_status ? 'job-applied' : 'job-not-applied');
                    
                    // Apply row background color based on status
                    if (response.data.new_status) {
                        $row.css('background-color', '#f0f0f0'); // Gray for applied
                    } else {
                        $row.css('background-color', ''); // Default for not applied
                    }
                } else {
                    // Display error message
                    alert('Error: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                // Re-enable button
                $button.prop('disabled', false);
                
                // Display error message
                alert('Error: Could not connect to server.');
            }
        });
    });
    
    // Application Status Toggle
    $(document).on('click', '.job-application-status', function() {
        var button = $(this);
        var jobId = button.data('job-id');
        var currentStatus = button.data('status');
        var newStatus = currentStatus === '1' ? '0' : '1';
        
        // Optimistic UI update
        if (newStatus === '1') {
            button.removeClass('not-applied').addClass('applied');
            button.text('Applied ✓');
        } else {
            button.removeClass('applied').addClass('not-applied');
            button.text('Not Applied ✗');
        }
        
        button.data('status', newStatus);
        
        // AJAX call to update the status in the database
        $.ajax({
            url: job_scraper_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'job_scraper_toggle_application',
                job_id: jobId,
                status: newStatus,
                nonce: job_scraper_admin.nonce
            },
            success: function(response) {
                if (!response.success) {
                    // Revert UI if there was an error
                    if (newStatus === '1') {
                        button.removeClass('applied').addClass('not-applied');
                        button.text('Not Applied ✗');
                    } else {
                        button.removeClass('not-applied').addClass('applied');
                        button.text('Applied ✓');
                    }
                    button.data('status', currentStatus);
                    alert('Error updating application status: ' + response.data);
                }
            },
            error: function() {
                // Revert UI on error
                if (newStatus === '1') {
                    button.removeClass('applied').addClass('not-applied');
                    button.text('Not Applied ✗');
                } else {
                    button.removeClass('not-applied').addClass('applied');
                    button.text('Applied ✓');
                }
                button.data('status', currentStatus);
                alert('An error occurred while updating the application status.');
            }
        });
    });
    
    // Select all checkboxes functionality
    $(document).on('change', '#job-scraper-select-all', function() {
        $('.job-scraper-select-job').prop('checked', $(this).prop('checked'));
    });
    
    // Handle bulk action button click
    $(document).on('click', '#job-scraper-apply-bulk-action', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $message = $('#job-scraper-bulk-message');
        var action = $('#job-scraper-bulk-action').val();
        
        // Get selected job IDs
        var selectedJobIds = [];
        $('.job-scraper-select-job:checked').each(function() {
            selectedJobIds.push($(this).val());
        });
        
        // Validate selection
        if (selectedJobIds.length === 0) {
            $message.html('<span style="color: red;">Please select at least one job.</span>');
            return;
        }
        
        // Validate action
        if (action === '') {
            $message.html('<span style="color: red;">Please select an action.</span>');
            return;
        }
        
        // Determine new status based on action
        var newStatus = (action === 'mark-applied') ? 1 : 0;
        
        // Disable button and show loading message
        $button.prop('disabled', true);
        $message.html('<span style="color: blue;">Processing...</span>');
        
        // Make AJAX request
        $.ajax({
            url: job_scraper_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'job_scraper_batch_update_application',
                job_ids: selectedJobIds,
                new_status: newStatus,
                nonce: job_scraper_admin.nonce
            },
            success: function(response) {
                // Re-enable button
                $button.prop('disabled', false);
                
                if (response.success) {
                    // Show success message
                    $message.html('<span style="color: green;">' + response.data.message + '</span>');
                    
                    // Update UI for each affected job
                    $.each(response.data.affected_jobs, function(index, jobId) {
                        var $row = $('input.job-scraper-select-job[value="' + jobId + '"]').closest('tr');
                        var $actionButton = $row.find('.job-scraper-toggle-application');
                        
                        // Update row class and styling
                        $row.removeClass('job-applied job-not-applied');
                        $row.addClass(newStatus ? 'job-applied' : 'job-not-applied');
                                                
                        // Update action button
                        $actionButton.text(response.data.button_text);
                        
                        // Update button styling
                        if (newStatus) {
                            // Applied - make button red
                            $actionButton.removeClass('button-primary').addClass('button-secondary').css({
                                'background-color': '#d63638',
                                'color': 'white',
                                'border-color': '#d63638'
                            });
                        } else {
                            // Not applied - make button green
                            $actionButton.removeClass('button-secondary').addClass('button-primary').css({
                                'background-color': '#46b450',
                                'color': 'white',
                                'border-color': '#46b450'
                            });
                        }
                    });
                    
                    // Clear checkboxes
                    $('#job-scraper-select-all').prop('checked', false);
                    $('.job-scraper-select-job').prop('checked', false);
                } else {
                    // Show error message
                    $message.html('<span style="color: red;">Error: ' + (response.data || 'Unknown error') + '</span>');
                }
            },
            error: function() {
                // Re-enable button
                $button.prop('disabled', false);
                
                // Show error message
                $message.html('<span style="color: red;">Server error occurred. Please try again.</span>');
            }
        });
    });
    
    // Clear message when changing bulk action
    $(document).on('change', '#job-scraper-bulk-action', function() {
        $('#job-scraper-bulk-message').html('');
    });
}); 