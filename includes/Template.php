<?php
/**
 * Template helper
 *
 * @package WPQuiz
 */

namespace WPQuiz;

/**
 * Class Template
 */
class Template {

	/**
	 * Gets template path.
	 *
	 * @param string $file_path Template file path.
	 * @return string
	 */
	public static function get_template_path( $file_path ) {
		$path       = '';
		$theme_path = locate_template( self::get_theme_template_dir() . $file_path );
		if ( $theme_path ) {
			$path = $theme_path;
		} elseif ( file_exists( wp_quiz()->templates_dir() . $file_path ) ) {
			$path = wp_quiz()->templates_dir() . $file_path;
		}

		return apply_filters( 'wp_quiz_template_path', $path, $file_path );
	}

	/**
	 * Gets theme template directory.
	 *
	 * @return string
	 */
	protected static function get_theme_template_dir() {
		return apply_filters( 'wp_quiz_theme_template_dir', 'wp-quiz/' );
	}

	/**
	 * Loads template.
	 *
	 * @param string $file_path Template file path.
	 * @param array  $data      Data passed to template file.
	 */
	public static function load_template( $file_path, $data = array() ) {
		$path = self::get_template_path( $file_path );
		if ( ! $path ) {
			return;
		}

		extract( $data ); // phpcs:ignore

		do_action( 'wp_quiz_before_load_template', $file_path, $data );

		include $path;
	}

	/**
	 * Shows notice.
	 *
	 * @param string $message Message.
	 * @param string $type    Notice type. Default is `error`.
	 * @param bool   $alt     Show alt color.
	 * @param bool   $echo    Show notice or return the output.
	 * @return string|void
	 */
	public static function notice( $message, $type = 'error', $alt = false, $echo = false ) {
		if ( ! $message ) {
			return '';
		}

		$classes   = array( 'notice' );
		$classes[] = 'notice-' . $type;
		if ( $alt ) {
			$classes[] = 'notice-alt';
		}
		$output = sprintf(
			'<div class="%1$s"><p>%2$s</p></div>',
			esc_attr( implode( ' ', $classes ) ),
			wp_kses_post( $message )
		);

		if ( ! $echo ) {
			return $output;
		}
		echo $output; // WPCS: xss ok.
	}
}
