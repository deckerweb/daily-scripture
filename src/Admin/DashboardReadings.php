<?php
/**
 * Website-owned reading definitions available to personal dashboard widgets.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Admin;

use Deckerweb\DailyScripture\Bible\{Library, TranslationManager};
use Deckerweb\DailyScripture\Core\Presentation;
use Deckerweb\DailyScripture\Data\LocalFiles;

defined( 'ABSPATH' ) || exit;

/** Bounded configuration without imported texts or personal preferences. */
final class DashboardReadings {
	/** Website option containing reading definitions. */
	public const OPTION = 'daily_scripture_dashboard_readings';

	/**
	 * Validate stable source IDs and at most six independent passage definitions.
	 *
	 * @param mixed $input Submitted website configuration.
	 * @return array Valid definitions, preserving their stable slot IDs.
	 */
	public static function sanitize( $input ): array {
		$input  = is_array( $input ) ? $input : array();
		$output = array();
		foreach ( array( 'herrnhuter', 'bible2' ) as $id ) {
			if ( ! empty( $input[ $id ]['enabled'] ) ) {
				$output[ $id ] = array( 'enabled' => 1 );
			}
		}
		$editions = ( new TranslationManager() )->bundled();
		for ( $i = 1; $i <= 6; ++$i ) {
			$id  = 'passage_' . $i;
			$row = $input[ $id ] ?? null;
			if ( ! is_array( $row ) || empty( $row['enabled'] ) ) {
				continue;
			}
			if ( ! is_string( $row['translation'] ?? null ) || ! isset( $editions[ $row['translation'] ] ) || ! is_string( $row['book'] ?? null ) || ! isset( TranslationManager::books()[ $row['book'] ] ) ) {
				continue;
			}
			$numbers = array();
			foreach ( array( 'chapter', 'from', 'to' ) as $key ) {
				$value           = $row[ $key ] ?? null;
				$numbers[ $key ] = is_scalar( $value ) && preg_match( '/^[1-9]\d{0,2}$/D', (string) $value ) ? (int) $value : 0;
			}
			if ( $numbers['chapter'] < 1 || $numbers['chapter'] > 150 || $numbers['from'] < 1 || $numbers['to'] < $numbers['from'] || $numbers['to'] > 176 || $numbers['to'] - $numbers['from'] >= 50 ) {
				continue;
			}
			$output[ $id ] = array_merge(
				array(
					'enabled'     => 1,
					'translation' => $row['translation'],
					'book'        => $row['book'],
				),
				$numbers,
				array( 'title' => is_string( $row['title'] ?? null ) ? Presentation::sanitize_heading( $row['title'] ) : '' )
			);
		}
		return $output;
	}

	/**
	 * Check enabled passages against local text before storing website choices.
	 *
	 * @param mixed $input Posted configuration.
	 * @return array Safe definitions; invalid slots retain their previous definition.
	 * @throws \RuntimeException Caught locally and shown as a settings notice.
	 */
	public static function save_configuration( $input ): array {
		$clean = self::sanitize( $input );
		$input = is_array( $input ) ? $input : array();
		$old   = self::all();
		for ( $i = 1; $i <= 6; ++$i ) {
			$id = 'passage_' . $i;
			if ( empty( $input[ $id ]['enabled'] ) ) {
				continue;
			}
			try {
				if ( ! isset( $clean[ $id ] ) ) {
					throw new \RuntimeException( __( 'Please choose a valid passage of up to 50 verses within one chapter.', 'daily-scripture' ) );
				}
				$row = $clean[ $id ];
				( new Library() )->passage( $row['translation'], $row['book'], $row['chapter'], $row['from'], $row['to'] );
			} catch ( \RuntimeException $error ) {
				/* translators: 1: numbered passage slot; 2: local validation message. */
				add_settings_error( self::OPTION, $id, sprintf( __( 'Bible passage %1$d was not changed: %2$s', 'daily-scripture' ), $i, $error->getMessage() ) );
				unset( $clean[ $id ] );
				if ( isset( $old[ $id ] ) ) {
					$clean[ $id ] = $old[ $id ];
				}
			}
		}
		return $clean;
	}

	/**
	 * Read configured definitions for the current website.
	 *
	 * @return array Sanitized definitions.
	 */
	public static function all(): array {
		return self::sanitize( get_option( self::OPTION, array() ) );
	}

	/**
	 * Label a configured source without loading Bible text files.
	 *
	 * @param string $id Stable configuration ID.
	 * @param array  $row Valid definition.
	 * @return string Plain display label.
	 */
	public static function label( string $id, array $row ): string {
		if ( 'herrnhuter' === $id ) {
			return 'Die Losungen';
		}
		if ( 'bible2' === $id ) {
			return 'Bible 2.0';
		}
		$edition = TranslationManager::edition( $row['translation'] );
		return ( '' !== $row['title'] ? $row['title'] : TranslationManager::books()[ $row['book'] ] . ' ' . $row['chapter'] . ',' . $row['from'] . ( $row['from'] !== $row['to'] ? '–' . $row['to'] : '' ) ) . ' · ' . $edition['label'];
	}

	/** Render definitions within the website settings form. @return void */
	public static function fields(): void {
		$saved = self::all();
		echo '<div class="ds-widget-definitions"><p>' . esc_html__( 'Define the readings available to your dashboard users. Each user chooses their own selection and order in the widget. These settings do not change public output.', 'daily-scripture' ) . '</p>';
		foreach ( array(
			'herrnhuter' => 'Die Losungen',
			'bible2'     => 'Bible 2.0',
		) as $id => $label ) {
			echo '<p><label><input type="checkbox" name="daily_scripture_dashboard_readings[' . esc_attr( $id ) . '][enabled]" value="1" ' . checked( isset( $saved[ $id ] ), true, false ) . '> ' . esc_html( $label ) . '</label></p>';
		}
		$editions = array();
		foreach ( ( new TranslationManager() )->bundled() as $id => $edition ) {
			try {
				$file = LocalFiles::directory( 'bibles' ) . '/' . $id . '.json.php';
			} catch ( \RuntimeException $error ) {
				continue;
			}
			if ( is_file( $file ) && ! is_link( $file ) ) {
				$editions[ $id ] = $edition['label'];
			}
		}
		echo '<p>' . esc_html__( 'Selected passages use locally installed Bible editions. Annual sources may be configured before importing their yearly data.', 'daily-scripture' ) . '</p>';
		for ( $i = 1; $i <= 6; ++$i ) {
			$id  = 'passage_' . $i;
			$row = $saved[ $id ] ?? array(
				'translation' => '',
				'book'        => 'JOH',
				'chapter'     => 3,
				'from'        => 16,
				'to'          => 16,
				'title'       => '',
			);
			/* translators: %d: numbered passage slot. */
			echo '<details data-ds-reading-slot><summary>' . esc_html( sprintf( __( 'Bible passage %d', 'daily-scripture' ), $i ) ) . '</summary><p><label><input type="checkbox" name="daily_scripture_dashboard_readings[' . esc_attr( $id ) . '][enabled]" value="1" ' . checked( isset( $saved[ $id ] ), true, false ) . '> ' . esc_html__( 'Make this reading available', 'daily-scripture' ) . '</label></p>';
			$choices = $editions;
			if ( '' !== $row['translation'] && ! isset( $choices[ $row['translation'] ] ) ) {
				$choices[ $row['translation'] ] = TranslationManager::edition( $row['translation'] )['label'];
			}
			foreach ( array(
				'translation' => array( __( 'Translation', 'daily-scripture' ), array( '' => __( 'Choose an installed edition', 'daily-scripture' ) ) + $choices ),
				'book'        => array( __( 'Book', 'daily-scripture' ), TranslationManager::books() ),
			) as $key => $field ) {
				echo '<p><label>' . esc_html( $field[0] ) . '<select name="daily_scripture_dashboard_readings[' . esc_attr( $id ) . '][' . esc_attr( $key ) . ']">';
				foreach ( $field[1] as $value => $label ) {
					echo '<option value="' . esc_attr( $value ) . '" ' . selected( $row[ $key ], $value, false ) . '>' . esc_html( $label ) . '</option>';
				}
				echo '</select></label></p>';
			}
			foreach ( array(
				'chapter' => __( 'Chapter', 'daily-scripture' ),
				'from'    => __( 'First verse', 'daily-scripture' ),
				'to'      => __( 'Last verse', 'daily-scripture' ),
				'title'   => __( 'Custom heading', 'daily-scripture' ),
			) as $key => $label ) {
				echo '<p><label>' . esc_html( $label ) . '<input type="' . ( 'title' === $key ? 'text' : 'number' ) . '" name="daily_scripture_dashboard_readings[' . esc_attr( $id ) . '][' . esc_attr( $key ) . ']" value="' . esc_attr( (string) $row[ $key ] ) . '" ' . ( 'title' === $key ? 'maxlength="160"' : 'min="1" max="' . ( 'chapter' === $key ? '150' : '176' ) . '"' ) . '></label></p>';
			}
			echo '</details>';
		}
		echo '</div>';
	}
}
