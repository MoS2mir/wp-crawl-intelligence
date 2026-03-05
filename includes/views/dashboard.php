<?php
/**
 * Dashboard View
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap wpci-dashboard">
	<h1>WP Crawl Intelligence - Premium Dashboard</h1>

	<?php if ( ! empty( $alerts ) ) : ?>
		<div class="notice notice-warning is-dismissible">
			<?php foreach ( $alerts as $alert ) : ?>
				<p><strong><?php echo esc_html( ucfirst( $alert['type'] ) ); ?>:</strong> <?php echo esc_html( $alert['message'] ); ?></p>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<!-- Row 1: Core Metrics & AI Advisor -->
	<div class="wpci-grid">
		<div class="wpci-card info-card">
			<h3>Crawl Budget Score</h3>
			<div class="score-display">
				<span class="score-value <?php echo ( $score > 80 ) ? 'green' : ( ( $score > 50 ) ? 'yellow' : 'red' ); ?>">
					<?php echo esc_html( $score ); ?>
				</span>
				<p>/100</p>
			</div>
			<p class="description">Overall efficiency of bot activity.</p>
		</div>

		<div class="wpci-card info-card">
			<h3>Crawl Capacity Estimate</h3>
			<div class="score-display">
				<span class="score-value green"><?php echo esc_html( number_format( $crawl_capacity ) ); ?></span>
				<p>URLs / Day</p>
			</div>
			<p class="description">Calculated healthy crawl ceiling.</p>
		</div>

		<div class="wpci-card advisor-card">
			<h3>8. AI Technical SEO Advisor</h3>
			<div class="recommendations-list">
				<?php if ( ! empty( $ai_recommendations ) ) : ?>
					<?php foreach ( $ai_recommendations as $rec ) : ?>
						<div class="rec-item">
							<span class="dashicons dashicons-warning problem-icon"></span>
							<div>
								<strong>Problem:</strong> <?php echo esc_html( $rec['problem'] ); ?><br>
								<strong>Solution:</strong> <span class="solution-text"><?php echo esc_html( $rec['solution'] ); ?></span>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<p><span class="dashicons dashicons-yes green-icon"></span> No critical issues found.</p>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Row 2: Indexing & Waste -->
	<div class="wpci-grid">
		<div class="wpci-card table-card">
			<h3>1. Sitemap-Log Gap</h3>
			<p class="description">Unloved pages (in sitemap, not crawled 30d).</p>
			<ul class="wpci-log-list">
				<?php if ( ! empty( $unloved_pages ) ) : ?>
					<?php foreach ( array_slice( $unloved_pages, 0, 5 ) as $url ) : ?>
						<li><?php echo esc_url( $url ); ?></li>
					<?php endforeach; ?>
				<?php else : ?>
					<li>All sitemap URLs recently crawled!</li>
				<?php endif; ?>
			</ul>
		</div>

		<div class="wpci-card table-card">
			<h3>2. Parameter Crawl Waste</h3>
			<p class="description">Inefficient query string crawling.</p>
			<table class="wp-list-table widefat fixed striped">
				<thead><tr><th>Path</th><th>Hits</th></tr></thead>
				<tbody>
					<?php if ( ! empty( $parameter_waste ) ) : ?>
						<?php foreach ( array_slice( $parameter_waste, 0, 5 ) as $row ) : ?>
							<tr>
								<td><?php echo esc_url( wp_parse_url( $row->url, PHP_URL_PATH ) ); ?></td>
								<td><?php echo esc_html( $row->waste_hits ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="2">No waste detected.</td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Row 3: Top Content & Volume -->
	<div class="wpci-grid half-grid">
		<div class="wpci-card table-card">
			<h3>Top Crawled URLs (Last 7 Days)</h3>
			<table class="wp-list-table widefat fixed striped">
				<thead><tr><th>URL</th><th>Hits</th></tr></thead>
				<tbody>
					<?php foreach ( array_slice( $top_urls, 0, 8 ) as $row ) : ?>
						<tr><td><?php echo esc_url( $row->url ); ?></td><td><?php echo esc_html( $row->hit_count ); ?></td></tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="wpci-card chart-card">
			<h3>Daily Bot Volume</h3>
			<canvas id="wpciBotChart" style="max-height: 250px;"></canvas>
		</div>
	</div>

	<!-- Row 4: Paths & Depth -->
	<div class="wpci-grid">
		<div class="wpci-card table-card">
			<h3>3. Redirect Chain Monitor</h3>
			<table class="wp-list-table widefat fixed striped">
				<thead><tr><th>URL</th><th>Status</th><th>Hops</th></tr></thead>
				<tbody>
					<?php if ( ! empty( $redirect_chains ) ) : ?>
						<?php foreach ( array_slice( $redirect_chains, 0, 5 ) as $row ) : ?>
							<tr><td><?php echo esc_url( $row->url ); ?></td><td><?php echo esc_html( $row->status_code ); ?></td><td><?php echo esc_html( $row->redirect_count ); ?></td></tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="3">Clean redirects.</td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<div class="wpci-card table-card">
			<h3>4. Post Type Energy</h3>
			<table class="wp-list-table widefat fixed striped">
				<thead><tr><th>Type</th><th>Hits</th><th>TTFB</th></tr></thead>
				<tbody>
					<?php foreach ( array_slice( $budget_by_type, 0, 5 ) as $row ) : ?>
						<tr><td><strong><?php echo esc_html( ucfirst($row->post_type) ); ?></strong></td><td><?php echo esc_html($row->hit_count); ?></td><td><?php echo esc_html(number_format($row->avg_time, 2)); ?>s</td></tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="wpci-card table-card">
			<h3>5. Soft 404 Detection</h3>
			<table class="wp-list-table widefat fixed striped">
				<thead><tr><th>URL</th><th>Size</th></tr></thead>
				<tbody>
					<?php if ( ! empty( $soft_404s ) ) : ?>
						<?php foreach ( array_slice( $soft_404s, 0, 5 ) as $row ) : ?>
							<tr><td><?php echo esc_url($row->url); ?></td><td><?php echo esc_html(round($row->content_length/1024, 1)); ?>KB</td></tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="2">No thin pages.</td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Row 5: Latency & Mobile -->
	<div class="wpci-grid">
		<div class="wpci-card table-card">
			<h3>6. Bot Latency Heatmap</h3>
			<table class="wp-list-table widefat fixed striped">
				<thead><tr><th>Bot</th><th>Area</th><th>Latency</th></tr></thead>
				<tbody>
					<?php foreach ( array_slice( $latency_heatmap, 0, 5 ) as $row ) : ?>
						<tr class="<?php echo ($row->avg_ttfb > 1) ? 'slow-row' : ''; ?>"><td><?php echo esc_html($row->bot_type); ?></td><td><?php echo esc_html($row->url_cluster); ?></td><td><?php echo esc_html(number_format($row->avg_ttfb, 2)); ?>s</td></tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="wpci-card table-card">
			<h3>8. Mobile-First Parity</h3>
			<table class="wp-list-table widefat fixed striped">
				<thead><tr><th>Bot</th><th>Agent</th><th>Hits</th></tr></thead>
				<tbody>
					<?php foreach ( $mobile_parity as $row ) : ?>
						<tr><td><?php echo esc_html($row->bot_type); ?></td><td><?php echo esc_html($row->device_type); ?></td><td><?php echo esc_html($row->hit_count); ?></td></tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Row 6: Discovery & Robots -->
	<div class="wpci-grid">
		<div class="wpci-card info-card">
			<h3>9. Robots.txt Suggestion</h3>
			<?php if ( ! empty( $robots_suggestions ) ) : ?>
				<pre class="robots-snippet"><?php echo esc_html( implode( "\n", $robots_suggestions ) ); ?></pre>
			<?php else : ?>
				<p>Robots.txt looks optimized.</p>
			<?php endif; ?>
		</div>
		<div class="wpci-card table-card">
			<h3>10. Discovery Speed</h3>
			<table class="wp-list-table widefat fixed striped">
				<thead><tr><th>Post</th><th>Delay</th></tr></thead>
				<tbody>
					<?php if ( ! empty( $discovery_speed ) ) : ?>
						<?php foreach ( $discovery_speed as $row ) : ?>
							<tr><td><?php echo esc_html($row->post_title); ?></td><td><span class="delay-badge <?php echo ($row->discovery_delay_hours > 24) ? 'red' : 'green'; ?>"><?php echo esc_html($row->discovery_delay_hours); ?>h</span></td></tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td>No recent posts logged.</td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Feature 7: Advanced SEO Intelligence (ROI & Simulator) -->
	<div class="wpci-grid">
		<div class="wpci-card info-card simulator-card">
			<h3>7. Crawl Budget Simulator</h3>
			<p class="description">Simulated savings if common waste patterns were blocked.</p>
			<div class="simulator-stats">
				<div class="sim-stat">
					<span class="sim-value"><?php echo esc_html( $budget_simulator['saved_percentage'] ); ?>%</span>
					<span class="sim-label">Potential Budget Recovery</span>
				</div>
				<p class="sim-impact">Blocking <strong>/tag/*</strong> and <strong>/search/*</strong> would save <strong><?php echo esc_html( $budget_simulator['saved_units'] ); ?></strong> crawl units per period.</p>
			</div>
		</div>

		<div class="wpci-card table-card">
			<h3>7. Crawl ROI Analyzer</h3>
			<p class="description">Bot energy vs. Content Value (Product = High ROI).</p>
			<table class="wp-list-table widefat fixed striped">
				<thead><tr><th>Post Type</th><th>Hits</th><th>Business Value</th></tr></thead>
				<tbody>
					<?php foreach ( $crawl_roi as $row ) : ?>
						<tr>
							<td><strong><?php echo esc_html( ucfirst( $row->post_type ) ); ?></strong></td>
							<td><?php echo esc_html( $row->bot_hits ); ?></td>
							<td><span class="roi-badge <?php echo strtolower( $row->roi_value ); ?>"><?php echo esc_html( $row->roi_value ); ?></span></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Feature 3 & 10: Bot Path & Mindmap -->
	<div class="wpci-grid full-grid">
		<div class="wpci-card session-card">
			<h3>3 & 10. Googlebot Mindmap & Crawl Path</h3>
			<p class="description">Visual flow of the most recent bot navigation sequences.</p>
			<div class="sessions-container">
				<?php if ( ! empty( $bot_sessions ) ) : ?>
					<?php foreach ( $bot_sessions as $session ) : ?>
						<div class="session-path">
							<div class="session-header"><strong><?php echo esc_html( $session['bot'] ); ?></strong> (<?php echo esc_html( $session['ip'] ); ?>)</div>
							<div class="path-flow">
								<?php foreach ( array_slice($session['path'], 0, 8) as $index => $hit ) : ?>
									<div class="path-step">
										<span class="step-url"><?php echo esc_html( wp_parse_url( $hit->url, PHP_URL_PATH ) ); ?></span>
										<span class="step-meta"><?php echo esc_html( date( 'H:i', strtotime( $hit->timestamp ) ) ); ?></span>
									</div>
									<?php if ( $index < count( array_slice($session['path'], 0, 8) ) - 1 ) : ?><span class="step-arrow">&rarr;</span><?php endif; ?>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<style>
		.wpci-dashboard .wpci-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin-bottom: 15px; }
		.wpci-dashboard .half-grid { grid-template-columns: 2fr 1fr; }
		.wpci-dashboard .full-grid { grid-template-columns: 1fr; }
		.wpci-dashboard .wpci-card { background: #fff; border: 1px solid #ccd0d4; padding: 15px; border-radius: 4px; }
		.wpci-dashboard h3 { margin-top: 0; }
		.wpci-dashboard .score-display { display: flex; align-items: baseline; gap: 5px; margin: 10px 0; }
		.wpci-dashboard .score-value { font-size: 28px; font-weight: bold; }
		.wpci-dashboard .score-value.green { color: #46b450; }
		.wpci-dashboard .score-value.yellow { color: #ffb900; }
		.wpci-dashboard .score-value.red { color: #dc3232; }
		.wpci-dashboard .advisor-card { border-left: 4px solid #2271b1; background: #f0f7ff; }
		.wpci-dashboard .simulator-card { border-left: 4px solid #46b450; background: #f0fff4; }
		.wpci-dashboard .sim-value { font-size: 32px; font-weight: bold; color: #46b450; }
		.wpci-dashboard .sim-label { display: block; font-size: 13px; color: #646970; }
		.wpci-dashboard .sim-impact { margin-top: 15px; font-size: 13px; padding-top: 10px; border-top: 1px solid #e0e0e0; }
		.wpci-dashboard .roi-badge { padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; color: #fff; }
		.wpci-dashboard .roi-badge.high { background: #46b450; }
		.wpci-dashboard .roi-badge.medium { background: #2271b1; }
		.wpci-dashboard .roi-badge.low { background: #dc3232; }
		.wpci-dashboard .rec-item { display: flex; gap: 8px; margin-top: 8px; padding: 8px; background: #fff; border: 1px solid #d1e3f9; }
		.wpci-dashboard .robots-snippet { background: #f0f0f1; padding: 8px; border-left: 3px solid #666; font-size: 11px; }
		.wpci-dashboard .delay-badge { padding: 2px 6px; border-radius: 10px; font-size: 10px; color: #fff; }
		.wpci-dashboard .delay-badge.green { background: #46b450; }
		.wpci-dashboard .delay-badge.red { background: #dc3232; }
		.wpci-dashboard .session-path { margin-top: 10px; padding: 10px; border: 1px solid #ddd; background: #f9f9f9; }
		.wpci-dashboard .path-flow { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 8px; }
		.wpci-dashboard .path-step { padding: 4px 8px; background: #fff; border: 1px solid #ccc; font-size: 10px; }
		.wpci-dashboard .slow-row { background: #fff5f5 !important; }
		.wpci-dashboard .description { font-style: italic; font-size: 11px; color: #777; margin-bottom: 8px; }
	</style>

	<div class="wpci-actions" style="margin-top: 30px;">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="wpci_export_csv">
			<?php wp_nonce_field( 'wpci_export_csv', '_wpnonce' ); ?>
			<button type="submit" class="button button-primary">Full CSV Export</button>
		</form>
	</div>
</div>
