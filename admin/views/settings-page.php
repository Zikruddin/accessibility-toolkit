<?php
/**
 * Admin settings page view.
 *
 * @package D2i_Accessibility_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab display parameter; page access already gated by manage_options capability check.
$opts       = D2i_A11y_Plugin::get_options();
?>
<div class="wrap d2i-a11y-admin-wrap">

	<div class="d2i-a11y-admin-header">
		<div class="d2i-a11y-admin-header-logo">
			<img
				src="<?php echo esc_url( D2I_A11Y_PLUGIN_URL . 'public/images/icons/d2i-logo.png' ); ?>"
				alt="D2i Technology"
				width="80"
				height="67"
			>
		</div>
		<div class="d2i-a11y-admin-header-text">
			<h1><?php esc_html_e( 'D2i Accessibility Toolkit', 'd2i-accessibility-toolkit' ); ?></h1>
			<p><?php esc_html_e( 'Configure the floating accessibility widget for your site.', 'd2i-accessibility-toolkit' ); ?></p>
		</div>
	</div>

	<nav class="nav-tab-wrapper d2i-a11y-nav-tabs" aria-label="<?php esc_attr_e( 'Settings sections', 'd2i-accessibility-toolkit' ); ?>">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=d2i-accessibility&tab=settings' ) ); ?>"
		   class="nav-tab <?php echo 'settings' === $active_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Widget Settings', 'd2i-accessibility-toolkit' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=d2i-accessibility&tab=statement' ) ); ?>"
		   class="nav-tab <?php echo 'statement' === $active_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Statement Generator', 'd2i-accessibility-toolkit' ); ?>
		</a>
	</nav>

	<?php if ( 'settings' === $active_tab ) : ?>

	<div class="d2i-a11y-settings-layout">

		<!-- Settings form -->
		<div class="d2i-a11y-settings-main">
			<form method="post" action="options.php">
				<?php
				settings_fields( D2I_A11Y_OPTIONS_KEY . '_group' );
				do_settings_sections( 'd2i-accessibility' );
				submit_button( __( 'Save Settings', 'd2i-accessibility-toolkit' ) );
				?>
			</form>
		</div>

	</div>

	<?php elseif ( 'statement' === $active_tab ) : ?>
		<?php require D2I_A11Y_PLUGIN_DIR . 'admin/views/statement-tool.php'; ?>
	<?php endif; ?>

	<div class="d2i-a11y-admin-footer">
		<p>
			<?php
			printf(
				/* translators: 1: plugin version, 2: link to D2i Technology website */
				esc_html__( 'D2i Accessibility Toolkit v%1$s — by %2$s', 'd2i-accessibility-toolkit' ),
				esc_html( D2I_A11Y_VERSION ),
				'<a href="https://d2itechnology.com" target="_blank" rel="noopener noreferrer">D2i Technology</a>'
			);
			?>
		</p>
		<p class="d2i-a11y-disclaimer">
			<?php esc_html_e( 'This version does not provide ADA compliance and legal protection for your site.', 'd2i-accessibility-toolkit' ); ?>
			<?php esc_html_e( 'The accessibility widget assists users but does not automatically make your website fully compliant with WCAG, ADA, Section 508, or EN 301 549. Website owners remain responsible for semantic HTML, alt text, captions, and colour contrast in their own designs.', 'd2i-accessibility-toolkit' ); ?>
		</p>
	</div>

</div><!-- /.wrap -->
