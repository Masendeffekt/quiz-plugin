<?php
/**
 * Exporter
 *
 * @package WPQuiz
 */

namespace WPQuiz;

use WP_Query;
use WPQuiz\Repositories\PlayersTracking;
use WPQuiz\Repositories\Database;

/**
 * Class Exporter
 */
class Exporter {

	/**
	 * Exports quiz.
	 *
	 * @param int $quiz_id Quiz ID.
	 * @return string|false Export string on success, `false` on failure.
	 */
	public function export_quiz( $quiz_id ) {
		$quiz = PostTypeQuiz::get_quiz( $quiz_id );
		if ( ! $quiz ) {
			return false;
		}
		return $quiz->to_json();
	}

	/**
	 * Exports quizzes.
	 *
	 * @param WP_Query $query Optional. WP_Query object.
	 * @return string
	 */
	public function export_quizzes( WP_Query $query = null ) {
		if ( ! $query ) {
			$query = new WP_Query(
				array(
					'post_type'   => PostTypeQuiz::get_name(),
					'post_status' => 'any',
					'nopaging'    => true, // phpcs:ignore
				)
			);
		}
		if ( ! $query->have_posts() ) {
			return '';
		}

		$result = array();
		foreach ( $query->posts as $post ) {
			$quiz = PostTypeQuiz::get_quiz( $post );
			if ( ! $quiz ) {
				continue;
			}
			$result[] = $quiz->to_array();
		}

		return wp_json_encode( $result );
	}

	/**
	 * Exports settings.
	 *
	 * @return string
	 */
	public function export_settings() {
		$settings = get_option( 'wp_quiz_pro_default_settings' );
		if ( ! $settings ) {
			return '';
		}
		return wp_json_encode( $settings );
	}
}
