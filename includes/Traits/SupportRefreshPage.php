<?php
/**
 * SupportRefreshPage trait
 *
 * @package WPQuiz\Traits
 */

namespace WPQuiz\Traits;

use WPQuiz\Helper;

/**
 * Trait SupportRefreshPage
 */
trait SupportRefreshPage {

	/**
	 * Checks if is continued quiz.
	 *
	 * @return bool
	 */
	protected function is_continued() {
		return true;
	}

	/**
	 * Gets default refresh step.
	 *
	 * @return int
	 */
	protected function get_default_refresh_step() {
		return intval( Helper::get_option( 'refresh_step' ) );
	}
}
