<?php
/**
 * Read-only release history from the documentation shipped with this installation.
 *
 * @package DailyScripture
 */

namespace Deckerweb\DailyScripture\Admin;

defined( 'ABSPATH' ) || exit;

/** Render trusted local filenames as escaped, structured changelog content. */
final class Changelog {
	/**
	 * Resolve the documentation language from the current WordPress admin locale.
	 *
	 * @return bool Whether to use the German documentation.
	 */
	public static function is_german(): bool {
		return 1 === preg_match( '/^de(?:_|$)/i', determine_locale() );
	}

	/**
	 * Choose a fixed local file for English, informal German or formal German.
	 *
	 * @return string Relative changelog filename.
	 */
	private static function filename(): string {
		if ( 'de_DE_formal' === determine_locale() ) {
			return 'docs/changelog-de-formal.txt';
		}
		return self::is_german() ? 'docs/changelog-de.txt' : 'docs/changelog.txt';
	}

	/**
	 * Local documentation URL, also used when dialogs or JavaScript are unavailable.
	 *
	 * @return string Changelog URL.
	 */
	public static function url(): string {
		return DAILY_SCRIPTURE_URL . self::filename();
	}

	/**
	 * Extract only the changelog section; never interpret readme text as HTML.
	 *
	 * @return string Escaped headings and lists, or an empty string if unavailable.
	 */
	public static function content(): string {
		$file = DAILY_SCRIPTURE_DIR . self::filename();
		if ( ! is_readable( $file ) || filesize( $file ) > 262144 ) {
			return '';
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Read one of two fixed local documentation files; no user-supplied paths or remote requests.
		$text = file_get_contents( $file );
		if ( false === $text || ! preg_match( '/^== Changelog ==\s*\R(.*?)(?=^== [^\r\n]+ ==\s*$|\z)/ms', $text, $section ) ) {
			return '';
		}
		$html = '';
		$list = false;
		foreach ( preg_split( '/\R/', trim( $section[1] ) ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			if ( preg_match( '/^= (.+) =$/', $line, $heading ) ) {
				$html .= ( $list ? '</ul>' : '' ) . '<h3>' . esc_html( $heading[1] ) . '</h3>';
				$list  = false;
			} elseif ( str_starts_with( $line, '* ' ) ) {
				$item       = substr( $line, 2 );
				$categories = array(
					'New'        => 'new',
					'Neu'        => 'new',
					'Improved'   => 'improved',
					'Verbessert' => 'improved',
					'Fixed'      => 'fixed',
					'Behoben'    => 'fixed',
					'Misc'       => 'misc',
					'Sonstiges'  => 'misc',
				);
				$parts      = explode( ': ', $item, 2 );
				$label      = isset( $categories[ $parts[0] ] ) && isset( $parts[1] ) ? '<span class="ds-changelog__category ds-changelog__category--' . esc_attr( $categories[ $parts[0] ] ) . '">' . esc_html( $parts[0] ) . '</span> ' . esc_html( $parts[1] ) : esc_html( $item );
				$html      .= ( $list ? '' : '<ul>' ) . '<li>' . $label . '</li>';
				$list       = true;
			} else {
				$html .= ( $list ? '</ul>' : '' ) . '<p>' . esc_html( $line ) . '</p>';
				$list  = false;
			}
		}
		return $html . ( $list ? '</ul>' : '' );
	}

	/**
	 * Dialog markup; the footer link remains usable if content cannot be read.
	 *
	 * @return void
	 */
	public static function dialog(): void {
		$content = self::content();
		if ( '' === $content ) {
			return;
		}
		$close = __( 'Close', 'daily-scripture' );
		echo '<dialog id="ds-changelog" class="ds-changelog" aria-labelledby="ds-changelog-title"><header class="ds-changelog__header"><h2 id="ds-changelog-title">Daily Scripture · ' . esc_html__( 'Changelog', 'daily-scripture' ) . '</h2><button type="button" class="button" data-ds-changelog-close autofocus>' . esc_html( $close ) . '</button></header><div class="ds-changelog__content" tabindex="0">';
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- content() escapes every text node; only fixed heading, list and paragraph markup is emitted.
		echo '</div><p class="ds-changelog__readme"><a href="' . esc_url( self::url() ) . '">' . esc_html( self::filename() ) . '</a></p></dialog>';
	}
}
