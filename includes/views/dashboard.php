<?php
/**
 * WP Crawl Intelligence - Modern SaaS Admin Dashboard
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// SVG Icons for SaaS look
$icons = [
    'budget'   => '<svg class="wpci-icon" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>',
    'capacity' => '<svg class="wpci-icon" viewBox="0 0 24 24"><path d="M21 3H3c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H3V5h18v14zM5 15h2V7H5v8zm12-8v8h2V7h-2zM9 15h2V9H9v6zm4 0h2V11h-2v4z"/></svg>',
    'ai'       => '<svg class="wpci-icon" viewBox="0 0 24 24"><path d="M11 2h2v5h-2V2zm0 15h2v5h-2v-5zm11-6v2h-5v-2h5zM7 11v2H2v-2h5zm12.364-5.95l1.414 1.414-3.535 3.536-1.414-1.415 3.535-3.535zM5.636 17.536l1.414 1.414-3.535 3.535-1.414-1.414 3.535-3.535zm12.728 0l1.414-1.414 3.536 3.535-1.415 1.414-3.535-3.535zM5.636 6.464L4.222 5.05l3.535-3.535 1.414 1.414-3.535 3.535z"/></svg>',
    'sitemap'  => '<svg class="wpci-icon" viewBox="0 0 24 24"><path d="M12 2L4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3zm0 18c-2.3 0-4.4-1.6-5.4-3.8l1.4-.4c.7 1.6 2.3 2.7 4 2.7 1.7 0 3.3-1.1 4-2.7l1.4.4c-1 2.2-3.1 3.8-5.4 3.8z"/></svg>',
    'waste'    => '<svg class="wpci-icon" viewBox="0 0 24 24"><path d="M16 1h-8l-1 1v2h10V2l-1-1zm-9 5l1 15c0 1.1.9 2 2 2h4c1.1 0 2-.9 2-2l1-15H7z"/></svg>',
    'path'     => '<svg class="wpci-icon" viewBox="0 0 24 24"><path d="M12 11c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm6 2c0-3.31-2.69-6-6-6s-6 2.69-6 6c0 2.22 1.21 4.15 3 5.19l1-1.74c-1.19-.7-2-1.97-2-3.45 0-2.21 1.79-4 4-4s4 1.79 4 4c0 1.48-.81 2.75-2 3.45l1 1.74c1.79-1.04 3-2.97 3-5.19z"/></svg>',
    'render'   => '<svg class="wpci-icon" viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>',
];
?>

<div class="wrap wpci-dashboard">
    <div class="wpci-header">
        <h1>
            <span class="dashicons dashicons-shield-alt" style="font-size: 32px; width: 32px; height: 32px; color: var(--wpci-primary);"></span>
            WP Crawl Intelligence <span class="wpci-badge blue">Premium</span>
        </h1>
        <p class="description">Modern Technical SEO Intelligence & Crawl Command Center.</p>
    </div>

    <?php if ( ! empty( $alerts ) ) : ?>
        <div class="wpci-grid full-grid">
            <div class="wpci-card advisor-card">
                <h3><span class="dashicons dashicons-warning" style="color: var(--wpci-danger);"></span> Critical Crawl Health Issues</h3>
                <div class="recommendations-list">
                    <?php foreach ( $alerts as $alert ) : ?>
                        <div class="rec-item" style="background: var(--wpci-danger-light); border-left: 4px solid var(--wpci-danger);">
                            <span class="dashicons dashicons-warning problem-icon" style="color: var(--wpci-danger);"></span>
                            <div>
                                <strong>Alert:</strong> <?php echo esc_html( $alert['message'] ); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Top KPI Row -->
    <div class="wpci-grid three-col">
        <div class="wpci-card">
            <div class="wpci-card-header">
                <h3><?php echo $icons['budget']; ?> Crawl Budget Score</h3>
                <span class="wpci-badge <?php echo ($score > 80) ? 'green' : 'yellow'; ?>">Last 7 Days</span>
            </div>
            <div class="score-display">
                <span class="score-value <?php echo ($score > 80) ? 'green' : (($score > 50) ? 'yellow' : 'red'); ?>">
                    <?php echo esc_html( $score ); ?>
                </span>
                <p>/100</p>
            </div>
            <p class="description">Overall efficiency ranking based on bot success rates & performance.</p>
        </div>

        <div class="wpci-card">
            <div class="wpci-card-header">
                <h3><?php echo $icons['capacity']; ?> Crawl Capacity</h3>
                <span class="wpci-badge green">Healthy</span>
            </div>
            <div class="score-display">
                <span class="score-value green"><?php echo esc_html( number_format( $crawl_capacity ) ); ?></span>
                <p>URLs / Day</p>
            </div>
            <p class="description">Calculated healthy crawl ceiling based on current server TTFB.</p>
        </div>

        <div class="wpci-card advisor-card">
            <div class="wpci-card-header">
                <h3><?php echo $icons['ai']; ?> AI Technical SEO Advisor</h3>
                <span class="wpci-badge blue">Intelligence</span>
            </div>
            <div class="recommendations-list">
                <?php if ( ! empty( $ai_recommendations ) ) : ?>
                    <?php foreach ( array_slice($ai_recommendations, 0, 2) as $rec ) : ?>
                        <div class="rec-item">
                            <span class="dashicons dashicons-lightbulb problem-icon"></span>
                            <div style="font-size: 11px;">
                                <strong>Rec:</strong> <span class="solution-text"><?php echo esc_html( $rec['solution'] ); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p style="font-size: 13px; margin: 10px 0;"><span class="dashicons dashicons-yes-alt green-icon"></span> No critical crawl optimizations needed!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Chart Section -->
    <div class="wpci-grid full-grid">
        <div class="wpci-card chart-card">
            <div class="wpci-card-header">
                <h3><span class="dashicons dashicons-chart-line"></span> Crawl vs Traffic Intelligence (Last 14 Days)</h3>
                <div class="wpci-chart-legend">
                    <div class="legend-item"><span class="dot blue"></span> Bots</div>
                    <div class="legend-item"><span class="dot green"></span> Human</div>
                </div>
            </div>
            <div id="wpciBotChart" style="width: 100%; height: 260px;"></div>
        </div>
    </div>

    <!-- Secondary Insights Grid -->
    <div class="wpci-grid two-col">
        <!-- Sitemap Gap card -->
        <div class="wpci-card">
            <div class="wpci-card-header">
                <h3><?php echo $icons['sitemap']; ?> 1. Sitemap-Log Gap Analyzer</h3>
                <span class="wpci-badge yellow">Discovery Fix</span>
            </div>
            <p class="description">Pages in your sitemap that bots have ignored for 30+ days.</p>
            <div class="wpci-table-wrapper">
                <table class="wp-list-table widefat fixed striped">
                    <thead><tr><th>URL Pathway</th><th>Recency</th></tr></thead>
                    <tbody>
                        <?php if ( ! empty( $unloved_pages ) ) : ?>
                            <?php foreach ( array_slice( $unloved_pages, 0, 4 ) as $url ) : ?>
                                <li><span class="dashicons dashicons-warning" style="color: var(--wpci-warning);"></span> <?php echo esc_url( $url ); ?></li>
                                <tr><td><?php echo esc_html( wp_parse_url($url, PHP_URL_PATH) ); ?></td><td><span class="wpci-badge red">30d+ Unseen</span></td></tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="2">Healthy! All sitemap URLs recently crawled.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Budget Simulator -->
        <div class="wpci-card simulator-card">
            <div class="wpci-card-header">
                <h3><span class="dashicons dashicons-calc"></span> 7. Crawl Budget Simulator</h3>
                <span class="wpci-badge green">ROI Potential</span>
            </div>
            <p class="description">Forecast savings by optimizing common waste patterns.</p>
            <div class="score-display">
                <span class="sim-value" style="font-size: 36px; color: var(--wpci-primary); font-weight: 800;"><?php echo esc_html( $budget_simulator['saved_percentage'] ); ?>%</span>
                <p>Recovery</p>
            </div>
            <div class="robots-snippet" style="background: var(--wpci-primary-light); color: var(--wpci-primary); border: 1px dashed var(--wpci-primary);">
                Blocking <strong>/tag/*</strong> and <strong>/search/*</strong> would recover <strong><?php echo esc_html( $budget_simulator['saved_units'] ); ?></strong> crawl units.
            </div>
        </div>
    </div>

    <!-- Mid Grid Modules -->
    <div class="wpci-grid three-col">
        <div class="wpci-card">
            <div class="wpci-card-header">
                <h3><?php echo $icons['waste']; ?> 2. Parameter Waste</h3>
            </div>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>Pattern</th><th>Hits</th></tr></thead>
                <tbody>
                    <?php if ( ! empty( $parameter_waste ) ) : ?>
                        <?php foreach ( array_slice( $parameter_waste, 0, 4 ) as $row ) : ?>
                            <tr><td><code><?php echo esc_html( substr(wp_parse_url($row->url, PHP_URL_QUERY), 0, 15) ); ?>...</code></td><td><?php echo esc_html($row->waste_hits); ?></td></tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="2">No waste detected.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="wpci-card">
            <div class="wpci-card-header">
                <h3><span class="dashicons dashicons-redo"></span> 3. Redirect Monitor</h3>
            </div>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>Redirect Chain</th><th>Hops</th></tr></thead>
                <tbody>
                    <?php if ( ! empty( $redirect_chains ) ) : ?>
                        <?php foreach ( array_slice( $redirect_chains, 0, 4 ) as $row ) : ?>
                            <tr><td><?php echo esc_html( wp_parse_url($row->url, PHP_URL_PATH) ); ?></td><td><span class="wpci-badge yellow"><?php echo esc_html($row->redirect_count); ?> hops</span></td></tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="2">Direct paths only.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="wpci-card">
            <div class="wpci-card-header">
                <h3><?php echo $icons['path']; ?> 10. Discovery Speed</h3>
            </div>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>Content</th><th>IDelay</th></tr></thead>
                <tbody>
                    <?php if ( ! empty( $discovery_speed ) ) : ?>
                        <?php foreach ( array_slice($discovery_speed, 0, 4) as $row ) : ?>
                            <tr><td><?php echo esc_html(substr($row->post_title, 0, 18)); ?>...</td><td><span class="wpci-badge <?php echo ($row->discovery_delay_hours > 24) ? 'red' : 'green'; ?>"><?php echo esc_html($row->discovery_delay_hours); ?>h</span></td></tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="2">N/A</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Analytics Cards -->
    <div class="wpci-grid">
        <div class="wpci-card">
            <div class="wpci-card-header">
                <h3><span class="dashicons dashicons-performance"></span> 6. Latency Heatmap</h3>
            </div>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>Cluster</th><th>latency</th></tr></thead>
                <tbody>
                    <?php foreach ( array_slice($latency_heatmap, 0, 4) as $row ) : ?>
                        <tr><td><?php echo esc_html($row->url_cluster); ?></td><td><span class="wpci-badge <?php echo ($row->avg_ttfb > 0.8) ? 'red' : 'green'; ?>"><?php echo number_format($row->avg_ttfb, 2); ?>s</span></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="wpci-card">
            <div class="wpci-card-header">
                <h3><span class="dashicons dashicons-smartphone"></span> 8. Mobile Parity</h3>
            </div>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>Agent</th><th>Hits</th></tr></thead>
                <tbody>
                    <?php foreach ( $mobile_parity as $row ) : ?>
                        <tr><td><strong><?php echo esc_html($row->device_type); ?></strong></td><td><?php echo esc_html($row->hit_count); ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="wpci-card">
            <div class="wpci-card-header">
                <h3><span class="dashicons dashicons-money-alt"></span> 7. Crawl ROI Stats</h3>
            </div>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>Type</th><th>Value</th></tr></thead>
                <tbody>
                    <?php foreach ( array_slice($crawl_roi, 0, 4) as $row ) : ?>
                        <tr><td><?php echo esc_html(ucfirst($row->post_type)); ?></td><td><span class="wpci-badge <?php echo ($row->roi_value == 'High') ? 'green' : 'blue'; ?>"><?php echo esc_html($row->roi_value); ?></span></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Final Path Row -->
    <div class="wpci-grid full-grid">
        <div class="wpci-card">
            <div class="wpci-card-header">
                <h3><?php echo $icons['path']; ?> 3. Googlebot Crawl Path Reconstruction</h3>
                <span class="wpci-badge blue">Vision</span>
            </div>
            <div class="sessions-container">
                <?php if ( ! empty( $bot_sessions ) ) : ?>
                    <?php foreach ( array_slice($bot_sessions, 0, 3) as $session ) : ?>
                        <div class="session-path">
                            <div class="session-header"><strong><?php echo esc_html( $session['bot'] ); ?></strong> &bull; <?php echo esc_html( $session['ip'] ); ?></div>
                            <div class="path-flow">
                                <?php foreach ( array_slice($session['path'], 0, 6) as $index => $hit ) : ?>
                                    <div class="path-step">
                                        <span class="step-url"><?php echo esc_html( wp_parse_url( $hit->url, PHP_URL_PATH ) ); ?></span>
                                        <span class="step-meta"><?php echo esc_html( date( 'H:i', strtotime( $hit->timestamp ) ) ); ?></span>
                                    </div>
                                    <?php if ( $index < count( array_slice($session['path'], 0, 6) ) - 1 ) : ?><span class="step-arrow">&rarr;</span><?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="wpci-actions" style="display: flex; gap: 15px; margin-top: 20px;">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="wpci_export_csv">
            <?php wp_nonce_field( 'wpci_export_csv', '_wpnonce' ); ?>
            <button type="submit" class="button button-primary button-large" style="background: var(--wpci-primary); border: none; padding: 10px 24px; height: auto;">Export Technical Logs</button>
        </form>
    </div>
</div>
