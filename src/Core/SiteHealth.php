<?php
/**
 * Minimal, read-only Site Health integration.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Core;

use Deckerweb\DailyScripture\Data\YearStore;

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
		$fields = array(
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
		foreach ( $this->sources() as $source => $label ) {
			try {
				$years = ( new YearStore() )->existing_years( $source );
				$value = $years ? implode( ', ', $years ) : __( 'None', 'daily-scripture' );
			} catch ( \RuntimeException $error ) {
				$value = __( 'Storage could not be read', 'daily-scripture' );
			}
			$fields[ $source ] = array(
				'label' => $label,
				'value' => $value,
			);
		}
		$information['daily-scripture'] = array(
			'label'       => 'Daily Scripture',
			'description' => __( 'Existing annual files are listed by year. Only the status test validates the current annual data. No verse texts or server paths are included.', 'daily-scripture' ),
			'fields'      => $fields,
		);
		return $information;
	}
}
