<?php
/**
 * Front-end: enqueue assets, inject early-paint script, render widget HTML.
 *
 * @package D2i_Accessibility_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class D2i_A11y_Frontend
 */
class D2i_A11y_Frontend {

	/** @var array Plugin options */
	private $options;

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_widget' ), 100 );
	}

	/**
	 * Check whether the widget should appear on the current page.
	 */
	private function should_show() {
		$opts = D2i_A11y_Plugin::get_options();

		// Honour admin/login exclusion.
		if ( $opts['disable_on_admin'] && ( is_admin() || $GLOBALS['pagenow'] === 'wp-login.php' ) ) {
			return false;
		}

		if ( 'all' === $opts['show_on'] ) {
			// Exclude specific pages.
			if ( ! empty( $opts['exclude_pages'] ) ) {
				$post_id = get_queried_object_id();
				if ( in_array( $post_id, array_map( 'intval', (array) $opts['exclude_pages'] ), true ) ) {
					return false;
				}
			}
			return true;
		}

		if ( 'specific' === $opts['show_on'] ) {
			$post_id = get_queried_object_id();
			$allowed  = array_map( 'intval', (array) $opts['show_on_pages'] );
			return in_array( $post_id, $allowed, true );
		}

		return true;
	}

	/**
	 * Enqueue front-end CSS and JS.
	 */
	public function enqueue_assets() {
		if ( ! $this->should_show() ) {
			return;
		}

		$opts = D2i_A11y_Plugin::get_options();

		wp_enqueue_style(
			'd2i-a11y-features',
			D2I_A11Y_PLUGIN_URL . 'public/css/d2i-a11y-features.css',
			array(),
			D2I_A11Y_VERSION
		);

		wp_enqueue_style(
			'd2i-a11y-widget',
			D2I_A11Y_PLUGIN_URL . 'public/css/d2i-a11y-widget.css',
			array( 'd2i-a11y-features' ),
			D2I_A11Y_VERSION
		);

		wp_enqueue_script(
			'd2i-a11y-widget',
			D2I_A11Y_PLUGIN_URL . 'public/js/d2i-a11y-widget.js',
			array(),
			D2I_A11Y_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		// Pass settings and translatable strings to JS.
		wp_localize_script(
			'd2i-a11y-widget',
			'd2iA11ySettings',
			$this->get_js_settings( $opts )
		);

		// Inline CSS custom properties — brand color + derived hover shade.
		$color       = sanitize_hex_color( $opts['primary_color'] ) ?: '#003366';
		$hover_color = $this->darken_hex( $color, 20 );
		$size_px     = $this->trigger_size_px( $opts['trigger_size'] );
		$pos_css     = $this->position_css( $opts['widget_position'] );

		$inline = sprintf(
			':root { --d2i-a11y-brand: %s; --d2i-a11y-brand-hover: %s; --d2i-a11y-tile-active: %s; --d2i-a11y-trigger-size: %spx; %s }',
			esc_attr( $color ),
			esc_attr( $hover_color ),
			esc_attr( $color ),
			esc_attr( $size_px ),
			$pos_css
		);
		wp_add_inline_style( 'd2i-a11y-widget', $inline );

		// Register a head-positioned handle (false src = no file, false in_footer = in <head>)
		// then attach the early-preference script via the WP inline script API.
		wp_register_script( 'd2i-a11y-early-prefs', false, array(), D2I_A11Y_VERSION, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooterNoAsyncDefer
		wp_enqueue_script( 'd2i-a11y-early-prefs' );
		// phpcs:disable WordPress.WP.InlineScripts -- Inline script required for head placement to prevent FOUC.
		$early_js = <<<'JS'
(function(){try{
var p=JSON.parse(localStorage.getItem('d2i_a11y_user_prefs')||'{}');
var h=document.documentElement;
var cm={dark:'d2i-a11y-contrast-dark',light:'d2i-a11y-contrast-light',high:'d2i-a11y-contrast-high'};
if(p.contrast&&cm[p.contrast])h.classList.add(cm[p.contrast]);
if(p.biggerText&&p.biggerText!=='100')h.classList.add('d2i-a11y-bigger-text-'+p.biggerText);
if(p.textSpacing)h.classList.add('d2i-a11y-text-spacing');
if(p.pauseAnimations||(window.matchMedia&&window.matchMedia('(prefers-reduced-motion:reduce)').matches))h.classList.add('d2i-a11y-pause-animations');
if(p.hideImages)h.classList.add('d2i-a11y-hide-images');
if(p.dyslexia)h.classList.add('d2i-a11y-dyslexia');
if(p.cursor&&p.cursor!=='default')h.classList.add('d2i-a11y-cursor-'+p.cursor);
if(p.lineHeight&&p.lineHeight!=='default')h.classList.add('d2i-a11y-line-height-'+p.lineHeight);
if(p.textAlign&&p.textAlign!=='default')h.classList.add('d2i-a11y-text-align-'+p.textAlign);
if(p.highlightLinks)h.classList.add('d2i-a11y-highlight-links');
}catch(e){}}());
JS;
		// phpcs:enable
		wp_add_inline_script( 'd2i-a11y-early-prefs', $early_js );
	}

	/**
	 * Render the full widget HTML in wp_footer.
	 */
	public function render_widget() {
		if ( ! $this->should_show() ) {
			return;
		}

		$opts             = D2i_A11y_Plugin::get_options();
		$enabled_features = (array) $opts['enabled_features'];
		$position_class   = 'd2i-a11y-pos-' . sanitize_html_class( $opts['widget_position'] );
		$statement_url    = $opts['statement_page_id'] ? get_permalink( (int) $opts['statement_page_id'] ) : '';

		?>
<div id="d2i-a11y-wrapper" class="d2i-a11y-wrapper <?php echo esc_attr( $position_class ); ?>" data-position="<?php echo esc_attr( $opts['widget_position'] ); ?>">

	<!-- Floating trigger button -->
	<button
		id="d2i-a11y-trigger"
		type="button"
		class="d2i-a11y-trigger"
		aria-label="<?php esc_attr_e( 'Open accessibility menu', 'd2i-accessibility-toolkit' ); ?>"
		aria-expanded="false"
		aria-controls="d2i-a11y-panel"
	>
		<?php echo $this->get_trigger_icon( $opts['icon_style'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG from trusted internal method. ?>
		<span class="d2i-a11y-sr-only"><?php esc_html_e( 'Open accessibility menu', 'd2i-accessibility-toolkit' ); ?></span>
	</button>

	<!-- Accessibility panel -->
	<div
		id="d2i-a11y-panel"
		class="d2i-a11y-panel"
		role="dialog"
		aria-modal="true"
		aria-labelledby="d2i-a11y-title"
		aria-hidden="true"
	>
		<!-- Panel header -->
		<div class="d2i-a11y-panel-header">
			<img
				src="<?php echo esc_url( D2I_A11Y_PLUGIN_URL . 'public/images/icons/d2i-logo.png' ); ?>"
				alt="D2i Technology"
				class="d2i-a11y-logo"
				width="60"
				height="50"
				loading="lazy"
			>
			<h2 id="d2i-a11y-title" class="d2i-a11y-panel-title">
				<?php esc_html_e( 'Accessibility Tools', 'd2i-accessibility-toolkit' ); ?>
			</h2>
			<button
				id="d2i-a11y-close"
				type="button"
				class="d2i-a11y-close-btn"
				aria-label="<?php esc_attr_e( 'Close accessibility menu', 'd2i-accessibility-toolkit' ); ?>"
			>
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" fill="currentColor"/></svg>
			</button>
		</div>

		<!-- Panel body -->
		<div class="d2i-a11y-panel-body" id="d2i-a11y-panel-body">

			<!-- Polite aria-live region for state announcements -->
			<div id="d2i-a11y-live" aria-live="polite" aria-atomic="true" class="d2i-a11y-sr-only"></div>

			<!-- Pre-made accessibility profiles -->
			<div class="d2i-a11y-profiles-section" id="d2i-a11y-profiles-section">
				<h3 class="d2i-a11y-profiles-label"><?php esc_html_e( 'Quick Profiles', 'd2i-accessibility-toolkit' ); ?></h3>
				<div class="d2i-a11y-profiles-grid" id="d2i-a11y-profiles-grid">
					<?php $this->render_profile_buttons(); ?>
				</div>
			</div>

			<!-- Individual feature tiles -->
			<div class="d2i-a11y-features-grid">
				<?php $this->render_feature_tiles( $enabled_features ); ?>
			</div>

		</div><!-- /.d2i-a11y-panel-body -->

		<!-- Inline accessibility statement view (shown when "Statement" is clicked) -->
		<div id="d2i-a11y-statement-view" class="d2i-a11y-statement-view" aria-hidden="true" hidden>
			<div class="d2i-a11y-statement-nav">
				<button type="button" id="d2i-a11y-back" class="d2i-a11y-back-btn">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" fill="currentColor"/></svg>
					<?php esc_html_e( 'Back', 'd2i-accessibility-toolkit' ); ?>
				</button>
				<!-- <strong class="d2i-a11y-statement-nav-title"><?php esc_html_e( 'Accessibility Statement', 'd2i-accessibility-toolkit' ); ?></strong> -->
			</div>
			<div class="d2i-a11y-statement-content">
				<?php echo $this->render_inline_statement( $opts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output is built with WP escaping functions. ?>
			</div>
		</div><!-- /.d2i-a11y-statement-view -->

		<!-- Panel footer -->
		<div class="d2i-a11y-panel-footer">
			<button type="button" id="d2i-a11y-reset" class="d2i-a11y-reset-btn">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z" fill="currentColor"/></svg>
				<?php esc_html_e( 'Reset All', 'd2i-accessibility-toolkit' ); ?>
			</button>
			<!-- Statement button always shown — opens inline view -->
			<button type="button" id="d2i-a11y-statement-btn" class="d2i-a11y-statement-link">
				<?php esc_html_e( 'Statement', 'd2i-accessibility-toolkit' ); ?>
			</button>
			<?php if ( $opts['show_powered_by'] ) : ?>
			<div class="d2i-a11y-powered-by">
				<?php esc_html_e( 'Powered by', 'd2i-accessibility-toolkit' ); ?>
				<a
					href="https://d2itechnology.com"
					target="_blank"
					rel="noopener noreferrer"
					class="d2i-a11y-powered-by-link"
				><?php esc_html_e( 'D2i Technology', 'd2i-accessibility-toolkit' ); ?></a>
			</div>
			<?php endif; ?>
		</div><!-- /.d2i-a11y-panel-footer -->

	</div><!-- /#d2i-a11y-panel -->

</div><!-- /#d2i-a11y-wrapper -->
		<?php
	}

	/**
	 * Build the inline accessibility statement shown inside the widget panel.
	 *
	 * @param array $opts Plugin options.
	 * @return string Escaped HTML string.
	 */
	private function render_inline_statement( array $opts ) {
		$site_name = 'D2i Technology';
		$site_url  = 'https://d2itechnology.com';
		$email     = 'info@d2itechnology.com';

		$html  = '<h3>' . esc_html__( 'Accessibility Commitment', 'd2i-accessibility-toolkit' ) . '</h3>';
		$html .= '<p>' . sprintf(
			/* translators: 1: Site name, 2: Site URL. */
			esc_html__( '%1$s is committed to ensuring digital accessibility for people with disabilities. We continuously improve the user experience for everyone and apply relevant accessibility standards across %2$s.', 'd2i-accessibility-toolkit' ),
			'<strong>' . $site_name . '</strong>',
			'<a href="' . $site_url . '" target="_blank" rel="noopener">' . $site_url . '</a>'
		) . '</p>';

		$html .= '<h3>' . esc_html__( 'Our Approach', 'd2i-accessibility-toolkit' ) . '</h3>';
		$html .= '<p>' . esc_html__( 'We aim to align with the Web Content Accessibility Guidelines (WCAG) 2.1 Level AA. These guidelines explain how to make web content more accessible to people with disabilities.', 'd2i-accessibility-toolkit' ) . '</p>';

		$html .= '<h3>' . esc_html__( 'Technical Specifications', 'd2i-accessibility-toolkit' ) . '</h3>';
		$html .= '<p>' . esc_html__( 'This plugin relies on HTML, CSS, JavaScript, and WAI-ARIA for accessibility. The D2i Accessibility Toolkit widget provides user-side adjustments to support a more accessible browsing experience.', 'd2i-accessibility-toolkit' ) . '</p>';

		$html .= '<h3>' . esc_html__( 'Feedback and Contact', 'd2i-accessibility-toolkit' ) . '</h3>';
		$html .= '<p>' . esc_html__( 'We welcome your feedback on the accessibility of this plugin. If you encounter barriers or need assistance, please contact us:', 'd2i-accessibility-toolkit' ) . '</p>';
		if ( $email ) {
			$html .= '<p><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></p>';
		}
		$html .= '<p>' . esc_html__( 'We try to respond to feedback within 2 business days.', 'd2i-accessibility-toolkit' ) . '</p>';

		$html .= '<div class="d2i-a11y-legal-notice">';
		$html .= '<strong>' . esc_html__( 'Important Notice:', 'd2i-accessibility-toolkit' ) . '</strong> ';
		$html .= esc_html__( 'This version does not provide ADA compliance and legal protection for your site. The accessibility widget assists users but does not automatically make your website fully compliant with WCAG, ADA, Section 508, or EN 301 549. Content authors remain responsible for semantic HTML, alt text, captions, and color contrast.', 'd2i-accessibility-toolkit' );
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render pre-made profile buttons.
	 */
	private function render_profile_buttons() {
		$profiles = array(
			array(
				'id'    => 'vision_impaired',
				'label' => __( 'Vision Impaired', 'd2i-accessibility-toolkit' ),
				'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/></svg>',
			),
			array(
				'id'    => 'seizure_safe',
				'label' => __( 'Seizure Safe', 'd2i-accessibility-toolkit' ),
				'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="currentColor"/></svg>',
			),
			array(
				'id'    => 'adhd_friendly',
				'label' => __( 'ADHD Friendly', 'd2i-accessibility-toolkit' ),
				'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z" fill="currentColor"/></svg>',
			),
			array(
				'id'    => 'blindness_mode',
				'label' => __( 'Blindness Mode', 'd2i-accessibility-toolkit' ),
				'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27z" fill="currentColor"/></svg>',
			),
			array(
				'id'    => 'epilepsy_safe',
				'label' => __( 'Epilepsy Safe', 'd2i-accessibility-toolkit' ),
				'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z" fill="currentColor"/></svg>',
			),
		);

		foreach ( $profiles as $profile ) {
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- $icon is hardcoded trusted SVG.
			printf(
				'<button type="button" class="d2i-a11y-profile-btn" data-profile="%s" aria-pressed="false"><span class="d2i-a11y-profile-icon">%s</span><span>%s</span></button>',
				esc_attr( $profile['id'] ),
				$profile['icon'],
				esc_html( $profile['label'] )
			);
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Render all enabled feature tiles.
	 *
	 * @param array $enabled List of enabled feature IDs.
	 */
	private function render_feature_tiles( array $enabled ) {
		$all_features = $this->feature_definitions();
		foreach ( $all_features as $feature ) {
			if ( ! in_array( $feature['id'], $enabled, true ) ) {
				continue;
			}
			$this->render_tile( $feature );
		}
	}

	/**
	 * Render a single feature tile button.
	 *
	 * @param array $feature Feature definition array.
	 */
	private function render_tile( array $feature ) {
		$id          = esc_attr( $feature['id'] );
		$label       = esc_html( $feature['label'] );
		$type        = esc_attr( $feature['type'] );
		$icon        = $feature['icon']; // Pre-sanitized SVG string.
		$default_val = esc_attr( $feature['default'] );

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- $id/$label/$first_label are pre-escaped; $icon is hardcoded trusted SVG.
		if ( 'toggle' === $feature['type'] ) {
			printf(
				'<button type="button" class="d2i-a11y-tile" data-feature="%s" data-type="toggle" aria-pressed="false"><span class="d2i-a11y-tile-icon">%s</span><span class="d2i-a11y-tile-label">%s</span></button>',
				$id,
				$icon,
				$label,
				esc_html__( 'Off', 'd2i-accessibility-toolkit' )
			);
		} else {
			// Cycle — aria-label includes the current state ("Contrast: Off").
			// JS updates aria-label on every click so SRs announce the new value
			// from the label change alone — no aria-live needed, no double read.
			$first_label = esc_html( $feature['steps'][0]['label'] );
			printf(
				'<button type="button" class="d2i-a11y-tile" data-feature="%s" data-type="cycle" data-step="0" aria-label="%s: %s"><span class="d2i-a11y-tile-icon">%s</span><span class="d2i-a11y-tile-label">%s</span><span class="d2i-a11y-tile-state" aria-hidden="true">%s</span></button>',
				$id,
				$label,
				$first_label,
				$icon,
				$label,
				$first_label
			);
		}
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * All feature definitions (labels, icons, step metadata).
	 * The heavy lifting (class toggling) lives in JS; PHP only needs labels/icons
	 * to render accessible initial HTML.
	 *
	 * @return array[]
	 */
	private function feature_definitions() {
		return array(
			array(
				'id'      => 'contrast',
				'type'    => 'cycle',
				'label'   => __( 'Contrast', 'd2i-accessibility-toolkit' ),
				'default' => 'off',
				'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18V4c4.41 0 8 3.59 8 8s-3.59 8-8 8z" fill="currentColor"/></svg>',
				'steps'   => array(
					array( 'value' => 'off',   'label' => __( 'Off', 'd2i-accessibility-toolkit' ) ),
					array( 'value' => 'dark',  'label' => __( 'Dark', 'd2i-accessibility-toolkit' ) ),
					array( 'value' => 'light', 'label' => __( 'Light', 'd2i-accessibility-toolkit' ) ),
					array( 'value' => 'high',  'label' => __( 'High Contrast', 'd2i-accessibility-toolkit' ) ),
				),
			),
			array(
				'id'      => 'highlight_links',
				'type'    => 'toggle',
				'label'   => __( 'Highlight Links', 'd2i-accessibility-toolkit' ),
				'default' => false,
				'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z" fill="currentColor"/></svg>',
			),
			array(
				'id'      => 'bigger_text',
				'type'    => 'cycle',
				'label'   => __( 'Bigger Text', 'd2i-accessibility-toolkit' ),
				'default' => '100',
				'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M9 4v3h5v12h3V7h5V4H9zm-6 8h3v7h3v-7h3V9H3v3z" fill="currentColor"/></svg>',
				'steps'   => array(
					array( 'value' => '100', 'label' => __( 'Normal', 'd2i-accessibility-toolkit' ) ),
					array( 'value' => '120', 'label' => '120%' ),
					array( 'value' => '140', 'label' => '140%' ),
					array( 'value' => '160', 'label' => '160%' ),
					array( 'value' => '180', 'label' => '180%' ),
					array( 'value' => '200', 'label' => '200%' ),
				),
			),
			array(
				'id'      => 'text_spacing',
				'type'    => 'toggle',
				'label'   => __( 'Text Spacing', 'd2i-accessibility-toolkit' ),
				'default' => false,
				'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M4 16h16v2H4v-2zm0-5h16v2H4v-2zm0-5h16v2H4V6z" fill="currentColor"/></svg>',
			),
			array(
				'id'      => 'pause_animations',
				'type'    => 'toggle',
				'label'   => __( 'Pause Animations', 'd2i-accessibility-toolkit' ),
				'default' => false,
				'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" fill="currentColor"/></svg>',
			),
			array(
				'id'      => 'hide_images',
				'type'    => 'toggle',
				'label'   => __( 'Hide Images', 'd2i-accessibility-toolkit' ),
				'default' => false,
				'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M21 5v6.59l-3-3.01-4 4.01-4-4-4 4-3-3.01V5c0-1.1.9-2 2-2h14c1.1 0 2 .9 2 2zm-3 6.42l3 3.01V19c0 1.1-.9 2-2 2H5c-1.1 0-2-.9-2-2v-6.58l3 2.99 4-4 4 4 4-3.99z" fill="currentColor"/></svg>',
			),
			array(
				'id'      => 'dyslexia',
				'type'    => 'toggle',
				'label'   => __( 'Dyslexia Friendly', 'd2i-accessibility-toolkit' ),
				'default' => false,
				'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><text x="2" y="19" font-size="15" font-weight="bold" fill="currentColor" font-family="serif">Aa</text></svg>',
			),
			array(
				'id'      => 'cursor',
				'type'    => 'cycle',
				'label'   => __( 'Cursor', 'd2i-accessibility-toolkit' ),
				'default' => 'default',
				'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M4 0l16 12.279-6.951 1.17 4.325 8.817-3.596 1.734-4.35-8.879-5.428 4.702z" fill="currentColor"/></svg>',
				'steps'   => array(
					array( 'value' => 'default', 'label' => __( 'Default', 'd2i-accessibility-toolkit' ) ),
					array( 'value' => 'white',   'label' => __( 'Big White', 'd2i-accessibility-toolkit' ) ),
					array( 'value' => 'black',   'label' => __( 'Big Black', 'd2i-accessibility-toolkit' ) ),
				),
			),
			array(
				'id'      => 'line_height',
				'type'    => 'cycle',
				'label'   => __( 'Line Height', 'd2i-accessibility-toolkit' ),
				'default' => 'default',
				'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M10 13h10v-2H10v2zm0-4h10V7H10v2zm0 8h10v-2H10v2zM6 7H4v2H2V7H.5L3 4.5 5.5 7H4zm0 10H2v-2H.5L3 17.5 5.5 15H4v-2H6v4z" fill="currentColor"/></svg>',
				'steps'   => array(
					array( 'value' => 'default', 'label' => __( 'Default', 'd2i-accessibility-toolkit' ) ),
					array( 'value' => '150',     'label' => '1.5' ),
					array( 'value' => '175',     'label' => '1.75' ),
					array( 'value' => '200',     'label' => '2.0' ),
					array( 'value' => '250',     'label' => '2.5' ),
				),
			),
			array(
				'id'      => 'text_align',
				'type'    => 'cycle',
				'label'   => __( 'Text Align', 'd2i-accessibility-toolkit' ),
				'default' => 'default',
				'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M15 15H3v2h12v-2zm0-8H3v2h12V7zM3 13h18v-2H3v2zm0 8h18v-2H3v2zM3 3v2h18V3H3z" fill="currentColor"/></svg>',
				'steps'   => array(
					array( 'value' => 'default', 'label' => __( 'Default', 'd2i-accessibility-toolkit' ) ),
					array( 'value' => 'left',    'label' => __( 'Left', 'd2i-accessibility-toolkit' ) ),
					array( 'value' => 'center',  'label' => __( 'Center', 'd2i-accessibility-toolkit' ) ),
					array( 'value' => 'right',   'label' => __( 'Right', 'd2i-accessibility-toolkit' ) ),
				),
			),
		array(
			'id'      => 'readable_font',
			'type'    => 'toggle',
			'label'   => __( 'Readable Font', 'd2i-accessibility-toolkit' ),
			'default' => false,
			'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M9.93 13.5h4.14L12 7.98zM20 2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-4.05 16.5l-1.14-3H9.17l-1.12 3H5.96l5.11-13h1.86l5.11 13h-2.09z" fill="currentColor"/></svg>',
		),
		array(
			'id'      => 'bold_text',
			'type'    => 'toggle',
			'label'   => __( 'Bold Text', 'd2i-accessibility-toolkit' ),
			'default' => false,
			'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z" fill="currentColor"/></svg>',
		),
		array(
			'id'      => 'highlight_titles',
			'type'    => 'toggle',
			'label'   => __( 'Highlight Titles', 'd2i-accessibility-toolkit' ),
			'default' => false,
			'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M5 4v3h5.5v12h3V7H19V4z" fill="currentColor"/></svg>',
		),
		array(
			'id'      => 'mute_sounds',
			'type'    => 'toggle',
			'label'   => __( 'Mute Sounds', 'd2i-accessibility-toolkit' ),
			'default' => false,
			'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z" fill="currentColor"/></svg>',
		),
		array(
			'id'      => 'reading_guide',
			'type'    => 'toggle',
			'label'   => __( 'Reading Guide', 'd2i-accessibility-toolkit' ),
			'default' => false,
			'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z" fill="currentColor"/></svg>',
		),
		array(
			'id'      => 'keyboard_nav',
			'type'    => 'toggle',
			'label'   => __( 'Keyboard Nav', 'd2i-accessibility-toolkit' ),
			'default' => false,
			'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M20 5H4c-1.1 0-1.99.9-1.99 2L2 17c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm-9 3h2v2h-2V8zm0 3h2v2h-2v-2zM8 8h2v2H8V8zm0 3h2v2H8v-2zm-1 2H5v-2h2v2zm0-3H5V8h2v2zm9 7H8v-2h8v2zm0-4h-2v-2h2v2zm0-3h-2V8h2v2zm3 3h-2v-2h2v2zm0-3h-2V8h2v2z" fill="currentColor"/></svg>',
		),
		);
	}

	/**
	 * Build the JS settings object passed via wp_localize_script.
	 *
	 * @param array $opts Plugin options.
	 * @return array
	 */
	private function get_js_settings( array $opts ) {
		$statement_url = $opts['statement_page_id'] ? get_permalink( (int) $opts['statement_page_id'] ) : '';

		return array(
			'pluginUrl'       => D2I_A11Y_PLUGIN_URL,
			'autoShow'        => (bool) ( $opts['auto_show'] ?? false ),
			'enabledFeatures' => array_values( (array) $opts['enabled_features'] ),
			'statementUrl'    => $statement_url ? esc_url( $statement_url ) : '',
			'showPoweredBy'   => (bool) $opts['show_powered_by'],
			'i18n'            => array(
				'open'             => __( 'Open accessibility menu', 'd2i-accessibility-toolkit' ),
				'close'            => __( 'Close accessibility menu', 'd2i-accessibility-toolkit' ),
				'reset'            => __( 'Reset All', 'd2i-accessibility-toolkit' ),
				'statement'        => __( 'Accessibility Statement', 'd2i-accessibility-toolkit' ),
				'poweredBy'        => __( 'Powered by', 'd2i-accessibility-toolkit' ),
				'poweredByLink'    => __( 'D2i Technology', 'd2i-accessibility-toolkit' ),
				'off'              => __( 'Off', 'd2i-accessibility-toolkit' ),
				'on'               => __( 'On', 'd2i-accessibility-toolkit' ),
			),
		);
	}

	/**
	 * Return trigger button SVG icon.
	 *
	 * @param string $style Icon style key.
	 * @return string Safe SVG markup.
	 */
	private function get_trigger_icon( $style ) {
		$icons = array(
			'universal'     => '<svg width="60px" height="60px" viewBox="0 0 60 60" version="1.1" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" xmlns:xlink="http://www.w3.org/1999/xlink"><title>man</title><g id="Drawer" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="man" transform="translate(-30.000000, -30.000000)"><g transform="translate(30.000000, 30.000000)" id="Group-12"><circle id="Oval" cx="30" cy="30" r="30"></circle><path d="M30,4.42857143 C44.12271,4.42857143 55.5714286,15.87729 55.5714286,30 C55.5714286,44.12271 44.12271,55.5714286 30,55.5714286 C15.87729,55.5714286 4.42857143,44.12271 4.42857143,30 C4.42857143,15.87729 15.87729,4.42857143 30,4.42857143 Z M30,6.42857143 C16.9818595,6.42857143 6.42857143,16.9818595 6.42857143,30 C6.42857143,43.0181405 16.9818595,53.5714286 30,53.5714286 C43.0181405,53.5714286 53.5714286,43.0181405 53.5714286,30 C53.5714286,16.9818595 43.0181405,6.42857143 30,6.42857143 Z M40.5936329,24.636146 C40.8208154,24.6942382 41.032297,24.8027599 41.212927,24.9537151 C41.3927444,25.1040671 41.5372605,25.2927156 41.6362456,25.506032 C41.7348561,25.7185411 41.7857143,25.9504498 41.7857143,26.1964545 C41.7780029,26.5779794 41.6395197,26.9452414 41.3935596,27.2352841 C41.1463511,27.5267988 40.8059352,27.7221149 40.4376358,27.7856619 C38.1921773,28.2017648 35.924387,28.4827808 33.6481064,28.6271294 C33.504948,28.636723 33.3651112,28.6758744 33.236922,28.7423749 C33.1082304,28.8090766 32.9940039,28.9018917 32.9011681,29.0153772 C32.8079332,29.1293505 32.7382931,29.2617886 32.6966918,29.404413 C32.6758615,29.4759144 32.6622539,29.5492793 32.6556797,29.6151616 L32.6510699,29.707205 L32.6598659,29.8496307 L32.8523035,31.5976067 C33.0926408,33.748446 33.5345387,35.8701755 34.1700609,37.9296172 L34.4174424,38.6989233 L34.6845982,39.467246 L35.9271291,42.8464114 C35.9992453,43.0440742 36.0318055,43.2541674 36.0229684,43.4645736 C36.0141278,43.6750654 35.9640303,43.8817121 35.8754594,44.0726551 C35.7867069,44.2638976 35.6611068,44.435479 35.5058759,44.5773262 C35.3501721,44.7195962 35.1677426,44.8289881 34.990022,44.8912207 C34.813373,44.9615763 34.6253467,44.9984764 34.4204191,45 C34.1147901,44.9943164 33.8175473,44.8987335 33.5650597,44.7252745 C33.4238771,44.6283171 33.2997507,44.5091367 33.1890431,44.3580526 L33.0826737,44.1959755 L33.0074053,44.0456077 L32.6901551,43.3562659 C31.8320879,41.4806152 31.0484874,39.6428286 30.3335907,37.8221303 L30.0024971,36.9627165 L29.5751047,38.0696169 C29.3403684,38.6636654 29.0998399,39.2560704 28.8536693,39.8464776 L28.4802005,40.730546 L27.9043756,42.0504488 L27.3109116,43.3600706 L27.0273167,43.9425803 C26.8810403,44.3389204 26.5849764,44.6608321 26.2034873,44.8369557 C25.8203243,45.0138521 25.3831542,45.0287926 24.9891662,44.8783588 C24.596572,44.7285499 24.2795594,44.4271943 24.1072539,44.0414047 C23.9885793,43.7756939 23.9446874,43.4836867 23.9834048,43.1768668 L24.016611,42.9910892 L24.0667666,42.8262042 L25.307875,39.4507095 C26.0439275,37.4198431 26.5851782,35.3222044 26.9239335,33.1916604 L27.0414597,32.3912301 L27.141282,31.5772235 L27.3403361,29.8381618 C27.3581635,29.6889408 27.3459492,29.5375642 27.3045081,29.3935084 C27.2630999,29.2497044 27.1934915,29.1162414 27.1000261,29.0011883 C27.0070148,28.8866944 26.8923305,28.7928596 26.7631114,28.7253145 C26.6343439,28.6580256 26.4937323,28.6181655 26.35351,28.6082966 C24.0561093,28.4626746 21.7692364,28.17737 19.5069975,27.7542651 C19.3015835,27.7165557 19.1057712,27.6379419 18.9308258,27.5230481 C18.7563857,27.408486 18.6063103,27.2602422 18.4889941,27.0867756 C18.3721069,26.9139017 18.2901967,26.7194847 18.2478998,26.5149205 C18.2055002,26.3103882 18.2034637,26.0993152 18.2403615,25.9020167 C18.2758029,25.695193 18.3515339,25.4974971 18.4633288,25.3201771 C18.5754166,25.1425366 18.7215515,24.9891682 18.8933065,24.8690391 C19.0655425,24.7486376 19.2599761,24.6643395 19.4651939,24.6211361 C19.6706526,24.577882 19.8826185,24.5767675 20.0822706,24.6166765 C26.6343689,25.8477827 33.3528511,25.8477827 39.8979716,24.6180222 C40.1283133,24.5717053 40.3659882,24.5779122 40.5936329,24.636146 Z M32.8056386,16.182956 C34.3520224,17.7551666 34.3520224,20.3006423 32.80563,21.8728616 C31.2542658,23.450066 28.7353061,23.450066 27.1840106,21.8728616 C25.6375563,20.3006489 25.6375563,17.7551599 27.1839933,16.1829647 C28.7352993,14.6056799 31.2542726,14.6056799 32.8056386,16.182956 Z" id="Combined-Shape" fill="currentColor" fill-rule="nonzero"></path></g></g></g></svg>',
			'person'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="currentColor"/></svg>',
			'eye'           => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/></svg>',
		);

		$style = sanitize_key( $style );
		return isset( $icons[ $style ] ) ? $icons[ $style ] : $icons['universal'];
	}

	/**
	 * Return D2i logo SVG for panel header.
	 *
	 * @return string
	 */
	// get_logo_svg() removed — replaced by inline <img> tag pointing to d2i-logo.png.

	/**
	 * Trigger size in pixels.
	 *
	 * @param string $size small|medium|large.
	 * @return int
	 */
	private function trigger_size_px( $size ) {
		$map = array( 'small' => 48, 'medium' => 56, 'large' => 64 );
		return isset( $map[ $size ] ) ? $map[ $size ] : 56;
	}

	/**
	 * Generate CSS custom properties for widget position.
	 *
	 * @param string $position Widget position key.
	 * @return string CSS declarations (no selector wrapper).
	 */
	private function position_css( $position ) {
		if ( 'custom' === $position ) {
			$opts = D2i_A11y_Plugin::get_options();
			return sprintf(
				'--d2i-a11y-top:%s;--d2i-a11y-right:%s;--d2i-a11y-bottom:%s;--d2i-a11y-left:%s;',
				$this->pos_val( $opts['custom_position_top']    ?? 'auto' ),
				$this->pos_val( $opts['custom_position_right']  ?? '24' ),
				$this->pos_val( $opts['custom_position_bottom'] ?? '24' ),
				$this->pos_val( $opts['custom_position_left']   ?? 'auto' )
			);
		}

		$positions = array(
			'bottom-right' => '--d2i-a11y-bottom:24px;--d2i-a11y-right:24px;--d2i-a11y-top:auto;--d2i-a11y-left:auto;',
			'bottom-left'  => '--d2i-a11y-bottom:24px;--d2i-a11y-left:24px;--d2i-a11y-top:auto;--d2i-a11y-right:auto;',
			'top-right'    => '--d2i-a11y-top:24px;--d2i-a11y-right:24px;--d2i-a11y-bottom:auto;--d2i-a11y-left:auto;',
			'top-left'     => '--d2i-a11y-top:24px;--d2i-a11y-left:24px;--d2i-a11y-bottom:auto;--d2i-a11y-right:auto;',
		);
		return isset( $positions[ $position ] ) ? $positions[ $position ] : $positions['bottom-right'];
	}

	/**
	 * Convert a saved position value to a CSS value.
	 * Stored as a plain integer string (e.g. "24") or "auto".
	 *
	 * @param string $val Stored value.
	 * @return string CSS value like "24px" or "auto".
	 */
	private function pos_val( $val ) {
		$val = trim( (string) $val );
		return ( '' === $val || 'auto' === $val ) ? 'auto' : absint( $val ) . 'px';
	}

	/**
	 * Darken a hex color by reducing each RGB channel by a fixed percentage.
	 *
	 * @param string $hex     Hex color string (with or without leading #).
	 * @param int    $percent Percentage to darken (0–100).
	 * @return string Darkened hex color with leading #.
	 */
	private function darken_hex( $hex, $percent = 20 ) {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		$amount = (int) round( 255 * $percent / 100 );
		$r      = max( 0, hexdec( substr( $hex, 0, 2 ) ) - $amount );
		$g      = max( 0, hexdec( substr( $hex, 2, 2 ) ) - $amount );
		$b      = max( 0, hexdec( substr( $hex, 4, 2 ) ) - $amount );
		return sprintf( '#%02x%02x%02x', $r, $g, $b );
	}
}
