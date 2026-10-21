<?php
/**
 * Translate shared updater messages through the Daily Scripture host domain.
 *
 * @package DailyScripture
 */

defined( 'ABSPATH' ) || exit;
/**
 * Resolve an updater source string in the host textdomain.
 *
 * @param string $message English updater message.
 * @return string Translated host message, or original source text.
 */
return static function ( string $message ): string {
	// Literal calls let the host's normal translation extractor collect every source string.
	switch ( $message ) {
		case 'Private mode must be boolean.':
			return __( 'Private mode must be boolean.', 'daily-scripture' );
		case 'Invalid authentication provider.':
			return __( 'Invalid authentication provider.', 'daily-scripture' );
		case 'The plugin must be installed in a stable slug directory.':
			return __( 'The plugin must be installed in a stable slug directory.', 'daily-scripture' );
		case 'Invalid GitHub repository URL.':
			return __( 'Invalid GitHub repository URL.', 'daily-scripture' );
		case 'The private update could not be authorized. Check the repository credentials and refresh updates.':
			return __( 'The private update could not be authorized. Check the repository credentials and refresh updates.', 'daily-scripture' );
		case 'Could not create the update download file.':
			return __( 'Could not create the update download file.', 'daily-scripture' );
		case 'The private update download failed. Check credentials and try again.':
			return __( 'The private update download failed. Check credentials and try again.', 'daily-scripture' );
		case 'Could not access the update filesystem.':
			return __( 'Could not access the update filesystem.', 'daily-scripture' );
		case 'GitHub release does not contain the plugin main file.':
			return __( 'GitHub release does not contain the plugin main file.', 'daily-scripture' );
		case 'Could not prepare the GitHub release package.':
			return __( 'Could not prepare the GitHub release package.', 'daily-scripture' );
		case 'See the release on GitHub.':
			return __( 'See the release on GitHub.', 'daily-scripture' );
		case 'Git Updater is active. If a deckerweb installation or update fails, temporarily deactivate Git Updater and retry. Reactivate it afterwards if other plugins or themes need it. Only leave it disabled when those extensions have another reliable update provider.':
			return __( 'Git Updater is active. If a deckerweb installation or update fails, temporarily deactivate Git Updater and retry. Reactivate it afterwards if other plugins or themes need it. Only leave it disabled when those extensions have another reliable update provider.', 'daily-scripture' );
		case 'Manage installed plugins':
			return __( 'Manage installed plugins', 'daily-scripture' );
		case 'Dismiss this notice':
			return __( 'Dismiss this notice', 'daily-scripture' );
		case 'Permission denied.':
			return __( 'Permission denied.', 'daily-scripture' );
		case 'Public catalog callbacks require both providers and cannot be used in private mode.':
			return __( 'Public catalog callbacks require both providers and cannot be used in private mode.', 'daily-scripture' );
		case 'The approved release could not be verified.':
			return __( 'The approved release could not be verified.', 'daily-scripture' );
		case 'The approved release changed. Refresh updates first.':
			return __( 'The approved release changed. Refresh updates first.', 'daily-scripture' );
		default:
			return $message;
	}
};
