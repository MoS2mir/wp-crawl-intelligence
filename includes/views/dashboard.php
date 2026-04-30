<?php
/**
 * WP Crawl Intelligence - Modern SaaS Admin Dashboard (Controller)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Get current tab
$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'overview';

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
    
    <?php include WPCI_PATH . 'includes/views/partials/header.php'; ?>

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

    <div class="wpci-tab-content">
        <?php
        switch ( $current_tab ) {
            case 'bots':
                include WPCI_PATH . 'includes/views/partials/bots.php';
                break;
            case 'content':
                include WPCI_PATH . 'includes/views/partials/content.php';
                break;
            case 'performance':
                include WPCI_PATH . 'includes/views/partials/performance.php';
                break;
            case 'overview':
            default:
                include WPCI_PATH . 'includes/views/partials/overview.php';
                break;
        }
        ?>
    </div>

    <div class="wpci-actions" style="display: flex; gap: 15px; margin-top: 20px;">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="wpci_export_csv">
            <?php wp_nonce_field( 'wpci_export_csv', '_wpnonce' ); ?>
            <button type="submit" class="button button-primary button-large" style="background: var(--wpci-primary); border: none; padding: 10px 24px; height: auto;">Export Technical Logs</button>
        </form>
    </div>
</div>
