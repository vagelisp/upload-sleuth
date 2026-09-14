<?php
/**
 * Plugin Name: UploadSleuth – Media Audit & Cleanup
 * Description: Find files that may be unused in WordPress uploads, spot missing Media Library files, and safely review, quarantine, back up, or delete them.
 * Version: 1.0.5
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Vagelis P.
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: upload-sleuth
 *
 * @package UploadSleuth
 */

defined( 'ABSPATH' ) || exit;

/** Current asset and release version. */
define( 'UPLOAD_SLEUTH_VERSION', '1.0.5' );

/** Absolute plugin bootstrap path. */
define( 'UPLOAD_SLEUTH_FILE', __FILE__ );

/** Public base URL used for dashboard assets. */
define( 'UPLOAD_SLEUTH_URL', plugin_dir_url( __FILE__ ) );

/* Migrate persisted options from the pre-UploadSleuth namespace once. */
function upload_sleuth_maybe_migrate_options() {
	if ( get_option( 'upload_sleuth_namespace_migrated', false ) ) {
		return;
	}
	$map = array(
		'media_audit_settings'      => 'upload_sleuth_settings',
		'media_audit_cleanup_stats' => 'upload_sleuth_cleanup_stats',
		'media_audit_ignore_patterns' => 'upload_sleuth_ignore_patterns',
	);
	foreach ( $map as $old_key => $new_key ) {
		$old_value = get_option( $old_key, null );
		if ( null !== $old_value && false === get_option( $new_key, false ) ) {
			update_option( $new_key, $old_value, false );
		}
	}
	update_option( 'upload_sleuth_namespace_migrated', 1, false );
}
add_action( 'plugins_loaded', 'upload_sleuth_maybe_migrate_options', 1 );

require_once __DIR__ . '/includes/class-media-audit-cli-command.php';
require_once __DIR__ . '/includes/class-media-audit-admin-page.php';

/**
 * Register only the public leaf command.
 *
 * Registering the engine class as a namespace would expose its public admin-job
 * methods as accidental commands, so the callable targets media_audit directly.
 */
function media_audit_register_cli_commands() {
	if ( ! class_exists( 'Media_Audit_CLI_Command' ) ) {
		return;
	}

	if ( is_callable( array( 'WP_CLI', 'add_command' ) ) ) {
		$command = new Media_Audit_CLI_Command();
		call_user_func( array( 'WP_CLI', 'add_command' ), 'upload-sleuth', array( $command, 'media_audit' ) );
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	add_action( 'cli_init', 'media_audit_register_cli_commands' );
}

if ( is_admin() ) {
	if ( class_exists( 'Media_Audit_Admin_Page' ) ) {
		Media_Audit_Admin_Page::init();
	}
}
