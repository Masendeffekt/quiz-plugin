<?php
/**
 * Subscription module
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\Subscription;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WPQuiz\Helper;
use WPQuiz\Lockers\PayLocker;
use WPQuiz\Lockers\SubscribeLocker;
use WPQuiz\Module;
use WPQuiz\Modules\Subscription\Admin\LeadsPage;
use WPQuiz\Modules\Subscription\MailServices\Manager;
use WPQuiz\PostTypeQuiz;
use WPQuiz\Quiz;
use WPQuiz\REST\REST;

/**
 * Class Subscription
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Subscription extends Module {

	/**
	 * Module ID.
	 *
	 * @var string
	 */
	protected $id = 'stats';

	/**
	 * Initializes module.
	 */
	public function init() {
		// Add mail services to registry.
		$class_names = $this->get_mail_services_classes();
		foreach ( $class_names as $class_name ) {
			Manager::add( new $class_name() );
		}

		add_action( 'wp_quiz_before_trivia_quiz', [ $this, 'show_lockers' ], 20 );
		add_action( 'wp_quiz_before_personality_quiz', [ $this, 'show_lockers' ], 20 );

		// Register REST route.
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

		// Register subscribers backend management page.
		if ( is_admin() ) {
			$leads_page = new LeadsPage();
			$leads_page->init();
		}
	}

	/**
	 * Shows lockers.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	public function show_lockers( Quiz $quiz ) {
		if ( ! empty( $quiz->displayed_question ) ) {
			return;
		}

		if ( 'on' === $quiz->get_setting( 'pay_to_play' ) && floatval( $quiz->get_setting( 'pay_to_play_amount' ) ) ) {
			$locker = new PayLocker( $quiz );
			echo $locker->output(); // WPCS: xss ok.
		}

		if ( $quiz->is_required_subscribing_to_play() ) {
			$locker = new SubscribeLocker( $quiz );
			echo $locker->output();
		}
	}

	/**
	 * Registers REST routes.
	 */
	public function register_rest_routes() {
		register_rest_route(
			REST::REST_NAMESPACE,
			'subscribe',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_subscribe' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Subscribe REST handler.
	 *
	 * @since 2.1.7
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return mixed|WP_REST_Response
	 */
	public function handle_subscribe( WP_REST_Request $request ) {
		$name         = $request->get_param( 'username' );
		$email        = $request->get_param( 'email' );
		$quiz_id      = $request->get_param( 'quiz_id' );
		$consent      = $request->get_param( 'consent' );
		$play_data_id = $request->get_param( 'play_data_id' );
		$play_url     = $request->get_param( 'current_url' );

		if ( ! $quiz_id ) {
			return new WP_Error( 'empty-quiz-id', __( 'Quiz ID is empty', 'wp-quiz-pro' ) );
		}

		$quiz = PostTypeQuiz::get_quiz( $quiz_id );
		if ( ! $quiz ) {
			return new WP_Error( 'quiz-not-found', __( 'Quiz not found', 'wp-quiz-pro' ) );
		}

		if ( ! $name || ! $email ) {
			return new WP_Error( 'empty-name-or-email', __( 'Name and email must not be empty.', 'wp-quiz-pro' ) );
		}

		if ( ! is_email( $email ) ) {
			return new WP_Error( 'invalid-email', __( 'Invalid email address', 'wp-quiz-pro' ) );
		}

		if ( Helper::get_option( 'subscribe_box_user_consent' ) && ! $consent ) {
			return new WP_Error( 'empty-consent', __( 'You must agree with our rules', 'wp-quiz-pro' ) );
		}

		$mail_service = Helper::get_option( 'mail_service' );

		$response = [];

		/**
		 * Fires when subscribing an email.
		 *
		 * @since 2.0.0
		 *
		 * @param array $data Subscribe data.
		 * @param Quiz  $quiz Quiz object.
		 */
		do_action( 'wp_quiz_subscribe_email', $request->get_params(), $quiz );

		// Add subscriber to DB.
		$db = new Database();
		if ( ! $db->has( $email ) ) {
			$email_id = $db->add(
				[
					'quiz_id'      => intval( $quiz_id ),
					'play_data_id' => intval( $play_data_id ),
					'username'     => strip_tags( $name ),
					'email'        => sanitize_email( $email ),
					'consent'      => $consent ? 1 : null,
					'mail_service' => strip_tags( $mail_service ),
				]
			);

			if ( ! $email_id ) {
				return new WP_Error( 'add-email-to-db-failed', __( 'Adding email to the DB failed', 'wp-quiz-pro' ) );
			}

			$response['db'] = $email_id;
		} else {
			$response['db'] = 'exist';
		}

		// Add subscriber to mail service.
		if ( $mail_service && Manager::get( $mail_service ) ) {
			$mail_service = Manager::get( $mail_service );
			$result       = $mail_service->subscribe( $email, $name );

			if ( is_wp_error( $result ) ) {
				$response['mail_service'] = $result->get_error_message();
			} else {
				$response['mail_service'] = true;
			}
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Gets mail_services classes.
	 *
	 * @return array
	 */
	protected function get_mail_services_classes() {
		/**
		 * Allows adding new mail servicess.
		 *
		 * @since 2.0.0
		 *
		 * @param array $classes An array of MailService classes.
		 */
		return apply_filters(
			'wp_quiz_mail_service_classes',
			array(
				'\\WPQuiz\\Modules\\Subscription\\MailServices\\Mailchimp',
				'\\WPQuiz\\Modules\\Subscription\\MailServices\\GetResponse',
				'\\WPQuiz\\Modules\\Subscription\\MailServices\\AWeber',
				'\\WPQuiz\\Modules\\Subscription\\MailServices\\ConvertKit',
			)
		);
	}
}
