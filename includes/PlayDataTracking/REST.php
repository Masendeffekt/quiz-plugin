<?php
/**
 * REST for Play Data Tracking
 *
 * @package WPQuiz
 */

namespace WPQuiz\PlayDataTracking;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WPQuiz\PostTypeQuiz;
use WPQuiz\WPQuiz;

/**
 * Class REST
 */
class REST {

	/**
	 * Initializes.
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers REST routes.
	 */
	public function register_routes() {
		register_rest_route(
			WPQuiz::REST_NAMESPACE,
			'play_data',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'add_play_data' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Adds play data.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return bool|mixed|WP_REST_Response
	 */
	public function add_play_data( WP_REST_Request $request ) {
		$quiz_id = $request->get_param( 'quiz_id' );
		$params  = $request->get_params();

		if ( ! empty( $params['quiz_data'] ) && ! is_array( $params['quiz_data'] ) ) {
			$params['quiz_data'] = json_decode( $params['quiz_data'] );
		}

		if ( ! empty( $params['answered'] ) && ! is_array( $params['answered'] ) && 0 !== strpos( $params['answered'], 'data:image/' ) ) {
			$params['answered'] = json_decode( $params['answered'] );
		}

		$quiz    = PostTypeQuiz::get_quiz( $quiz_id );
		if ( ! $quiz ) {
			return new WP_Error( 'quiz-not-found', __( 'Quiz not found', 'wp-quiz-pro' ) );
		}

		return PlayDataTracking::add_play_data( $quiz, $params );
	}
}
