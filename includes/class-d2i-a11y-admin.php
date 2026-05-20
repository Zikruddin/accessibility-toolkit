<?php
/**
 * Admin settings page — registers settings, renders the settings UI.
 *
 * @package D2i_Accessibility_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class D2i_A11y_Admin
 */
class D2i_A11y_Admin {

	const MENU_SLUG    = 'd2i-accessibility';
	const SETTINGS_KEY = D2I_A11Y_OPTIONS_KEY;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'plugin_action_links_' . D2I_A11Y_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
	}

	/**
	 * Add top-level menu item in the WP Admin sidebar.
	 */
	public function add_menu() {
		add_menu_page(
			__( 'D2i Accessibility Toolkit', 'd2i-accessibility-toolkit' ),
			__( 'D2i Accessibility', 'd2i-accessibility-toolkit' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_settings_page' ),
			'dashicons-universal-access',
			80
		);
	}

	/**
	 * Register settings via the Settings API.
	 */
	public function register_settings() {
		register_setting(
			self::SETTINGS_KEY . '_group',
			self::SETTINGS_KEY,
			array(
				'sanitize_callback' => array( $this, 'sanitize_options' ),
			)
		);

		// Section: Widget Appearance.
		add_settings_section(
			'd2i_a11y_appearance',
			__( 'Widget Appearance', 'd2i-accessibility-toolkit' ),
			null,
			self::MENU_SLUG
		);

		// Section: Features.
		add_settings_section(
			'd2i_a11y_features',
			__( 'Enabled Features', 'd2i-accessibility-toolkit' ),
			array( $this, 'features_section_description' ),
			self::MENU_SLUG
		);

		// Section: Behaviour.
		add_settings_section(
			'd2i_a11y_behaviour',
			__( 'Behaviour & Display Rules', 'd2i-accessibility-toolkit' ),
			null,
			self::MENU_SLUG
		);

		$opts = D2i_A11y_Plugin::get_options();

		// Appearance fields.
		$this->add_field( 'd2i_a11y_appearance', 'widget_position',   __( 'Widget Position', 'd2i-accessibility-toolkit' ),           'render_field_position' );
		$this->add_field( 'd2i_a11y_appearance', 'primary_color',     __( 'Primary Color', 'd2i-accessibility-toolkit' ),              'render_field_color' );
		$this->add_field( 'd2i_a11y_appearance', 'icon_style',        __( 'Trigger Icon', 'd2i-accessibility-toolkit' ),               'render_field_icon_style' );
		$this->add_field( 'd2i_a11y_appearance', 'trigger_size',      __( 'Trigger Size', 'd2i-accessibility-toolkit' ),               'render_field_trigger_size' );
		$this->add_field( 'd2i_a11y_appearance', 'show_powered_by',   __( 'Show "Powered by" Credit', 'd2i-accessibility-toolkit' ),   'render_field_powered_by' );

		// Features field (single complex field).
		add_settings_field(
			'd2i_a11y_enabled_features',
			__( 'Enabled Features', 'd2i-accessibility-toolkit' ),
			array( $this, 'render_field_features' ),
			self::MENU_SLUG,
			'd2i_a11y_features'
		);

		// Behaviour fields.
		$this->add_field( 'd2i_a11y_behaviour', 'show_on',          __( 'Show Widget On', 'd2i-accessibility-toolkit' ),           'render_field_show_on' );
		$this->add_field( 'd2i_a11y_behaviour', 'disable_on_admin', __( 'Disable on Admin / Login Pages', 'd2i-accessibility-toolkit' ), 'render_field_disable_admin' );
		$this->add_field( 'd2i_a11y_behaviour', 'statement_page_id', __( 'Accessibility Statement Page', 'd2i-accessibility-toolkit' ), 'render_field_statement_page' );
	}

	/**
	 * Helper: add a settings field.
	 *
	 * @param string $section   Section ID.
	 * @param string $field_key Option key.
	 * @param string $label     Field label.
	 * @param string $callback  Render callback method name.
	 */
	private function add_field( $section, $field_key, $label, $callback ) {
		add_settings_field(
			'd2i_a11y_' . $field_key,
			$label,
			array( $this, $callback ),
			self::MENU_SLUG,
			$section
		);
	}

	/**
	 * Sanitize and validate all options before saving.
	 *
	 * @param mixed $input Raw POST data.
	 * @return array Sanitized options.
	 */
	public function sanitize_options( $input ) {
		$defaults = D2i_A11y_Plugin::default_options();
		$output   = $defaults;

		if ( ! is_array( $input ) ) {
			return $defaults;
		}

		// Widget position.
		$valid_positions = array( 'bottom-right', 'bottom-left', 'top-right', 'top-left', 'custom' );
		if ( isset( $input['widget_position'] ) && in_array( $input['widget_position'], $valid_positions, true ) ) {
			$output['widget_position'] = $input['widget_position'];
		}

		// Custom position values (each is a positive integer or "auto").
		foreach ( array( 'custom_position_top', 'custom_position_right', 'custom_position_bottom', 'custom_position_left' ) as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$val = trim( sanitize_text_field( $input[ $field ] ) );
				$output[ $field ] = ( '' === $val || 'auto' === $val ) ? 'auto' : (string) absint( $val );
			}
		}

		// Primary color.
		if ( isset( $input['primary_color'] ) ) {
			$color = sanitize_hex_color( $input['primary_color'] );
			$output['primary_color'] = $color ?: '#003366';
		}

		// Icon style.
		$valid_icons = array( 'accessibility', 'person', 'universal', 'eye' );
		if ( isset( $input['icon_style'] ) && in_array( $input['icon_style'], $valid_icons, true ) ) {
			$output['icon_style'] = $input['icon_style'];
		}

		// Trigger size.
		$valid_sizes = array( 'small', 'medium', 'large' );
		if ( isset( $input['trigger_size'] ) && in_array( $input['trigger_size'], $valid_sizes, true ) ) {
			$output['trigger_size'] = $input['trigger_size'];
		}

		// Booleans.
		$bool_fields = array( 'show_powered_by', 'disable_on_admin' );
		foreach ( $bool_fields as $field ) {
			$output[ $field ] = ! empty( $input[ $field ] );
		}

		// Show on.
		$valid_show = array( 'all', 'specific' );
		if ( isset( $input['show_on'] ) && in_array( $input['show_on'], $valid_show, true ) ) {
			$output['show_on'] = $input['show_on'];
		}

		// Exclude pages — comma-separated text input.
		if ( isset( $input['exclude_pages_raw'] ) ) {
			$parts = explode( ',', sanitize_text_field( $input['exclude_pages_raw'] ) );
			$output['exclude_pages'] = array_values( array_filter( array_map( 'absint', $parts ) ) );
		}

		// Include pages (specific mode) — comma-separated text input.
		if ( isset( $input['show_on_pages_raw'] ) ) {
			$parts = explode( ',', sanitize_text_field( $input['show_on_pages_raw'] ) );
			$output['show_on_pages'] = array_values( array_filter( array_map( 'absint', $parts ) ) );
		}

		// Enabled features.
		$all_features = array(
			'contrast', 'highlight_links', 'bigger_text', 'text_spacing',
			'pause_animations', 'hide_images', 'dyslexia', 'cursor',
			'line_height', 'text_align',
		);
		if ( isset( $input['enabled_features'] ) && is_array( $input['enabled_features'] ) ) {
			$output['enabled_features'] = array_intersect( $input['enabled_features'], $all_features );
		}

		// Statement page ID.
		if ( isset( $input['statement_page_id'] ) ) {
			$output['statement_page_id'] = absint( $input['statement_page_id'] );
		}

		return $output;
	}

	/**
	 * Enqueue admin-only CSS and JS.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_' . self::MENU_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'd2i-a11y-admin',
			D2I_A11Y_PLUGIN_URL . 'admin/css/d2i-a11y-admin.css',
			array( 'wp-color-picker' ),
			D2I_A11Y_VERSION
		);

		wp_enqueue_script(
			'd2i-a11y-admin',
			D2I_A11Y_PLUGIN_URL . 'admin/js/d2i-a11y-admin.js',
			array( 'wp-color-picker' ),
			D2I_A11Y_VERSION,
			true
		);

		$admin_opts = D2i_A11y_Plugin::get_options();
		wp_localize_script(
			'd2i-a11y-admin',
			'd2iA11yAdmin',
			array(
				'pluginUrl'      => D2I_A11Y_PLUGIN_URL,
				'nonce'          => wp_create_nonce( 'd2i_a11y_preview' ),
				'widgetPosition' => $admin_opts['widget_position'],
				'showOn'         => $admin_opts['show_on'],
			)
		);
	}

	/**
	 * Add Settings link on Plugins list page.
	 *
	 * @param array $links Existing action links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG ) ),
			esc_html__( 'Settings', 'd2i-accessibility-toolkit' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Render the main settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'd2i-accessibility-toolkit' ) );
		}
		require D2I_A11Y_PLUGIN_DIR . 'admin/views/settings-page.php';
	}

	// -------------------------------------------------------------------------
	// Individual field renderers
	// -------------------------------------------------------------------------

	public function render_field_position() {
		$opts    = D2i_A11y_Plugin::get_options();
		$current = $opts['widget_position'];
		$key     = esc_attr( self::SETTINGS_KEY );
		$options = array(
			'bottom-right' => __( 'Bottom Right (default)', 'd2i-accessibility-toolkit' ),
			'bottom-left'  => __( 'Bottom Left', 'd2i-accessibility-toolkit' ),
			'top-right'    => __( 'Top Right', 'd2i-accessibility-toolkit' ),
			'top-left'     => __( 'Top Left', 'd2i-accessibility-toolkit' ),
			'custom'       => __( 'Custom Position', 'd2i-accessibility-toolkit' ),
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $key is esc_attr( self::SETTINGS_KEY ), a constant string.
		echo '<select name="' . $key . '[widget_position]" id="d2i_a11y_position">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $key is pre-escaped.
		foreach ( $options as $value => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $value ),
				selected( $current, $value, false ),
				esc_html( $label )
			);
		}
		echo '</select>';

		// Custom position fields — shown only when "Custom Position" is selected.
		$is_custom = 'custom' === $current;
		$top    = esc_attr( $opts['custom_position_top']    ?? 'auto' );
		$right  = esc_attr( $opts['custom_position_right']  ?? '24' );
		$bottom = esc_attr( $opts['custom_position_bottom'] ?? '24' );
		$left   = esc_attr( $opts['custom_position_left']   ?? 'auto' );
		?>
		<?php // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- $key, $top, $bottom, $left, $right are all pre-escaped with esc_attr(). ?>
		<div id="d2i-a11y-custom-pos" style="margin-top:14px;padding:14px;background:#f9f9f9;border:1px solid #ddd;border-radius:6px;max-width:460px;<?php echo $is_custom ? '' : 'display:none;'; ?>">
			<p style="margin:0 0 10px;font-weight:600;"><?php esc_html_e( 'Custom Position (px)', 'd2i-accessibility-toolkit' ); ?></p>
			<table style="border-collapse:separate;border-spacing:8px;">
				<tr>
					<td>
						<label for="d2i_cp_top" style="display:block;margin-bottom:4px;font-size:12px;"><?php esc_html_e( 'Top', 'd2i-accessibility-toolkit' ); ?></label>
						<input type="text" id="d2i_cp_top" name="<?php echo $key; ?>[custom_position_top]"
							value="<?php echo $top; ?>" placeholder="auto"
							style="width:80px;" pattern="^(auto|\d+)$">
					</td>
					<td>
						<label for="d2i_cp_bottom" style="display:block;margin-bottom:4px;font-size:12px;"><?php esc_html_e( 'Bottom', 'd2i-accessibility-toolkit' ); ?></label>
						<input type="text" id="d2i_cp_bottom" name="<?php echo $key; ?>[custom_position_bottom]"
							value="<?php echo $bottom; ?>" placeholder="auto"
							style="width:80px;" pattern="^(auto|\d+)$">
					</td>
					<td>
						<label for="d2i_cp_left" style="display:block;margin-bottom:4px;font-size:12px;"><?php esc_html_e( 'Left', 'd2i-accessibility-toolkit' ); ?></label>
						<input type="text" id="d2i_cp_left" name="<?php echo $key; ?>[custom_position_left]"
							value="<?php echo $left; ?>" placeholder="auto"
							style="width:80px;" pattern="^(auto|\d+)$">
					</td>
					<td>
						<label for="d2i_cp_right" style="display:block;margin-bottom:4px;font-size:12px;"><?php esc_html_e( 'Right', 'd2i-accessibility-toolkit' ); ?></label>
						<input type="text" id="d2i_cp_right" name="<?php echo $key; ?>[custom_position_right]"
							value="<?php echo $right; ?>" placeholder="auto"
							style="width:80px;" pattern="^(auto|\d+)$">
					</td>
				</tr>
			</table>
			<p class="description" style="margin-top:8px;"><?php esc_html_e( 'Enter pixel numbers (e.g. 24) or "auto". Set one of top/bottom and one of left/right.', 'd2i-accessibility-toolkit' ); ?></p>
		</div>
		<?php // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
	}

	public function render_field_color() {
		$opts = D2i_A11y_Plugin::get_options();
		printf(
			'<input type="text" id="d2i_a11y_color" name="%s[primary_color]" value="%s" class="d2i-a11y-color-picker" data-default-color="#003366">',
			esc_attr( self::SETTINGS_KEY ),
			esc_attr( $opts['primary_color'] )
		);
		echo '<p class="description">' . esc_html__( 'Brand color used for the trigger button and panel accents.', 'd2i-accessibility-toolkit' ) . '</p>';
	}

	public function render_field_icon_style() {
		$opts    = D2i_A11y_Plugin::get_options();
		$current = $opts['icon_style'];
		$icons   = array(
			'universal'     => __( 'Universal Access (default)', 'd2i-accessibility-toolkit' ),
			'person'        => __( 'Person Silhouette', 'd2i-accessibility-toolkit' ),
			'eye'           => __( 'Eye', 'd2i-accessibility-toolkit' ),
		);
		echo '<select name="' . esc_attr( self::SETTINGS_KEY ) . '[icon_style]" id="d2i_a11y_icon_style">';
		foreach ( $icons as $value => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $value ),
				selected( $current, $value, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}

	public function render_field_trigger_size() {
		$opts    = D2i_A11y_Plugin::get_options();
		$current = $opts['trigger_size'];
		$sizes   = array(
			'small'  => __( 'Small (48px)', 'd2i-accessibility-toolkit' ),
			'medium' => __( 'Medium (56px) — default', 'd2i-accessibility-toolkit' ),
			'large'  => __( 'Large (64px)', 'd2i-accessibility-toolkit' ),
		);
		echo '<select name="' . esc_attr( self::SETTINGS_KEY ) . '[trigger_size]" id="d2i_a11y_trigger_size">';
		foreach ( $sizes as $value => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $value ),
				selected( $current, $value, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}


	public function features_section_description() {
		echo '<p>' . esc_html__( 'Uncheck features to hide them from the widget panel.', 'd2i-accessibility-toolkit' ) . '</p>';
	}

	public function render_field_features() {
		$opts     = D2i_A11y_Plugin::get_options();
		$enabled  = (array) $opts['enabled_features'];
		$all      = array(
			'contrast'          => __( 'Contrast Modes', 'd2i-accessibility-toolkit' ),
			'highlight_links'   => __( 'Highlight Links', 'd2i-accessibility-toolkit' ),
			'bigger_text'       => __( 'Bigger Text', 'd2i-accessibility-toolkit' ),
			'text_spacing'      => __( 'Text Spacing (WCAG 1.4.12)', 'd2i-accessibility-toolkit' ),
			'pause_animations'  => __( 'Pause Animations', 'd2i-accessibility-toolkit' ),
			'hide_images'       => __( 'Hide Images', 'd2i-accessibility-toolkit' ),
			'dyslexia'          => __( 'Dyslexia Friendly Font', 'd2i-accessibility-toolkit' ),
			'cursor'            => __( 'Big Cursor', 'd2i-accessibility-toolkit' ),
			'line_height'       => __( 'Line Height', 'd2i-accessibility-toolkit' ),
			'text_align'        => __( 'Text Alignment', 'd2i-accessibility-toolkit' ),
		);
		echo '<fieldset><legend class="screen-reader-text">' . esc_html__( 'Enabled Features', 'd2i-accessibility-toolkit' ) . '</legend>';
		foreach ( $all as $key => $label ) {
			printf(
				'<label style="display:block;margin-bottom:6px"><input type="checkbox" name="%s[enabled_features][]" value="%s"%s> %s</label>',
				esc_attr( self::SETTINGS_KEY ),
				esc_attr( $key ),
				checked( in_array( $key, $enabled, true ), true, false ),
				esc_html( $label )
			);
		}
		echo '</fieldset>';
	}

	public function render_field_show_on() {
		$opts    = D2i_A11y_Plugin::get_options();
		$current = $opts['show_on'];
		$key     = esc_attr( self::SETTINGS_KEY );

		$exclude = implode( ', ', array_map( 'absint', (array) $opts['exclude_pages'] ) );
		$include = implode( ', ', array_map( 'absint', (array) $opts['show_on_pages'] ) );
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- $key pre-escaped.
		?>
		<fieldset>
			<label style="display:block;margin-bottom:8px">
				<input type="radio" name="<?php echo $key; ?>[show_on]" value="all"
					<?php checked( $current, 'all' ); ?>
					class="d2i-show-on-radio">
				<?php esc_html_e( 'All pages (default)', 'd2i-accessibility-toolkit' ); ?>
			</label>

			<div id="d2i-a11y-exclude-wrap" style="margin:0 0 12px 20px;<?php echo 'all' !== $current ? 'display:none' : ''; ?>">
				<label for="d2i_a11y_exclude_pages"><strong><?php esc_html_e( 'Exclude from these pages (IDs, comma-separated):', 'd2i-accessibility-toolkit' ); ?></strong></label><br>
				<input type="text" id="d2i_a11y_exclude_pages"
					name="<?php echo $key; ?>[exclude_pages_raw]"
					value="<?php echo esc_attr( $exclude ); ?>"
					class="regular-text"
					placeholder="<?php esc_attr_e( 'e.g. 42, 57, 103', 'd2i-accessibility-toolkit' ); ?>">
				<p class="description"><?php esc_html_e( 'Widget will appear on all pages except the IDs listed here.', 'd2i-accessibility-toolkit' ); ?></p>
			</div>

			<label style="display:block;margin-bottom:8px">
				<input type="radio" name="<?php echo $key; ?>[show_on]" value="specific"
					<?php checked( $current, 'specific' ); ?>
					class="d2i-show-on-radio">
				<?php esc_html_e( 'Specific pages only', 'd2i-accessibility-toolkit' ); ?>
			</label>

			<div id="d2i-a11y-include-wrap" style="margin:0 0 12px 20px;<?php echo 'specific' !== $current ? 'display:none' : ''; ?>">
				<label for="d2i_a11y_include_pages"><strong><?php esc_html_e( 'Show only on these pages (IDs, comma-separated):', 'd2i-accessibility-toolkit' ); ?></strong></label><br>
				<input type="text" id="d2i_a11y_include_pages"
					name="<?php echo $key; ?>[show_on_pages_raw]"
					value="<?php echo esc_attr( $include ); ?>"
					class="regular-text"
					placeholder="<?php esc_attr_e( 'e.g. 10, 25, 88', 'd2i-accessibility-toolkit' ); ?>">
				<p class="description"><?php esc_html_e( 'Widget will appear only on the page IDs listed here.', 'd2i-accessibility-toolkit' ); ?></p>
			</div>
		</fieldset>
		<?php // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
	}

	public function render_field_disable_admin() {
		$opts = D2i_A11y_Plugin::get_options();
		printf(
			'<label><input type="checkbox" name="%s[disable_on_admin]" value="1"%s> %s</label>',
			esc_attr( self::SETTINGS_KEY ),
			checked( $opts['disable_on_admin'], true, false ),
			esc_html__( 'Do not show widget on WP Admin and wp-login.php (recommended)', 'd2i-accessibility-toolkit' )
		);
	}

	public function render_field_statement_page() {
		$opts    = D2i_A11y_Plugin::get_options();
		$page_id = (int) $opts['statement_page_id'];

		// Page dropdown. Args are escaped inline so PHPCS can track them.
		wp_dropdown_pages( array(
			'name'              => esc_attr( self::SETTINGS_KEY ) . '[statement_page_id]',
			'id'                => 'd2i_a11y_statement_page',
			'selected'          => absint( $page_id ),
			'show_option_none'  => esc_html__( '— None selected —', 'd2i-accessibility-toolkit' ),
			'option_none_value' => '0',
		) );

		echo '<p class="description">';
		if ( $page_id && get_post( $page_id ) ) {
			printf(
				'<a href="%s" target="_blank">%s</a> &bull; <a href="%s">%s</a>',
				esc_url( get_permalink( $page_id ) ),
				esc_html__( 'View statement', 'd2i-accessibility-toolkit' ),
				esc_url( get_edit_post_link( $page_id ) ),
				esc_html__( 'Edit statement', 'd2i-accessibility-toolkit' )
			);
		}
		echo '</p>';

		// Statement generator button (separate action, rendered via statement tool view).
		echo '<p>';
		printf(
			'<a href="%s" class="button button-secondary">%s</a>',
			esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG . '&tab=statement' ) ),
			esc_html__( 'Go to Statement Generator →', 'd2i-accessibility-toolkit' )
		);
		echo '</p>';
	}

	public function render_field_powered_by() {
		$opts = D2i_A11y_Plugin::get_options();
		printf(
			'<label><input type="checkbox" name="%s[show_powered_by]" value="1"%s> %s</label><p class="description">%s</p>',
			esc_attr( self::SETTINGS_KEY ),
			checked( $opts['show_powered_by'], true, false ),
			esc_html__( 'Show "Powered by D2i Technology" in the widget footer', 'd2i-accessibility-toolkit' ),
			esc_html__( 'Displays a small credit link in the widget panel. Site owners must opt in to this — unchecking removes it entirely.', 'd2i-accessibility-toolkit' )
		);
	}

}
