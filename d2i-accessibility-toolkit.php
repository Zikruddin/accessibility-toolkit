<?php
/**
 * Plugin Name:       D2i Accessibility Toolkit
 * Plugin URI:        https://wordpress.org/plugins/d2i-accessibility-toolkit/
 * Description:       A floating accessibility widget providing one-click adjustments to support WCAG 2.1 AA + 2.2, ADA Title II/III, Section 508, and EN 301 549. No external requests. No tracking. All features included free.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            D2i Technology
 * Author URI:        https://d2itechnology.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       d2i-accessibility-toolkit
 * Domain Path:       /languages
 *
 * @package D2i_Accessibility_Toolkit
 *
 * IMPORTANT NOTICE: This plugin assists users in customizing their browsing
 * experience for better accessibility. It does not automatically make a website
 * fully compliant with WCAG, ADA, Section 508, or EN 301 549. Site owners remain
 * responsible for ensuring their content (semantic HTML, alt text, headings,
 * captions, color contrast in their own designs) meets applicable standards.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'D2I_A11Y_VERSION', '1.0.1' );
define( 'D2I_A11Y_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'D2I_A11Y_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'D2I_A11Y_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'D2I_A11Y_OPTIONS_KEY', 'd2i_a11y_settings' );

require_once D2I_A11Y_PLUGIN_DIR . 'includes/class-d2i-a11y-i18n.php';
require_once D2I_A11Y_PLUGIN_DIR . 'includes/class-d2i-a11y-plugin.php';
require_once D2I_A11Y_PLUGIN_DIR . 'includes/class-d2i-a11y-frontend.php';
require_once D2I_A11Y_PLUGIN_DIR . 'includes/class-d2i-a11y-admin.php';
require_once D2I_A11Y_PLUGIN_DIR . 'includes/class-d2i-a11y-statement.php';

register_activation_hook( __FILE__, array( 'D2i_A11y_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'D2i_A11y_Plugin', 'deactivate' ) );

D2i_A11y_Plugin::get_instance();
