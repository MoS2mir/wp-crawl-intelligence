<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wpci-grid two-col">
    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><?php echo $icons['sitemap']; ?> Sitemap-Log Gap Analyzer <span class="wpci-help-tip" data-tip="Pages in your sitemap that bots haven't crawled in 30+ days. This indicates discovery issues.">?</span></h3>
            <span class="wpci-badge yellow">Discovery Fix</span>
        </div>
        <p class="description">Pages in your sitemap that bots have ignored for 30+ days.</p>
        <div class="wpci-table-wrapper">
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>URL Pathway</th><th>Actions</th><th>Recency</th></tr></thead>
                <tbody>
                    <?php if ( ! empty( $unloved_pages ) ) : ?>
                        <?php foreach ( array_slice( $unloved_pages, 0, 10 ) as $url ) : ?>
                            <tr>
                                <td class="wpci-url-cell">
                                    <code title="<?php echo esc_attr($url); ?>"><?php echo esc_html( wp_parse_url($url, PHP_URL_PATH) ); ?></code>
                                </td>
                                <td class="wpci-actions-cell">
                                    <a href="<?php echo esc_url($url); ?>" target="_blank" class="wpci-action-btn" title="View Page"><span class="dashicons dashicons-visibility"></span></a>
                                    <a href="https://search.google.com/search-console/inspect?resource_id=<?php echo urlencode(home_url('/')); ?>&url=<?php echo urlencode($url); ?>" target="_blank" class="wpci-action-btn gsc-btn" title="Inspect in Search Console"><span class="dashicons dashicons-google"></span></a>
                                </td>
                                <td><span class="wpci-badge red">30d+ Unseen</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="3">Healthy! All sitemap URLs recently crawled.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><span class="dashicons dashicons-search"></span> Top Crawled URLs <span class="wpci-help-tip" data-tip="Most frequently crawled URLs by all bots in the last 7 days.">?</span></h3>
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>URL</th><th>Actions</th><th>Hits</th></tr></thead>
            <tbody>
                <?php foreach ( $top_urls as $row ) : ?>
                    <tr>
                        <td class="wpci-url-cell"><code title="<?php echo esc_attr($row->url); ?>"><?php echo esc_html( wp_parse_url($row->url, PHP_URL_PATH) ); ?></code></td>
                        <td class="wpci-actions-cell">
                            <a href="<?php echo esc_url($row->url); ?>" target="_blank" class="wpci-action-btn" title="View Page"><span class="dashicons dashicons-visibility"></span></a>
                        </td>
                        <td><strong><?php echo esc_html($row->hit_count); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="wpci-grid three-col">
    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><?php echo $icons['waste']; ?> Parameter Waste <span class="wpci-help-tip" data-tip="URLs with query parameters that bots are crawling. Excessive crawling of these can waste your budget.">?</span></h3>
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Query</th><th>Actions</th><th>Hits</th></tr></thead>
            <tbody>
                <?php if ( ! empty( $parameter_waste ) ) : ?>
                    <?php foreach ( $parameter_waste as $row ) : ?>
                        <tr>
                            <td class="wpci-url-cell"><code>?<?php echo esc_html( wp_parse_url($row->url, PHP_URL_QUERY) ); ?></code></td>
                            <td class="wpci-actions-cell">
                                <a href="<?php echo esc_url($row->url); ?>" target="_blank" class="wpci-action-btn" title="View Example"><span class="dashicons dashicons-visibility"></span></a>
                            </td>
                            <td><?php echo esc_html($row->waste_hits); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="3">No waste detected.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><span class="dashicons dashicons-redo"></span> Redirect Monitor <span class="wpci-help-tip" data-tip="Bots hitting redirecting URLs. Too many redirects can slow down crawling.">?</span></h3>
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>URL</th><th>Actions</th><th>Hits</th></tr></thead>
            <tbody>
                <?php if ( ! empty( $redirect_chains ) ) : ?>
                    <?php foreach ( $redirect_chains as $row ) : ?>
                        <tr>
                            <td class="wpci-url-cell"><code><?php echo esc_html( wp_parse_url($row->url, PHP_URL_PATH) ); ?></code></td>
                            <td class="wpci-actions-cell">
                                <a href="<?php echo esc_url($row->url); ?>" target="_blank" class="wpci-action-btn" title="Follow Redirect"><span class="dashicons dashicons-external"></span></a>
                            </td>
                            <td><span class="wpci-badge yellow"><?php echo esc_html($row->redirect_count); ?> hops</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="3">Direct paths only.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><span class="dashicons dashicons-clock"></span> Discovery Speed <span class="wpci-help-tip" data-tip="Time between post publication and the first time a bot crawled it. Faster is better.">?</span></h3>
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Content</th><th>Actions</th><th>Delay</th></tr></thead>
            <tbody>
                <?php if ( ! empty( $discovery_speed ) ) : ?>
                    <?php foreach ( $discovery_speed as $row ) : ?>
                        <tr>
                            <td><?php echo esc_html($row->post_title); ?></td>
                            <td class="wpci-actions-cell">
                                <a href="<?php echo esc_url(get_permalink($row->ID)); ?>" target="_blank" class="wpci-action-btn" title="View Post"><span class="dashicons dashicons-visibility"></span></a>
                            </td>
                            <td><span class="wpci-badge <?php echo ($row->discovery_delay_hours > 24) ? 'red' : 'green'; ?>"><?php echo esc_html($row->discovery_delay_hours); ?>h</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="3">N/A</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
