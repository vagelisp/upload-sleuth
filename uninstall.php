<?php
/**
 * Remove UploadSleuth settings and temporary scan data during uninstall.
 *
 * Quarantined uploads and backup archives are intentionally preserved because
 * deleting user files during plugin removal would be unexpected and unsafe.
 *
 * @package UploadSleuth
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// Remove persistent plugin options.
delete_option( 'media_audit_settings' );
delete_option( 'media_audit_cleanup_stats' );
delete_option( 'media_audit_ignore_patterns' );

// Remove user/token-scoped scan, integrity, and download transients.
$transient_like = $wpdb->esc_like( '_transient_media_audit_' ) . '%';
$timeout_like   = $wpdb->esc_like( '_transient_timeout_media_audit_' ) . '%';
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$transient_like,
		$timeout_like
	)
);

