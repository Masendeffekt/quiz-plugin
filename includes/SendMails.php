<?php
/**
 * Send mails
 *
 * @package WPQuiz
 */

namespace WPQuiz;

use WPQuiz\Emails\AdminResultEmail;
use WPQuiz\Emails\QuizResultEmail;
use WPQuiz\PlayDataTracking\PlayData;

/**
 * Class SendMails
 */
class SendMails {

	/**
	 * Initializes.
	 */
	public function init() {
		add_action( 'wp_quiz_subscribe_email', array( $this, 'send_quiz_result_email' ), 10, 2 );
		add_action( 'wp_quiz_inserted_play_data', array( $this, 'send_admin_result_email' ), 10, 2 );
	}

	/**
	 * Sends quiz result email.
	 *
	 * @param array $subscribe_data Subscribe data.
	 * @param Quiz  $quiz           Quiz object.
	 */
	public function send_quiz_result_email( array $subscribe_data, Quiz $quiz ) {
		$is_enabled = ! $quiz->get_setting( 'result_method' ) ?
			in_array( Helper::get_option( 'result_method' ), array( 'show_send', 'send' ), true ) :
			in_array( $quiz->get_setting( 'result_method' ), array( 'show_send', 'send' ), true );

		if ( ! $is_enabled ) {
			return;
		}

		$play_data = PlayData::get( $subscribe_data['play_data_id'] );
		if ( ! $play_data ) {
			return;
		}

		$quiz_type   = $quiz->get_quiz_type();
		$quiz_result = $quiz_type->quiz_result_email( $quiz, $play_data );
		if ( ! $quiz_result ) {
			return;
		}

		$replaces = array(
			'%%subscriber_name%%'  => ! empty( $subscribe_data['username'] ) ? $subscribe_data['username'] : '',
			'%%subscriber_email%%' => $subscribe_data['email'],
			'%%quiz_url%%'         => $play_data->quiz_url,
			'%%quiz_name%%'        => $quiz->get_title(),
			'%%quiz_result%%'      => $quiz_result,
		);

		$email = new QuizResultEmail();
		$email->replaces( $replaces );
		$email->send( $subscribe_data['email'] );
	}

	/**
	 * Sends admin result email.
	 *
	 * @param int   $play_data_id Play ID.
	 * @param array $insert_data  Player insert data.
	 */
	public function send_admin_result_email( $play_data_id, array $insert_data ) {
		$is_enabled = 'on' === Helper::get_option( 'email_result_to_admin' );

		if ( ! $is_enabled ) {
			return;
		}

		$play_data   = PlayData::get( $play_data_id );
		$admin_email = get_option( 'admin_email' );
		$quiz        = $play_data->get_quiz();
		$quiz_type   = $quiz->get_quiz_type();
		$quiz_result = $quiz_type->quiz_result_email( $quiz, $play_data );
		if ( ! $quiz_result ) {
			return;
		}

		$replaces = array(
			'%%quiz_url%%'    => $play_data->quiz_url,
			'%%quiz_name%%'   => $quiz->get_title(),
			'%%quiz_result%%' => $quiz_result,
		);

		$email = new AdminResultEmail();
		$email->replaces( $replaces );
		$email->send( $admin_email );
	}
}
