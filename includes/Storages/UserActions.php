<?php
/**
 * User actions storage
 *
 * @package WPQuiz
 */

namespace WPQuiz\Storages;

/**
 * Class UserActions
 */
class UserActions implements Storage {

	/**
	 * User ID.
	 *
	 * @var int
	 */
	protected $user_id;

	/**
	 * User IP.
	 *
	 * @var string
	 */
	protected $user_ip;

	/**
	 * WPDB object.
	 *
	 * @var \wpdb
	 */
	protected $wpdb;

	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * UserActions constructor.
	 *
	 * @param int    $user_id User ID.
	 * @param string $user_ip User IP.
	 */
	public function __construct( $user_id = 0, $user_ip = '' ) {
		global $wpdb;
		$this->wpdb    = $wpdb;
		$this->table   = $wpdb->prefix . 'wp_quiz_user_actions';
		$this->user_id = $user_id;
		$this->user_ip = $user_ip;
	}

	/**
	 * Gets where clause.
	 *
	 * @param array $where Where data.
	 * @return string
	 */
	protected function get_where( array $where ) {
		$where_clause = '';
		if ( isset( $where['action'] ) ) {
			$where_clause .= sprintf( '`action` = %s', sanitize_text_field( $where['action'] ) );
		}
		if ( $this->user_id ) {
			$where_clause .= sprintf( ' AND `user_id` = %s', intval( $this->user_id ) );
		}
		if ( $this->user_ip ) {
			$where_clause .= sprintf( ' AND `user_ip` = %s', sanitize_text_field( $this->user_ip ) );
		}
		if ( $where_clause ) {
			$where_clause = '(' . $where_clause . ')';
		}
		return $where_clause;
	}

	/**
	 * Checks if has value with given key.
	 *
	 * @param string $key Storage key.
	 *
	 * @return bool
	 */
	public function has( $key ) {
		$sql = "SELECT COUNT(*) FROM {$this->table} WHERE " . $this->get_where( array( 'action' => $key ) );
		return $this->wpdb->get_var( $sql ); // WPCS: unprepared SQL ok.
	}

	/**
	 * Gets value with given key.
	 *
	 * @param string $key Storage key.
	 *
	 * @return mixed
	 */
	public function get( $key ) {
		$sql = "SELECT * FROM {$this->table} WHERE " . $this->get_where( array( 'action' => $key ) );
		return $this->wpdb->get_results( $sql ); // WPCS: unprepared SQL ok.
	}

	/**
	 * Updates storage value.
	 *
	 * @param string $key   Storage key.
	 * @param mixed  $value Storage value.
	 */
	public function update( $key, $value ) {
		$where = array(
			'action' => $key,
		);
		if ( $this->user_id ) {
			$where['user_id'] = $this->user_id;
		}
		if ( $this->user_ip ) {
			$where['user_ip'] = $this->user_ip;
		}
		$this->wpdb->update(
			$this->table,
			array( 'value' => $value ),
			$where
		);
	}

	/**
	 * Deletes storage value.
	 *
	 * @param string $key Storage key.
	 */
	public function delete( $key ) {
		// TODO: Implement delete() method.
	}
}
