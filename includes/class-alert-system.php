<?php
namespace WPCI\Includes;

class AlertSystem {
	public function __construct() {
		// Possibly hook into cron or a custom action
		add_action( 'wpci_daily_check', [ $this, 'run_checks' ] );
	}

	public function run_checks() {
		$analyzer = new CrawlAnalyzer();
		$alerts = $analyzer->get_alerts();

		foreach ( $alerts as $alert ) {
			if ( 'critical' === $alert['type'] ) {
				$this->send_admin_email( $alert['message'] );
				// Feature 12: Slack integration could go here too
				do_action( 'wpci_critical_alert', $alert );
			}
		}
	}

	private function send_admin_email( $message ) {
		$to = get_option( 'admin_email' );
		$subject = '[WPCI] Critical Crawl Alert';
		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
		
		$body = "<h2>WP Crawl Intelligence Alert</h2>";
		$body .= "<p>{$message}</p>";
		$body .= "<p>View dashboard: <a href='" . admin_url( 'admin.php?page=wpci_dashboard' ) . "'>click here</a></p>";

		wp_mail( $to, $subject, $body, $headers );
	}
}
