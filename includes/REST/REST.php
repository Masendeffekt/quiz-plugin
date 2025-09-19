<?php
/**
 * REST API
 *
 * @package WPQuiz
 */

namespace WPQuiz\REST;

use WP_REST_Response;
use WP_REST_Server;
use WP_REST_Request;
use WPQuiz\Helper;

/**
 * Class REST
 */
class REST {

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	const REST_NAMESPACE = 'wp-quiz/v2';

	/**
	 * REST constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers REST routes.
	 */
	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'video-content',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'video_content' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * REST gets video content.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return mixed|WP_REST_Response
	 */
	public function video_content( WP_REST_Request $request ) {
		$video_url  = $request->get_param( 'video_url' );
		$poster_url = $request->get_param( 'poster_url' );
		return rest_ensure_response( Helper::video_content( $video_url, $poster_url ) );
	}
}
