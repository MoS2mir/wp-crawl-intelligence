<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wpci-grid two-col">
    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><?php echo $icons['sitemap']; ?> Sitemap-Log Gap Analyzer</h3>
            <span class="wpci-badge yellow">Discovery Fix</span>
        </div>
        <p class="description">Pages in your sitemap that bots have ignored for 30+ days.</p>
        <div class="wpci-table-wrapper">
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>URL Pathway</th><th>Recency</th></tr></thead>
                <tbody>
                    <?php if ( ! empty( $unloved_pages ) ) : ?>
                        <?php foreach ( array_slice( $unloved_pages, 0, 10 ) as $url ) : ?>
                            <tr><td><?php echo esc_html( wp_parse_url($url, PHP_URL_PATH) ); ?></td><td><span class="wpci-badge red">30d+ Unseen</span></td></tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="2">Healthy! All sitemap URLs recently crawled.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><span class="dashicons dashicons-search"></span> Top Crawled URLs (7 Days)</h3>
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>URL</th><th>Hits</th></tr></thead>
            <tbody>
                <?php foreach ( $top_urls as $row ) : ?>
                    <tr><td><code><?php echo esc_html( wp_parse_url($row->url, PHP_URL_PATH) ); ?></code></td><td><?php echo esc_html($row->hit_count); ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="wpci-grid three-col">
    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><?php echo $icons['waste']; ?> Parameter Waste</h3>
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Query Pattern</th><th>Hits</th></tr></thead>
            <tbody>
                <?php if ( ! empty( $parameter_waste ) ) : ?>
                    <?php foreach ( $parameter_waste as $row ) : ?>
                        <tr><td><code>?<?php echo esc_html( wp_parse_url($row->url, PHP_URL_QUERY) ); ?></code></td><td><?php echo esc_html($row->waste_hits); ?></td></tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="2">No waste detected.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><span class="dashicons dashicons-redo"></span> Redirect Monitor</h3>
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>URL</th><th>Hits</th></tr></thead>
            <tbody>
                <?php if ( ! empty( $redirect_chains ) ) : ?>
                    <?php foreach ( $redirect_chains as $row ) : ?>
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
            <h3><span class="dashicons dashicons-clock"></span> Discovery Speed</h3>
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Content</th><th>Delay</th></tr></thead>
            <tbody>
                <?php if ( ! empty( $discovery_speed ) ) : ?>
                    <?php foreach ( $discovery_speed as $row ) : ?>
                        <tr><td><?php echo esc_html($row->post_title); ?></td><td><span class="wpci-badge <?php echo ($row->discovery_delay_hours > 24) ? 'red' : 'green'; ?>"><?php echo esc_html($row->discovery_delay_hours); ?>h</span></td></tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="2">N/A</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
