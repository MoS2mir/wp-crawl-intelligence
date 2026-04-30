<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wpci-header">
    <h1>
        <span class="dashicons dashicons-shield-alt" style="font-size: 32px; width: 32px; height: 32px; color: var(--wpci-primary);"></span>
        WP Crawl Intelligence <span class="wpci-badge blue">Premium</span>
    </h1>
    <p class="description">Modern Technical SEO Intelligence & Crawl Command Center.</p>
</div>

<nav class="nav-tab-wrapper wpci-nav-tabs" style="margin-bottom: 20px;">
    <a href="?page=wpci_dashboard&tab=overview" class="nav-tab <?php echo $current_tab === 'overview' ? 'nav-tab-active' : ''; ?>">Overview</a>
    <a href="?page=wpci_dashboard&tab=bots" class="nav-tab <?php echo $current_tab === 'bots' ? 'nav-tab-active' : ''; ?>">Bot Intelligence</a>
    <a href="?page=wpci_dashboard&tab=content" class="nav-tab <?php echo $current_tab === 'content' ? 'nav-tab-active' : ''; ?>">Content Analysis</a>
    <a href="?page=wpci_dashboard&tab=performance" class="nav-tab <?php echo $current_tab === 'performance' ? 'nav-tab-active' : ''; ?>">Health & Performance</a>
</nav>
