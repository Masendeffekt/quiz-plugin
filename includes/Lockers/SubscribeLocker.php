<?php
/**
 * Subscribe locker
 *
 * @package WPQuizPro
 * @since 2.1.7
 */

namespace WPQuiz\Lockers;

use WPQuiz\Quiz;
use WPQuiz\Template;

/**
 * Class SubscribeLocker
 */
class SubscribeLocker extends Locker {

	public function output() {
		ob_start();
		Template::load_template(
			'lockers/subscribe-locker.php',
			array(
				'quiz' => $this->quiz,
			)
		);
		$output = ob_get_clean();

		$this->enqueue();

		/**
		 * Allows changing pay locker output.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output Locker output.
		 * @param Quiz   $quiz   Quiz object.
		 */
		return apply_filters( 'wp_quiz_subscribe_locker_output', $output, $this->quiz );
	}

	/**
	 * Enqueues css and js.
	 */
	protected function enqueue() {
		wp_enqueue_script( 'wp-quiz-subscribe-locker', wp_quiz()->assets() . 'js/lockers/subscribe-locker.js', array( 'jquery', 'wp-quiz' ), wp_quiz()->version, true );
	}
}
