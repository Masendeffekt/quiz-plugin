<?php
/**
 * FB Share force action
 *
 * @package WPQuiz
 */

namespace WPQuiz\ForceActions;

use WPQuiz\Quiz;
use WPQuiz\Template;

/**
 * Class FBShare
 */
class FBShare extends ForceAction {

	/**
	 * Subscribe constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->id    = '2';
		$this->title = __( 'Facebook Share', 'wp-quiz-pro' );
	}

	/**
	 * Gets force action output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function output( Quiz $quiz ) {
		ob_start();
		Template::load_template( 'force-actions/fb-share.php', compact( 'quiz' ) );
		return ob_get_clean();
	}

	/**
	 * Enqueues css and js.
	 */
	public function enqueue() {
		wp_enqueue_script( 'wp-quiz-force-action-fb-share', wp_quiz()->assets() . 'js/force-actions/fb-share.js', array( 'jquery', 'wp-quiz' ), '2.0.0', true );
	}
}
