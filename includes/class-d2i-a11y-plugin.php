<?php
/**
 * Bootstrap class — singleton entry point for the plugin.
 *
 * @package D2i_Accessibility_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class D2i_A11y_Plugin
 */
class D2i_A11y_Plugin {

	/** @var D2i_A11y_Plugin|null */
	private static $instance = null;

	/** @var D2i_A11y_Frontend */
	public $frontend;

	/** @var D2i_A11y_Admin */
	public $admin;

	/** @var D2i_A11y_Statement */
	public $statement;

	/** @var D2i_A11y_I18n */
	public $i18n;

	/**
	 * Default plugin options.
	 */
	public static function default_options() {
		return array(
			'widget_position'        => 'bottom-right',
			'show_powered_by'        => false,
			'custom_position_top'    => 'auto',
			'custom_position_right'  => '24',
			'custom_position_bottom' => '24',
			'custom_position_left'   => 'auto',
			'primary_color'        => '#003366',
			'icon_style'           => 'accessibility',
			'trigger_size'         => 'medium',
			'enabled_features'     => array(
				'contrast', 'highlight_links', 'bigger_text', 'text_spacing',
				'pause_animations', 'hide_images', 'dyslexia', 'cursor',
				'line_height', 'text_align', 'readable_font', 'bold_text',
				'highlight_titles', 'mute_sounds', 'reading_guide', 'keyboard_nav',
			),
			'show_on'              => 'all',
			'show_on_pages'        => array(),
			'exclude_pages'        => array(),
			'disable_on_admin'  => true,
			'statement_page_id' => 0,
		);
	}

	/**
	 * Get plugin options, merged with defaults.
	 *
	 * @return array
	 */
	public static function get_options() {
		$saved = get_option( D2I_A11Y_OPTIONS_KEY, array() );
		return wp_parse_args( $saved, self::default_options() );
	}

	/**
	 * Singleton accessor.
	 *
	 * @return D2i_A11y_Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — wire up hooks.
	 */
	private function __construct() {
		$this->i18n      = new D2i_A11y_I18n();
		$this->frontend  = new D2i_A11y_Frontend();
		$this->statement = new D2i_A11y_Statement();

		if ( is_admin() ) {
			$this->admin = new D2i_A11y_Admin();
		}
	}

	/**
	 * Activation hook — set default options.
	 */
	public static function activate() {
		if ( ! get_option( D2I_A11Y_OPTIONS_KEY ) ) {
			add_option( D2I_A11Y_OPTIONS_KEY, self::default_options() );
		}
		flush_rewrite_rules();
	}

	/**
	 * Deactivation hook — no destructive actions.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
