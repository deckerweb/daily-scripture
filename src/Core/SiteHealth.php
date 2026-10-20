<?php
/**
 * Minimal, read-only Site Health integration.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Core;

use Deckerweb\DailyScripture\Data\YearStore;
use Deckerweb\DailyScripture\Downloads\{AnnualPackages, DownloadError};

defined( 'ABSPATH' ) || exit;

/** Inspect existing annual sources only; optional features never generate warnings. */
final class SiteHealth {
	/** Register standard WordPress diagnostics. @return void */
	public function register(): void {
		add_filter( 'site_status_tests', array( $this, 'tests' ) );
		add_filter( 'debug_information', array( $this, 'information' ) );
	}

	/**
	 * Add a single bounded local test, without cron or external requests.
	 *
	 * @param array $tests WordPress tests.
	 * @return array
	 */
	public function tests( array $tests ): array {
		$tests['direct']['daily_scripture_years'] = array(
			'label'     => __( 'Daily Scripture annual data', 'daily-scripture' ),
			'test'      => array( $this, 'check' ),
			'skip_cron' => true,
		);
		return $tests;
	}

	/** Source labels independent of optional builder or Bible-library features. @return array */
	private function sources(): array {
		return array(
			'herrnhuter' => __( 'Die Losungen', 'daily-scripture' ),
			'bible2'     => 'Bible 2.0',
		);
	}

	/**
	 * Validate only this year's files for sources with existing annual data.
	 * Missing optional sources and a fresh installation are intentionally neutral.
	 *
	 * @return array WordPress Site Health result.
	 */
	public function check(): array {
		$store     = new YearStore();
		$today     = current_datetime();
		$issues    = array();
		$installed = false;
		foreach ( $this->sources() as $source => $label ) {
			try {
				if ( ! $store->existing_years( $source ) ) {
					continue;
				}
				$installed = true;
				$record    = $store->read( $source, (int) $today->format( 'Y' ), false );
				if ( null === $record || empty( $record['days'][ $today->format( 'Y-m-d' ) ] ) ) {
					$issues[] = $label;
				}
			} catch ( \RuntimeException $error ) {
				$issues[] = $label;
			}
		}
		$description = $installed
			? __( 'The installed annual sources contain valid data for today. Optional sources and Bible editions are not checked.', 'daily-scripture' )
			: __( 'No annual sources are installed. There is nothing to check; using only the Bible library is fine.', 'daily-scripture' );
		$result      = array(
			'label'       => __( 'Daily Scripture: no annual data issues found', 'daily-scripture' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => 'Daily Scripture',
				'color' => 'blue',
			),
			'description' => '<p>' . esc_html( $description ) . '</p>',
			'actions'     => '',
			'test'        => 'daily_scripture_years',
		);
		if ( $issues ) {
			$result['label']       = __( 'Check the annual data for Daily Scripture', 'daily-scripture' );
			$result['status']      = 'recommended';
			$result['description'] = '<p>' . esc_html__( 'An installed source has missing or unreadable data for today. Check its current annual package on the data sources page.', 'daily-scripture' ) . '</p><p>' . esc_html( implode( ', ', $issues ) ) . '</p>';
			if ( current_user_can( 'manage_options' ) ) {
				$result['actions'] = '<p><a href="' . esc_url( admin_url( 'admin.php?page=daily-scripture-data' ) ) . '">' . esc_html__( 'Manage annual data', 'daily-scripture' ) . '</a></p>';
			}
		}
		return $result;
	}

	/**
	 * Add a compact support report, excluding text contents and absolute paths.
	 *
	 * @param array $information WordPress debug sections.
	 * @return array
	 */
	public function information( array $information ): array {
		$runtime               = $GLOBALS['deckerweb_library_runtime_v1'] ?? null;
		$fields                = array(
			'version'  => array(
				'label' => __( 'Plugin version', 'daily-scripture' ),
				'value' => DAILY_SCRIPTURE_VERSION,
			),
			'timezone' => array(
				'label' => __( 'Site timezone', 'daily-scripture' ),
				'value' => wp_timezone_string(),
			),
			'date'     => array(
				'label' => __( 'Local date', 'daily-scripture' ),
				'value' => current_datetime()->format( 'Y-m-d' ),
			),
		);
		$fields['environment'] = array(
			'label' => __( 'WordPress / PHP', 'daily-scripture' ),
			'value' => get_bloginfo( 'version' ) . ' / ' . PHP_VERSION,
		);
		$fields['mode']        = array(
			'label' => __( 'Installation mode', 'daily-scripture' ),
			'value' => is_multisite() ? __( 'Multisite', 'daily-scripture' ) : __( 'Single site', 'daily-scripture' ),
		);
		$network_plugins       = is_multisite() ? (array) get_site_option( 'active_sitewide_plugins', array() ) : array();
		$fields['activation']  = array(
			'label' => __( 'Plugin activation', 'daily-scripture' ),
			'value' => isset( $network_plugins[ plugin_basename( DAILY_SCRIPTURE_FILE ) ] ) ? __( 'Network-wide', 'daily-scripture' ) : __( 'Current site', 'daily-scripture' ),
		);
		$fields['library']     = array(
			'label' => 'deckerweb Library',
			'value' => is_object( $runtime ) && defined( get_class( $runtime ) . '::VERSION' ) ? constant( get_class( $runtime ) . '::VERSION' ) : __( 'Not loaded', 'daily-scripture' ),
		);
		$fields['updater']     = array(
			'label' => 'deckerweb Updater V2',
			'value' => defined( '\Deckerweb\GitHubReleaseUpdater\V2\Updater::IMPLEMENTATION_VERSION' ) ? \Deckerweb\GitHubReleaseUpdater\V2\Updater::IMPLEMENTATION_VERSION : __( 'Not loaded', 'daily-scripture' ),
		);
		foreach ( array(
			'DOM/XML' => class_exists( '\DOMDocument' ),
			'ZIP'     => class_exists( '\ZipArchive' ),
			'OpenSSL' => extension_loaded( 'openssl' ),
			'cURL'    => extension_loaded( 'curl' ),
		) as $extension => $available ) {
			$fields[ 'extension_' . sanitize_key( $extension ) ] = array(
				'label' => $extension,
				'value' => $available ? __( 'Available', 'daily-scripture' ) : __( 'Unavailable', 'daily-scripture' ),
			);
		}

		foreach ( $this->sources() as $source => $label ) {
			try {
				$years = ( new YearStore() )->existing_years( $source );
				$value = $years ? implode( ', ', $years ) : __( 'None', 'daily-scripture' );
			} catch ( \RuntimeException $error ) {
				$value = __( 'Storage could not be read', 'daily-scripture' );
			}
			$fields[ $source ]            = array(
				'label' => $label,
				'value' => $value,
			);
			$fields[ $source . '_check' ] = array(
				/* translators: %s: Annual source name. */
				'label' => sprintf( __( '%s: cached source check', 'daily-scripture' ), $label ),
				'value' => $this->source_check( $source ),
			);

		}
		$information['daily-scripture'] = array(
			'label'       => 'Daily Scripture',
			'description' => __( 'Local support information only. Source checks use the existing temporary cache and may have expired; they do not test a new connection. Only the status test validates current annual data. Verse texts, addresses and server paths are excluded.', 'daily-scripture' ),
			'fields'      => $fields,
		);
		return $information;
	}
	/**
	 * Describe only the existing check cache, never raw provider messages or URLs.
	 *
	 * @param string $source Annual provider.
	 * @return string Localized cache state and optional whitelisted codes.
	 */
	private function source_check( string $source ): string {
		$cached = ( new AnnualPackages() )->catalogue( $source );
		if ( ! $cached || ! is_int( $cached['checked'] ?? null ) || $cached['checked'] <= 0 || $cached['checked'] > time() ) {
			return __( 'No cached check (not checked or expired)', 'daily-scripture' );
		}
		$state = empty( $cached['error'] ) ? __( 'Successful', 'daily-scripture' ) : __( 'Failed', 'daily-scripture' );
		$text  = $state . ' · ' . wp_date( 'Y-m-d H:i:s T', $cached['checked'] );
		if ( ! empty( $cached['error'] ) && is_array( $cached['diagnostic'] ?? null ) ) {
			$data  = DownloadError::restore( $cached['diagnostic'] )->diagnostic();
			$text .= ' · ' . $data['reason'];
			if ( $data['http'] ) {
				$text .= ' · HTTP ' . $data['http'];
			}
			if ( $data['curl'] ) {
				$text .= ' · cURL ' . $data['curl'];
			}
		}
		return $text;
	}

	/**
	 * Reuse the Site Health fields for a copyable, read-only administrator report.
	 *
	 * @return string Plain text; no user, domain, credential or content information.
	 */
	public function support_text(): string {
		$section = $this->information( array() )['daily-scripture'];
		$lines   = array( 'Daily Scripture', $section['description'], '' );
		foreach ( $section['fields'] as $field ) {
			$lines[] = $field['label'] . ': ' . $field['value'];
		}
		return implode( "\n", $lines );
	}
}
