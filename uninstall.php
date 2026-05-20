<?php
/**
 * Uninstall — remove plugin options from the database.
 *
 * Does NOT delete the Accessibility Statement page — the site owner may have
 * customised it and should manage its removal manually.
 *
 * @package D2i_Accessibility_Toolkit
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'd2i_a11y_settings' );
