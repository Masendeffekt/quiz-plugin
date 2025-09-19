<?php
/**
 * Import background process
 *
 * @package WPQuiz
 */

namespace WPQuiz\Processes;

use WP_Background_Process;
use WPQuiz\Importer;

/**
 * Class ImportProcess
 */
class ImportProcess extends WP_Background_Process {

	/**
	 * Action.
	 *
	 * @var string
	 * @access protected
	 */
	protected $action = 'wp_quiz_import_quizzes';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		$importer = new Importer();
		$importer->set_download_images( $item['download_images'] );
		$importer->set_author( $item['author'] );
		$importer->import_quiz( $item['quiz'], $item['force_new'] );
		$remain = $this->get_remain();
		$this->set_remain( $remain - 1 );

		return false;
	}

	/**
	 * Completes process.
	 */
	protected function complete() {
		parent::complete();
		error_log( 'Import done!' );
		delete_option( "wp_quiz_{$this->action}_remain" );
	}

	/**
	 * Sets remain value.
	 *
	 * @param int $remain Remain.
	 */
	public function set_remain( $remain ) {
		update_option( "wp_quiz_{$this->action}_remain", intval( $remain ) );
	}

	/**
	 * Gets remain value.
	 *
	 * @return int
	 */
	public function get_remain() {
		return intval( get_option( "wp_quiz_{$this->action}_remain" ) );
	}
}
