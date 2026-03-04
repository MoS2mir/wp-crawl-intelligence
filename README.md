# WP Crawl Intelligence

An advanced real-time bot tracking and crawl budget analysis plugin for WordPress. 

## Features

- **Real-time Request Logging:** Captures bot hits (URL, IP, User-Agent, Status Code, Response Time).
- **Bot Verification:** Uses reverse DNS to verify real Googlebot and Bingbot to prevent spoofing.
- **Crawl Budget Scoring:** Automatically calculates a health score based on crawl efficiency.
- **Performance Optimized:** Uses custom database tables, indexes, and background aggregation.
- **SEO Alerts:** Detects spikes in 404 errors or crawl patterns.
- **Admin Dashboard:** Premium UI with SVG charts and top URL analysis.

## Installation

1. Upload the `wp-crawl-intelligence` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to **Crawl Intel** in the admin sidebar.

## Technical Details

- **Namespace:** `WPCI`
- **Minimum PHP Version:** 8.0
- **Hooks:** Uses `plugins_loaded`, `template_redirect`, `shutdown`, and `wp_schedule_event`.
- **Database Tables:** 
    - `wp_wpci_logs`: Stores raw hit data.
    - `wp_wpci_stats`: Stores daily aggregated metrics.

---
Created by Antigravity Architect
