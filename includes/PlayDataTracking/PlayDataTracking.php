<?php
/**
 * Play data tracking feature
 *
 * @package WPQuiz
 */

namespace WPQuiz\PlayDataTracking;

use WP_Error;
use WPQuiz\Quiz;

/**
 * Class PlayDataTracking
 */
class PlayDataTracking {

	/**
	 * Initializes.
	 */
	public function init() {
		$rest = new REST();
		$rest->init();
	}

	/**
	 * Adds play data.
	 *
	 * @param Quiz  $quiz      Quiz object.
	 * @param array $play_data Play data.
	 *
	 * @return int|WP_Error Return play_data_id on success or WP_Error on failure.
	 */
	public static function add_play_data( Quiz $quiz, array $play_data ) {
		$insert_data = $quiz->get_quiz_type()->get_inserting_play_data( $quiz, $play_data );

		if ( is_wp_error( $insert_data ) ) {
			return $insert_data;
		}

		/**
		 * Allows changing inserting play data of a specific quiz type.
		 *
		 * @since 2.0.0
		 *
		 * @param array $insert_data Play insert data.
		 * @param array $play_data   Unprocessed player data from REST request.
		 * @param Quiz  $quiz        Quiz object.
		 */
		$insert_data = apply_filters( "wp_quiz_{$quiz->get_quiz_type()->get_name()}_inserting_play_data", $insert_data, $play_data, $quiz );

		/**
		 * Allows changing inserting play data.
		 *
		 * @since 2.0.0
		 *
		 * @param array $insert_data Play insert data.
		 * @param array $play_data   Unprocessed player data from REST request.
		 * @param Quiz  $quiz        Quiz object.
		 */
		$insert_data = apply_filters( 'wp_quiz_inserting_play_data', $insert_data, $play_data, $quiz );

		if ( ! $insert_data ) {
			return new WP_Error( 'empty-play-data', __( 'Empty play data', 'wp-quiz-pro' ) );
		}

		$database = new Database();
		return $database->add( $insert_data );
	}
}
