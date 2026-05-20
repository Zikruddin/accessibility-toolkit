<?php
/**
 * Accessibility Statement Generator tool view.
 *
 * @package D2i_Accessibility_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$opts    = D2i_A11y_Plugin::get_options();
$page_id = (int) $opts['statement_page_id'];
$notice  = isset( $_GET['notice'] ) ? sanitize_key( $_GET['notice'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; admin page already gated by manage_options capability check.
$notice_page_id = isset( $_GET['page_id'] ) ? absint( $_GET['page_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; admin page already gated by manage_options capability check.
?>

<div class="d2i-a11y-statement-tool">

	<h2><?php esc_html_e( 'Accessibility Statement Generator', 'd2i-accessibility-toolkit' ); ?></h2>

	<?php if ( 'created' === $notice && $notice_page_id ) : ?>
	<div class="notice notice-success is-dismissible">
		<p>
			<?php esc_html_e( 'Accessibility Statement page created successfully!', 'd2i-accessibility-toolkit' ); ?>
			<a href="<?php echo esc_url( get_permalink( $notice_page_id ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View page', 'd2i-accessibility-toolkit' ); ?></a>
			&bull;
			<a href="<?php echo esc_url( get_edit_post_link( $notice_page_id ) ); ?>"><?php esc_html_e( 'Edit page', 'd2i-accessibility-toolkit' ); ?></a>
		</p>
	</div>
	<?php elseif ( 'exists' === $notice && $notice_page_id ) : ?>
	<div class="notice notice-info is-dismissible">
		<p>
			<?php esc_html_e( 'A statement page already exists — we did not overwrite your edits.', 'd2i-accessibility-toolkit' ); ?>
			<a href="<?php echo esc_url( get_permalink( $notice_page_id ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View existing page', 'd2i-accessibility-toolkit' ); ?></a>
		</p>
	</div>
	<?php elseif ( 'error' === $notice ) : ?>
	<div class="notice notice-error is-dismissible">
		<p><?php esc_html_e( 'There was a problem creating the page. Please try again or create it manually.', 'd2i-accessibility-toolkit' ); ?></p>
	</div>
	<?php endif; ?>

	<p><?php esc_html_e( 'Use this tool to generate a ready-made Accessibility Statement page for your site. The page will be published with a template that you can customise afterward.', 'd2i-accessibility-toolkit' ); ?></p>

	<?php if ( $page_id && get_post( $page_id ) ) : ?>
	<div class="notice notice-info inline">
		<p>
			<strong><?php esc_html_e( 'An Accessibility Statement page is already linked.', 'd2i-accessibility-toolkit' ); ?></strong>
			<?php esc_html_e( 'Generating a new one will create a second page. To update the existing page, edit it directly.', 'd2i-accessibility-toolkit' ); ?>
			&mdash;
			<a href="<?php echo esc_url( get_permalink( $page_id ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View', 'd2i-accessibility-toolkit' ); ?></a>
			&bull;
			<a href="<?php echo esc_url( get_edit_post_link( $page_id ) ); ?>"><?php esc_html_e( 'Edit', 'd2i-accessibility-toolkit' ); ?></a>
		</p>
	</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="d2i-a11y-statement-form">
		<input type="hidden" name="action" value="d2i_a11y_generate_statement">
		<?php wp_nonce_field( 'd2i_a11y_generate_statement' ); ?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="d2i_stmt_org"><?php esc_html_e( 'Organisation / Site Name', 'd2i-accessibility-toolkit' ); ?></label>
				</th>
				<td>
					<input type="text" id="d2i_stmt_org" name="org_name" class="regular-text"
						value="<?php echo esc_attr( get_option( 'blogname' ) ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="d2i_stmt_status"><?php esc_html_e( 'Conformance Status', 'd2i-accessibility-toolkit' ); ?></label>
				</th>
				<td>
					<select id="d2i_stmt_status" name="conformance_status">
						<option value="partially conforms to"><?php esc_html_e( 'Partially conforms to WCAG 2.1 AA (default)', 'd2i-accessibility-toolkit' ); ?></option>
						<option value="fully conforms to"><?php esc_html_e( 'Fully conforms to WCAG 2.1 AA', 'd2i-accessibility-toolkit' ); ?></option>
						<option value="does not yet conform to"><?php esc_html_e( 'Does not yet conform to WCAG 2.1 AA', 'd2i-accessibility-toolkit' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Choose an honest status. "Partially conforms" is the safest default.', 'd2i-accessibility-toolkit' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="d2i_stmt_email"><?php esc_html_e( 'Accessibility Contact Email', 'd2i-accessibility-toolkit' ); ?></label>
				</th>
				<td>
					<input type="email" id="d2i_stmt_email" name="contact_email" class="regular-text"
						value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
					<p class="description"><?php esc_html_e( 'Users will contact this address with accessibility issues.', 'd2i-accessibility-toolkit' ); ?></p>
				</td>
			</tr>
		</table>

		<p class="submit">
			<button type="submit" class="button button-primary">
				<?php esc_html_e( 'Generate Accessibility Statement Page', 'd2i-accessibility-toolkit' ); ?>
			</button>
		</p>

	</form>

	<hr>

	<h3><?php esc_html_e( 'What the statement page includes', 'd2i-accessibility-toolkit' ); ?></h3>
	<ul>
		<li><?php esc_html_e( 'Commitment to accessibility statement', 'd2i-accessibility-toolkit' ); ?></li>
		<li><?php esc_html_e( 'WCAG 2.1 Level AA conformance status', 'd2i-accessibility-toolkit' ); ?></li>
		<li><?php esc_html_e( 'Compatible assistive technologies list', 'd2i-accessibility-toolkit' ); ?></li>
		<li><?php esc_html_e( 'Technical specifications (HTML, CSS, JS, ARIA)', 'd2i-accessibility-toolkit' ); ?></li>
		<li><?php esc_html_e( 'Known limitations and alternatives', 'd2i-accessibility-toolkit' ); ?></li>
		<li><?php esc_html_e( 'Feedback and contact information', 'd2i-accessibility-toolkit' ); ?></li>
		<li><?php esc_html_e( 'Assessment approach and review date', 'd2i-accessibility-toolkit' ); ?></li>
	</ul>
	<p><?php esc_html_e( 'After generating, open the page in the WordPress editor to customise the text to match your organisation\'s actual conformance state.', 'd2i-accessibility-toolkit' ); ?></p>

</div>
