<?php
/**
 * Stats module
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\Stats;

use WPQuiz\Module;
use WPQuiz\Modules\Stats\Admin\Admin;

/**
 * Class Stats
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Stats extends Module {

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
		if ( is_admin() ) {
			$admin = new Admin();
			$admin->init();
		}
	}
}
