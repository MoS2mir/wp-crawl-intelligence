<?php
/**
 * Dashboard View
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap wpci-dashboard">
	<h1>Crawl Intelligence Dashboard</h1>

	<?php if ( ! empty( $alerts ) ) : ?>
		<div class="notice notice-warning is-dismissible">
			<?php foreach ( $alerts as $alert ) : ?>
				<p><strong>Warning:</strong> <?php echo esc_html( $alert['message'] ); ?></p>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="wpci-grid">
		<div class="wpci-card info-card">
			<h3>Crawl Budget Score</h3>
			<div class="score-display">
				<span class="score-value <?php echo ( $score > 80 ) ? 'green' : ( ( $score > 50 ) ? 'yellow' : 'red' ); ?>">
					<?php echo esc_html( $score ); ?>
				</span>
				<p>/100</p>
			</div>
			<p class="description">Based on bot error rates and response times over the last 7 days.</p>
		</div>

		<div class="wpci-card chart-card">
			<h3>Daily Bot Crawl Volume (Last 14 Days)</h3>
			<canvas id="wpciBotChart"></canvas>
		</div>

		<div class="wpci-card table-card">
			<h3>Top Crawled URLs (Last 7 Days)</h3>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>URL</th>
						<th>Hits</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $top_urls ) : ?>
						<?php foreach ( $top_urls as $row ) : ?>
							<tr>
								<td><?php echo esc_url( $row->url ); ?></td>
								<td><?php echo esc_html( $row->hit_count ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="2">No data yet.</td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="wpci-actions">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="wpci_export_csv">
			<?php wp_nonce_field( 'wpci_export_csv', '_wpnonce' ); ?>
			<button type="submit" class="button button-primary">Export Bot Logs to CSV</button>
		</form>
	</div>
</div>
