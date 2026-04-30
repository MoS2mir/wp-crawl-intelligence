<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wpci-grid two-col">
    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><span class="dashicons dashicons-performance"></span> Latency Heatmap (TTFB per Bot)</h3>
        </div>
        <p class="description">Average server response time experienced by different bot types.</p>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Bot & Cluster</th><th>Avg TTFB</th><th>Samples</th></tr></thead>
            <tbody>
                <?php foreach ( $latency_heatmap as $row ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html($row->bot_type); ?></strong> (<?php echo esc_html($row->url_cluster); ?>)</td>
                        <td><span class="wpci-badge <?php echo ($row->avg_ttfb > 0.8) ? 'red' : (($row->avg_ttfb > 0.4) ? 'yellow' : 'green'); ?>"><?php echo number_format($row->avg_ttfb, 3); ?>s</span></td>
                        <td><?php echo esc_html($row->sample_size); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><span class="dashicons dashicons-warning"></span> Soft 404 Candidates</h3>
        </div>
        <p class="description">URLs returning Status 200 but with unusually low content size.</p>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>URL</th><th>Size</th><th>Bot</th></tr></thead>
            <tbody>
                <?php if ( ! empty( $soft_404s ) ) : ?>
                    <?php foreach ( $soft_404s as $row ) : ?>
                        <tr>
                            <td><code><?php echo esc_html( wp_parse_url($row->url, PHP_URL_PATH) ); ?></code></td>
                            <td><span class="wpci-badge red"><?php echo esc_html( round($row->content_length / 1024, 1) ); ?> KB</span></td>
                            <td><?php echo esc_html($row->bot_type); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="3">No soft 404 candidates detected.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="wpci-grid full-grid">
    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><span class="dashicons dashicons-media-text"></span> Automatic Robots.txt Insights</h3>
        </div>
        <p class="description">Based on recent crawl waste, we suggest evaluating these Disallow rules:</p>
        <div class="robots-snippet" style="background: #2c3338; color: #fff; font-family: monospace; padding: 20px; border-radius: 8px;">
            <?php if ( ! empty( $robots_suggestions ) ) : ?>
                <?php foreach ( $robots_suggestions as $suggestion ) : ?>
                    <div style="margin-bottom: 5px;"><?php echo esc_html( $suggestion ); ?></div>
                <?php endforeach; ?>
            <?php else : ?>
                # No specific waste patterns detected for robots.txt
            <?php endif; ?>
        </div>
    </div>
</div>
