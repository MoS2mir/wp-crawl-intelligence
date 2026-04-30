<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
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

<div class="wpci-grid two-col">
    <!-- Budget Simulator -->
    <div class="wpci-card simulator-card">
        <div class="wpci-card-header">
            <h3><span class="dashicons dashicons-calc"></span> Crawl Budget Simulator</h3>
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

    <div class="wpci-card">
        <div class="wpci-card-header">
            <h3><span class="dashicons dashicons-money-alt"></span> Crawl ROI Stats</h3>
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
