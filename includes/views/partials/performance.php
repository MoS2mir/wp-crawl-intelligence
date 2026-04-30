<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wpci-grid two-col">
    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><span class="dashicons dashicons-performance"></span> Latency Heatmap <span class="wpci-help-tip" data-tip="Average server response time (TTFB) experienced by bots. High latency can cause bots to crawl less.">?</span></h3>
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
            <h3><span class="dashicons dashicons-warning"></span> Soft 404 Candidates <span class="wpci-help-tip" data-tip="Pages returning Status 200 but having very little content. Google may treat these as errors.">?</span></h3>
        </div>
        <p class="description">URLs returning Status 200 but with unusually low content size.</p>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>URL</th><th>Actions</th><th>Size</th></tr></thead>
            <tbody>
                <?php if ( ! empty( $soft_404s ) ) : ?>
                    <?php foreach ( $soft_404s as $row ) : ?>
                        <tr>
                            <td class="wpci-url-cell"><code><?php echo esc_html( wp_parse_url($row->url, PHP_URL_PATH) ); ?></code></td>
                            <td class="wpci-actions-cell">
                                <a href="<?php echo esc_url($row->url); ?>" target="_blank" class="wpci-action-btn" title="View Page"><span class="dashicons dashicons-visibility"></span></a>
                                <a href="https://search.google.com/search-console/inspect?resource_id=<?php echo urlencode(home_url('/')); ?>&url=<?php echo urlencode($row->url); ?>" target="_blank" class="wpci-action-btn gsc-btn" title="Inspect in GSC"><span class="dashicons dashicons-google"></span></a>
                            </td>
                            <td><span class="wpci-badge red"><?php echo esc_html( round($row->content_length / 1024, 1) ); ?> KB</span></td>
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
            <h3><span class="dashicons dashicons-media-text"></span> Robots.txt Insights <span class="wpci-help-tip" data-tip="Suggestions for your robots.txt file based on detected crawl waste patterns.">?</span></h3>
        </div>
        <p class="description">Based on recent crawl waste, we suggest evaluating these Disallow rules:</p>
        <div class="robots-snippet" style="background: #2c3338; color: #fff; font-family: monospace; padding: 20px; border-radius: 8px; position: relative;">
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
