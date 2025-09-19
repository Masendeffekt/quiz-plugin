<?php
/**
 * User actions
 *
 * @package WPQuiz
 */

namespace WPQuiz;

/**
 * Class UserAction
 */
class UserActions {

	/**
	 * DB Table.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * WPDB object.
	 *
	 * @var \wpdb
	 */
	protected $wpdb;

	/**
	 * Class UserActions constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->prefix . 'wp_quiz_user_actions';
	}

	/**
	 * Adds an action.
	 *
	 * @param string $action  Action name.
	 * @param int    $user_id User ID.
	 * @param string $user_ip User IP.
	 * @return int|false Action ID or `false` on failure.
	 */
	public function add( $action, $user_id = 0, $user_ip = '' ) {
		$result = $this->wpdb->insert(
			$this->table,
			array(
				'action'  => $action,
				'user_id' => $user_id ? intval( $user_id ) : 0,
				'user_ip' => $user_ip ? $user_ip : '',
				'time'    => date( 'Y-m-d H:i:s' ),
			)
		);
		if ( ! $result ) {
			return false;
		}
		return $this->wpdb->insert_id;
	}

	/**
	 * Checks if an action exists.
	 *
	 * @param string $action  Action name.
	 * @param int    $user_id User ID.
	 * @param string $user_ip User IP.
	 * @return bool
	 */
	public function has( $action, $user_id = 0, $user_ip = '' ) {
		$sql = $this->wpdb->prepare(
			"SELECT COUNT(*) FROM `{$this->table}` WHERE `action` = %s AND `user_id` = %d AND `user_ip` = %s",
			$action,
			$user_id ? intval( $user_id ) : 0,
			$user_ip ? $user_ip : ''
		); // WPCS: unprepared SQL ok.
		return intval( $this->wpdb->get_var( $sql ) ) > 0; // WPCS: unprepared SQL ok.
	}
}
