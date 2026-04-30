<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wpci-grid full-grid">
    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><?php echo $icons['path']; ?> Googlebot Crawl Path Reconstruction</h3>
            <span class="wpci-badge blue">Vision</span>
        </div>
        <div class="sessions-container">
            <?php if ( ! empty( $bot_sessions ) ) : ?>
                <?php foreach ( $bot_sessions as $session ) : ?>
                    <div class="session-path">
                        <div class="session-header"><strong><?php echo esc_html( $session['bot'] ); ?></strong> &bull; <?php echo esc_html( $session['ip'] ); ?></div>
                        <div class="path-flow">
                            <?php foreach ( array_slice($session['path'], 0, 8) as $index => $hit ) : ?>
                                <div class="path-step">
                                    <span class="step-url"><?php echo esc_html( wp_parse_url( $hit->url, PHP_URL_PATH ) ); ?></span>
                                    <span class="step-meta"><?php echo esc_html( date( 'H:i', strtotime( $hit->timestamp ) ) ); ?> &bull; <?php echo esc_html($hit->status_code); ?></span>
                                </div>
                                <?php if ( $index < count( array_slice($session['path'], 0, 8) ) - 1 ) : ?><span class="step-arrow">&rarr;</span><?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No bot sessions detected in the last 24 hours.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="wpci-grid two-col">
    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><span class="dashicons dashicons-smartphone"></span> Mobile-First Indexing Parity</h3>
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Bot & Device</th><th>Hits</th><th>Avg Response</th></tr></thead>
            <tbody>
                <?php foreach ( $mobile_parity as $row ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html($row->bot_type); ?> (<?php echo esc_html($row->device_type); ?>)</strong></td>
                        <td><?php echo esc_html($row->hit_count); ?></td>
                        <td><span class="wpci-badge blue"><?php echo number_format($row->avg_time, 2); ?>s</span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><?php echo $icons['render']; ?> Rendering Monitor (Resource Ratio)</h3>
        </div>
        <p class="description">Ratio of JS/CSS requests vs Page requests per bot.</p>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Bot</th><th>Page Hits</th><th>Asset Hits</th></tr></thead>
            <tbody>
                <?php foreach ( $resource_ratio as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html($row->bot_type); ?></td>
                        <td><?php echo esc_html($row->page_hits); ?></td>
                        <td><?php echo esc_html($row->asset_hits); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
