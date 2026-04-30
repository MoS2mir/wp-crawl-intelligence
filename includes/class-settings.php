<?php
namespace WPCI\Includes;

class Settings {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function add_settings_page() {
		add_submenu_page(
			'wpci_dashboard',
			'WPCI Settings',
			'Settings',
			'manage_options',
			'wpci_settings',
			[ $this, 'render_settings_page' ]
		);
	}

	public function register_settings() {
		register_setting( 'wpci_settings_group', 'wpci_retention_days', [
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 30,
		] );

		register_setting( 'wpci_settings_group', 'wpci_enable_email_alerts', [
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => false,
		] );

		register_setting( 'wpci_settings_group', 'wpci_alert_email', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_email',
			'default'           => get_option( 'admin_email' ),
		] );

		register_setting( 'wpci_settings_group', 'wpci_ignored_ips', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		] );
	}

	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1>WPCI Settings</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wpci_settings_group' );
				do_settings_sections( 'wpci_settings_group' );
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Data Retention (Days)</th>
						<td>
							<input type="number" name="wpci_retention_days" value="<?php echo esc_attr( get_option( 'wpci_retention_days', 30 ) ); ?>" />
							<p class="description">How many days of logs to keep in the database. Older logs will be deleted daily.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Enable Email Alerts</th>
						<td>
							<input type="checkbox" name="wpci_enable_email_alerts" value="1" <?php checked( 1, get_option( 'wpci_enable_email_alerts' ) ); ?> />
							<p class="description">Send email notifications for critical crawl issues.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Alert Email Address</th>
						<td>
							<input type="email" name="wpci_alert_email" value="<?php echo esc_attr( get_option( 'wpci_alert_email', get_option( 'admin_email' ) ) ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Ignored IPs</th>
						<td>
							<textarea name="wpci_ignored_ips" class="large-text" rows="3"><?php echo esc_textarea( get_option( 'wpci_ignored_ips' ) ); ?></textarea>
							<p class="description">Comma-separated list of IPs to ignore (e.g., your own IP).</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
