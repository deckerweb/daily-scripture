<?php
/**
 * Translate shared updater messages through the Daily Scripture text domain.
 *
 * @package DailyScripture
 */

defined( 'ABSPATH' ) || exit;
/**
 * Resolve an engine message in the current host locale.
 *
 * @param string $message Fixed English engine message.
 * @return string Localized message or its English fallback.
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
		default:
			return $message;
	}
};
