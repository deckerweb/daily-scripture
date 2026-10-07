<?php
/**
 * Clean temporary data; preserve site settings and texts unless explicitly requested.
 *
 * @package DailyScripture
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

spl_autoload_register(
	/**
	 * Load plugin classes without booting services or scheduling work.
	 *
	 * @param string $class_name Fully qualified class name.
	 * @return void
	 */
	static function ( $class_name ) {
		$prefix = 'Deckerweb\\DailyScripture\\';
		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}
		$file = __DIR__ . '/src/' . str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) ) . '.php';
		if ( is_file( $file ) ) {
			require_once $file;
		}
	}
);

Deckerweb\DailyScripture\Core\Cleanup::sites( array( Deckerweb\DailyScripture\Core\Cleanup::class, 'site' ), true );

// A repository-specific update cache belongs to each network, not to each site.
$daily_scripture_cache = 'ddw_ghru_' . substr( md5( 'https://github.com/deckerweb/daily-scripture' ), 0, 24 );
if ( is_multisite() ) {
	$daily_scripture_offset = 0;
	do {
		$daily_scripture_networks = get_networks(
			array(
				'fields'  => 'ids',
				'number'  => 100,
				'offset'  => $daily_scripture_offset,
				'orderby' => 'id',
				'order'   => 'ASC',
			)
		);
		foreach ( $daily_scripture_networks as $daily_scripture_network ) {
			delete_network_option( $daily_scripture_network, '_site_transient_' . $daily_scripture_cache );
			delete_network_option( $daily_scripture_network, '_site_transient_timeout_' . $daily_scripture_cache );
		}
		$daily_scripture_count   = count( $daily_scripture_networks );
		$daily_scripture_offset += $daily_scripture_count;
	} while ( 100 === $daily_scripture_count );
	wp_cache_delete( $daily_scripture_cache, 'site-transient' );
} else {
	delete_site_transient( $daily_scripture_cache );
}

require_once __DIR__ . '/includes/deckerweb-plugin-library/lifecycle.php';
deckerweb_library_uninstall_v2( __DIR__ . '/daily-scripture.php' );
