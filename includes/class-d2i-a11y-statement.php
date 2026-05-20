<?php
/**
 * Accessibility Statement generator — creates/retrieves the statement WP page.
 *
 * @package D2i_Accessibility_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class D2i_A11y_Statement
 */
class D2i_A11y_Statement {

	public function __construct() {
		add_action( 'admin_post_d2i_a11y_generate_statement', array( $this, 'handle_generate' ) );
	}

	/**
	 * Handle the "Generate Statement Page" form submission.
	 */
	public function handle_generate() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'd2i-accessibility-toolkit' ) );
		}

		check_admin_referer( 'd2i_a11y_generate_statement' );

		$opts    = D2i_A11y_Plugin::get_options();
		$page_id = (int) $opts['statement_page_id'];

		// Do not overwrite an existing page — just redirect to it.
		if ( $page_id && get_post( $page_id ) ) {
			wp_safe_redirect( add_query_arg(
				array(
					'page'    => D2i_A11y_Admin::MENU_SLUG,
					'tab'     => 'statement',
					'notice'  => 'exists',
					'page_id' => $page_id,
				),
				admin_url( 'admin.php' )
			) );
			exit;
		}

		// Gather data from POST fields. wp_unslash() required before sanitization.
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$contact_email      = sanitize_email( wp_unslash( isset( $_POST['contact_email'] ) ? $_POST['contact_email'] : get_option( 'admin_email' ) ) );
		$conformance_status = sanitize_text_field( wp_unslash( isset( $_POST['conformance_status'] ) ? $_POST['conformance_status'] : 'partially conforms to' ) );
		$org_name           = sanitize_text_field( wp_unslash( isset( $_POST['org_name'] ) ? $_POST['org_name'] : get_option( 'blogname' ) ) );
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$site_url       = esc_url_raw( get_option( 'siteurl' ) );
		$statement_date = date_i18n( get_option( 'date_format' ) );

		$content = $this->build_statement_content( array(
			'org_name'           => $org_name,
			'site_url'           => $site_url,
			'conformance_status' => $conformance_status,
			'contact_email'      => $contact_email,
			'statement_date'     => $statement_date,
		) );

		$new_page_id = wp_insert_post( array(
			'post_title'   => __( 'Accessibility Statement', 'd2i-accessibility-toolkit' ),
			'post_name'    => 'accessibility-statement',
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => get_current_user_id(),
		), true );

		if ( is_wp_error( $new_page_id ) ) {
			wp_safe_redirect( add_query_arg(
				array( 'page' => D2i_A11y_Admin::MENU_SLUG, 'tab' => 'statement', 'notice' => 'error' ),
				admin_url( 'admin.php' )
			) );
			exit;
		}

		// Store the page ID in settings.
		$opts['statement_page_id'] = $new_page_id;
		update_option( D2I_A11Y_OPTIONS_KEY, $opts );

		wp_safe_redirect( add_query_arg(
			array(
				'page'    => D2i_A11y_Admin::MENU_SLUG,
				'tab'     => 'statement',
				'notice'  => 'created',
				'page_id' => $new_page_id,
			),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/**
	 * Build the full statement page content (HTML, translatable).
	 *
	 * @param array $data Replacement variables.
	 * @return string Post content (HTML).
	 */
	public function build_statement_content( array $data ) {
		$org    = esc_html( $data['org_name'] );
		$url    = esc_url( $data['site_url'] );
		$status = esc_html( $data['conformance_status'] );
		$email  = esc_html( $data['contact_email'] );
		$date   = esc_html( $data['statement_date'] );

		$intro = sprintf(
			/* translators: 1: Organisation name, 2: Website URL. */
			__( '%1$s is committed to ensuring digital accessibility for people with disabilities. We continually improve the user experience for everyone and apply relevant accessibility standards to our website at %2$s.', 'd2i-accessibility-toolkit' ),
			$org,
			$url
		);

		$conformance_para = sprintf(
			/* translators: %s: Conformance status string (e.g. "partially conforms to"). */
			__( '<strong>Conformance status:</strong> This website <em>%s</em> WCAG 2.1 Level AA and incorporates applicable criteria from WCAG 2.2. WCAG defines requirements for designers and developers to improve accessibility for people with disabilities. It defines three levels of conformance: Level A, Level AA, and Level AAA.', 'd2i-accessibility-toolkit' ),
			$status
		);

		$content  = '<h2>' . esc_html__( 'Commitment to Accessibility', 'd2i-accessibility-toolkit' ) . '</h2>';
		$content .= '<p>' . $intro . '</p>';

		$content .= '<h2>' . esc_html__( 'Conformance Status', 'd2i-accessibility-toolkit' ) . '</h2>';
		$content .= '<p>' . $conformance_para . '</p>';

		$content .= '<h2>' . esc_html__( 'Technical Specifications', 'd2i-accessibility-toolkit' ) . '</h2>';
		$content .= '<p>' . esc_html__( 'Accessibility of this website relies on the following technologies to work with the particular combination of web browser and any assistive technologies or plugins installed on your computer:', 'd2i-accessibility-toolkit' ) . '</p>';
		$content .= '<ul><li>HTML</li><li>CSS</li><li>JavaScript</li><li>WAI-ARIA</li></ul>';

		$content .= '<h2>' . esc_html__( 'Assistive Technology Compatibility', 'd2i-accessibility-toolkit' ) . '</h2>';
		$content .= '<p>' . esc_html__( 'This website is designed to be compatible with the following assistive technologies:', 'd2i-accessibility-toolkit' ) . '</p>';
		$content .= '<ul>';
		$content .= '<li>' . esc_html__( 'NVDA (screen reader) with Mozilla Firefox or Google Chrome', 'd2i-accessibility-toolkit' ) . '</li>';
		$content .= '<li>' . esc_html__( 'JAWS (screen reader) with Google Chrome', 'd2i-accessibility-toolkit' ) . '</li>';
		$content .= '<li>' . esc_html__( 'VoiceOver (screen reader) with Safari on macOS and iOS', 'd2i-accessibility-toolkit' ) . '</li>';
		$content .= '<li>' . esc_html__( 'TalkBack (screen reader) with Chrome on Android', 'd2i-accessibility-toolkit' ) . '</li>';
		$content .= '<li>' . esc_html__( 'Keyboard-only navigation', 'd2i-accessibility-toolkit' ) . '</li>';
		$content .= '</ul>';

		$content .= '<h2>' . esc_html__( 'Known Limitations', 'd2i-accessibility-toolkit' ) . '</h2>';
		$content .= '<p>' . esc_html__( 'Despite our best efforts, there may be some parts of our website that are not fully accessible. We are continually working to improve. If you experience a barrier, please contact us using the details below.', 'd2i-accessibility-toolkit' ) . '</p>';

		$content .= '<h2>' . esc_html__( 'Feedback and Contact', 'd2i-accessibility-toolkit' ) . '</h2>';
		$content .= '<p>' . esc_html__( 'We welcome your feedback on the accessibility of this website. Please let us know if you encounter accessibility barriers:', 'd2i-accessibility-toolkit' ) . '</p>';
		$content .= '<ul>';
		$content .= '<li>' . sprintf(
			/* translators: %s: Email address. */
			esc_html__( 'E-mail: %s', 'd2i-accessibility-toolkit' ),
			'<a href="mailto:' . esc_attr( $data['contact_email'] ) . '">' . $email . '</a>'
		) . '</li>';
		$content .= '</ul>';
		$content .= '<p>' . esc_html__( 'We try to respond to feedback within 2 business days.', 'd2i-accessibility-toolkit' ) . '</p>';

		$content .= '<h2>' . esc_html__( 'Assessment Approach', 'd2i-accessibility-toolkit' ) . '</h2>';
		$content .= '<p>' . esc_html__( 'This website was assessed by self-evaluation.', 'd2i-accessibility-toolkit' ) . '</p>';

		$content .= '<p><em>' . sprintf(
			/* translators: %s: Date string. */
			esc_html__( 'Statement date: %s', 'd2i-accessibility-toolkit' ),
			$date
		) . '</em></p>';
		$content .= '<p><em>' . sprintf(
			/* translators: %s: Date string. */
			esc_html__( 'Last reviewed: %s', 'd2i-accessibility-toolkit' ),
			$date
		) . '</em></p>';

		$content .= '<hr>';
		$content .= '<p><small>' . esc_html__( 'This statement was generated using the D2i Accessibility Toolkit for WordPress.', 'd2i-accessibility-toolkit' ) . '</small></p>';

		return $content;
	}
}
