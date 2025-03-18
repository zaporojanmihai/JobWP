# Job Scraper WordPress Plugin

A WordPress plugin that scrapes job listings from LinkedIn based on keywords, date filters, and locations.

## Description

Job Scraper helps you automatically collect job listings from LinkedIn and display them on your WordPress website. This plugin is perfect for job boards, career sites, or any website that wants to showcase relevant job opportunities to their visitors.

### Features

- Scrape job listings from LinkedIn
- Filter jobs by keywords
- Filter by date posted (last day, last week, last month)
- Filter by location (United States, United Kingdom, Canada, Germany, France, Spain, and Europe)
- Automatic scraping via WordPress cron
- Manual scraping option in the admin panel
- Clean, responsive job listings display with shortcode
- Customizable settings
- Detailed job information including company, location, description, and application links
- Track application status for jobs

## Installation

1. Download the plugin zip file
2. Go to your WordPress admin panel
3. Navigate to Plugins > Add New
4. Click "Upload Plugin" and select the downloaded zip file
5. Click "Install Now"
6. After installation, click "Activate Plugin"

## Usage

### Admin Settings

After activating the plugin, go to "Job Scraper" in the WordPress admin menu to configure the plugin:

1. **Keywords**: Enter the keywords to search for jobs, separated by commas
2. **Date Filter**: Choose to scrape only jobs posted within a specific time period (all time, last 24 hours, last week, or last month)
3. **Locations**: Select which countries/regions to include in your job search
4. **Sources**: Enable/disable LinkedIn as a job source
5. **Auto Scrape Frequency**: Set how often the plugin should automatically scrape for new jobs
6. **Results Per Page**: Set how many job listings to display per page

### Manual Scraping

On the Job Scraper settings page, click the "Scrape Jobs Now" button to manually trigger the scraping process.

### View Job Listings

Go to "Job Scraper > Job Listings" in the admin menu to view all scraped jobs. You can filter the listings by date posted, location, or search by keyword.

### Display Job Listings on Your Site

Use the shortcode `[job_scraper_listings]` to display job listings on any post or page.

#### Shortcode Options

You can customize the job listings display with these attributes:

- `title`: Add a custom title above the listings
- `count`: Number of listings to display
- `source`: Filter by source (linkedin or all)
- `keywords`: Filter by specific keywords, comma-separated
- `date_range`: Filter by date posted (all, day, week, month)
- `location`: Filter by location (United States, United Kingdom, Canada, Germany, France, Spain, Europe)

Example:
```
[job_scraper_listings title="Developer Jobs" count="5" source="linkedin" keywords="php,wordpress" date_range="week" location="Germany"]
```

## Technical Notes

### Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- cURL enabled
- PHP DOM extension enabled

### 1.0.0
- Initial release 
