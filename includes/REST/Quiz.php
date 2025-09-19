<?php
/**
 * Quiz REST routes.
 *
 * @package WPQuiz
 */

namespace WPQuiz\REST;

use WP_REST_Response;
use WP_REST_Server;
use WP_REST_Request;
use WPQuiz\PostTypeQuiz;

/**
 * Class Quiz
 */
class Quiz extends REST {

	/**
	 * REST base.
	 *
	 * @var string
	 */
	protected $rest_base = 'quizzes/(?P<id>[\d]+)/';

	/**
	 * Gets ID args.
	 *
	 * @return array
	 */
	protected function get_id_args() {
		return array(
			'description' => __( 'Unique identifier for the quiz.', 'wp-quiz-pro' ),
			'type'        => 'integer',
		);
	}

	/**
	 * Registers REST routes.
	 */
	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			$this->rest_base . 'vote-up',
			array(
				'args' => array(
					'id' => $this->get_id_args(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'vote_question_up' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			$this->rest_base . 'vote-down',
			array(
				'args' => array(
					'id' => $this->get_id_args(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'vote_question_down' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * REST votes question up.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return bool|mixed|WP_REST_Response
	 */
	public function vote_question_up( WP_REST_Request $request ) {
		$quiz_id     = $request->get_param( 'id' );
		$question_id = $request->get_param( 'question_id' );
		$quiz        = PostTypeQuiz::get_quiz( $quiz_id );
		$votes       = $quiz->vote_question_up( $question_id );
		if ( ! $votes ) {
			return false;
		}
		return rest_ensure_response(
			array(
				'number' => $votes,
				'text'   => $votes > 1 ? __( 'Votes', 'wp-quiz-pro' ) : __( 'Vote', 'wp-quiz-pro' ),
			)
		);
	}

	/**
	 * REST votes question down.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return bool|mixed|WP_REST_Response
	 */
	public function vote_question_down( WP_REST_Request $request ) {
		$quiz_id     = $request->get_param( 'id' );
		$question_id = $request->get_param( 'question_id' );
		$quiz        = PostTypeQuiz::get_quiz( $quiz_id );
		$votes       = $quiz->vote_question_down( $question_id );
		if ( ! $votes ) {
			return false;
		}
		return rest_ensure_response(
			array(
				'number' => $votes,
				'text'   => $votes > 1 ? __( 'Votes', 'wp-quiz-pro' ) : __( 'Vote', 'wp-quiz-pro' ),
			)
		);
	}
}
