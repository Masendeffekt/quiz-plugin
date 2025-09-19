<?php
/**
 * Pay locker
 *
 * @package WPQuiz
 */

namespace WPQuiz\Lockers;

use WPQuiz\Quiz;
use WPQuiz\Template;

/**
 * Class PayLocker
 */
class PayLocker extends Locker {

	/**
	 * Gets locker output.
	 *
	 * @return string
	 */
	public function output() {
		$amount = $this->quiz->get_setting( 'pay_to_play_amount' );
		ob_start();
		Template::load_template(
			'lockers/pay-locker.php',
			array(
				'quiz'   => $this->quiz,
				'amount' => floatval( $amount ),
			)
		);
		$output = ob_get_clean();

		/**
		 * Allows changing pay locker output.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output Locker output.
		 * @param Quiz   $quiz   Quiz object.
		 */
		return apply_filters( 'wp_quiz_pay_locker_output', $output, $this->quiz );
	}

	/**
	 * Enqueues css and js.
	 */
	protected function enqueue() {
		wp_enqueue_script( 'wp-quiz-pay-locker', wp_quiz()->assets() . 'js/lockers/pay-locker.js', array( 'jquery', 'wp-quiz' ), '2.0.0', true );
	}
}
