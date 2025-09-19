<?php
/**
 * Database handler
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\PlayerTracking;

use WP_Error;
use wpdb;

/**
 * Class Database
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Database {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $table_name;

	/**
	 * WPDB object.
	 *
	 * @var wpdb;
	 */
	protected $wpdb;

	/**
	 * Database constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb       = $wpdb;
		$this->table_name = $wpdb->prefix . 'wp_quiz_players';
	}

	/**
	 * Checks if user ID exists.
	 *
	 * @param int $user_id User ID.
	 * @return int Player ID if exists. Otherwise, return 0.
	 */
	public function user_id_exists( $user_id ) {
		$ids = $this->wpdb->get_col(
			$this->wpdb->prepare(
				"SELECT id FROM {$this->table_name} WHERE user_id = %d ORDER BY id DESC LIMIT 1",
				intval( $user_id )
			)
		); // WPCS: unprepared SQL ok.

		if ( empty( $ids ) ) {
			return 0;
		}
		return intval( $ids[0] );
	}

	/**
	 * Checks if user IP exists.
	 *
	 * @param int $user_ip User IP.
	 * @return int Player ID if exists. Otherwise, return 0.
	 */
	public function user_ip_exists( $user_ip ) {
		$ids = $this->wpdb->get_col(
			$this->wpdb->prepare(
				"SELECT id FROM {$this->table_name} WHERE user_ip = %s ORDER BY id DESC LIMIT 1",
				$user_ip
			)
		); // WPCS: unprepared SQL ok.

		if ( empty( $ids ) ) {
			return 0;
		}
		return intval( $ids[0] );
	}

	/**
	 * Gets player.
	 *
	 * @param int $id Player ID.
	 * @return Player|false
	 */
	public function get( $id ) {
		$data = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table_name} WHERE id = %d",
				intval( $id )
			),
			ARRAY_A
		); // WPCS: unprepared SQL ok.

		if ( ! $data ) {
			return false;
		}

		return new Player( $data );
	}

	/**
	 * Gets players list table items.
	 *
	 * @param array $args Custom args.
	 * @return array
	 */
	public function get_list_table_items( array $args = array() ) {
		$args['column'] = '*, players.id AS player_id, play_data.id AS play_data_id';
		return $this->wpdb->get_results( $this->get_list_table_sql( $args ), ARRAY_A ); // WPCS: unprepared SQL ok.
	}

	/**
	 * Gets players list table items count.
	 *
	 * @param array $args Custom args.
	 * @return int
	 */
	public function get_list_table_count( array $args = array() ) {
		$args['column'] = 'players.id';
		$items          = $this->wpdb->get_results( $this->get_list_table_sql( $args ) ); // WPCS: unprepared SQL ok.
		return count( $items );
	}

	/**
	 * Gets SQL statement.
	 *
	 * @param array $args Custom args.
	 * @return string
	 */
	protected function get_list_table_sql( array $args = array() ) {
		$column = isset( $args['column'] ) ? $args['column'] : '*';

		$sql     = "SELECT {$column} FROM {$this->table_name} AS players INNER JOIN {$this->wpdb->prefix}wp_quiz_play_data AS play_data";
		$where   = 'WHERE players.id = play_data.player_id';
		$group   = 'GROUP BY players.id';
		$orderby = 'ORDER BY players.id DESC';
		$limit   = '';
		if ( ! empty( $args['s'] ) ) {
			$where .= " AND (
				players.email LIKE '%{$args['s']}%'
				OR players.first_name LIKE '%{$args['s']}%'
				OR players.last_name LIKE '%{$args['s']}%'
				OR (
					(SELECT posts.post_title FROM {$this->wpdb->posts} AS posts WHERE ID = play_data.quiz_id) LIKE '%{$args['s']}%'
				)
				OR (
					(SELECT users.display_name FROM {$this->wpdb->users} AS users WHERE ID = players.user_id) LIKE '%{$args['s']}%'
				)
			)";
		}

		if ( ! empty( $args['ids'] ) ) {
			$ids    = is_array( $args['ids'] ) ? implode( ',', $args['ids'] ) : $args['ids'];
			$where .= " AND players.id IN ({$ids})";
		}

		if ( ! empty( $args['orderby'] ) ) {
			$order   = isset( $args['order'] ) ? $args['order'] : 'ASC';
			$orderby = "ORDER BY {$args['orderby']} {$order}";
		}

		if ( ! empty( $args['per_page'] ) ) {
			$paged  = ! empty( $args['paged'] ) ? absint( $args['paged'] ) : 1;
			$offset = ( $paged - 1 ) * $args['per_page'];
			$limit  = "LIMIT {$args['per_page']} OFFSET {$offset}";
		}

		return "{$sql} {$where} {$group} {$orderby} {$limit}";
	}

	/**
	 * Adds play.
	 *
	 * @param array $data Play data.
	 * @return int|WP_Error
	 */
	public function add( array $data ) {
		$data = wp_parse_args(
			$data,
			array(
				'user_id'    => 0,
				'user_ip'    => '',
				'fb_user_id' => '',
				'email'      => '',
				'first_name' => '',
				'last_name'  => '',
				'gender'     => '',
				'picture'    => '',
				'friends'    => '',
				'type'       => '',
			)
		);

		$insert_data = array(
			'user_id'    => intval( $data['user_id'] ),
			'user_ip'    => $data['user_ip'],
			'created_at' => date( 'Y-m-d H:i:S' ),
			'updated_at' => date( 'Y-m-d H:i:S' ),
			'fb_user_id' => $data['fb_user_id'],
			'email'      => $data['email'],
			'first_name' => $data['first_name'],
			'last_name'  => $data['last_name'],
			'gender'     => $data['gender'],
			'picture'    => $data['picture'],
			'friends'    => is_array( $data['friends'] ) ? wp_json_encode( $data['friends'] ) : '',
			'type'       => $data['type'],
		);

		/**
		 * Allows changing sanitized inserting player data.
		 *
		 * @since 2.0.0
		 *
		 * @param array $insert_data Insert data.
		 * @param array $data        Unprocessed data.
		 */
		$insert_data = apply_filters( 'wp_quiz_sanitized_inserting_player_data', $insert_data, $data );

		/**
		 * Allows changing player insert format.
		 *
		 * @since 2.0.0
		 *
		 * @param array $insert_format Insert format.
		 * @param array $insert_data   Insert data.
		 */
		$insert_format = apply_filters( 'wp_quiz_player_insert_format', array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ), $insert_data );

		$result = $this->wpdb->insert(
			$this->table_name,
			$insert_data,
			$insert_format
		);

		if ( ! $result ) {
			return new WP_Error( 'insert-player-failed', __( 'Unable to insert player', 'wp-quiz-pro' ) );
		}

		$player_id = $this->wpdb->insert_id;

		/**
		 * Fires after inserting player.
		 *
		 * @since 2.0.0
		 *
		 * @param int   $play_data_id Play ID.
		 * @param array $insert_data  Insert data.
		 */
		do_action( 'wp_quiz_inserted_player', $player_id, $insert_data );

		return $player_id;
	}

	/**
	 * Updates player.
	 *
	 * @param int   $player_id Player ID.
	 * @param array $data      Update data.
	 */
	public function update( $player_id, array $data ) {
		if ( isset( $data['id'] ) ) {
			unset( $data['id'] );
		}

		// TODO: improve this method.
		$this->wpdb->update(
			$this->table_name,
			$data,
			array( 'id' => $player_id )
		);
	}

	/**
	 * Delete player by ID.
	 *
	 * @param int $id Player ID.
	 */
	public function delete( $id ) {
		$this->wpdb->delete(
			$this->table_name,
			array( 'id' => $id ),
			array( '%d' )
		);

		/**
		 * Fires after deleting a player.
		 *
		 * @since 2.0.0
		 *
		 * @param int $id Player ID.
		 */
		do_action( 'wp_quiz_deleted_player', $id );
	}
}
