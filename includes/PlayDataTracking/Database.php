<?php
/**
 * Database handler
 *
 * @package WPQuiz
 */

namespace WPQuiz\PlayDataTracking;

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
		$this->table_name = $wpdb->prefix . 'wp_quiz_play_data';
	}

	/**
	 * Gets play data.
	 *
	 * @param int $id Play data ID.
	 * @return PlayData|false
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

		return new PlayData( $data );
	}

	/**
	 * Gets all play data.
	 *
	 * @param array $args Custom args.
	 * @return array
	 */
	public function get_all( array $args = array() ) {
		$sql  = $this->get_sql( $args );
		$data = $this->wpdb->get_results( $sql, ARRAY_A ); // WPCS: unprepared SQL ok.

		if ( ! $data ) {
			return array();
		}

		$result = array();
		foreach ( $data as $value ) {
			$result[] = new PlayData( $value );
		}
		return $result;
	}

	/**
	 * Gets count.
	 *
	 * @param array $args Custom args.
	 * @return int
	 */
	public function get_count( array $args = array() ) {
		$args['column'] = 'COUNT(*)';
		$sql            = $this->get_sql( $args );
		$count          = $this->wpdb->get_var( $sql ); // WPCS: unprepared SQL ok.
		return intval( $count );
	}

	/**
	 * Gets SQL statement.
	 *
	 * @param array $args Custom args.
	 * @return string
	 */
	protected function get_sql( array $args = array() ) {
		$column = isset( $args['column'] ) ? $args['column'] : '*';
		$sql    = "SELECT {$column} FROM {$this->table_name} WHERE 1 = 1";

		if ( ! empty( $args['player_id'] ) ) {
			$player_id = intval( $args['player_id'] );
			$sql      .= " AND player_id = {$player_id}";
		}

		if ( ! empty( $args['quiz_id'] ) ) {
			$quiz_id = intval( $args['quiz_id'] );
			$sql    .= " AND quiz_id = {$quiz_id}";
		}

		if ( ! empty( $args['orderby'] ) ) {
			$order = isset( $args['order'] ) ? $args['order'] : 'ASC';
			$sql  .= " ORDER BY {$args['orderby']} {$order}";
		} else {
			$sql .= ' ORDER BY id DESC';
		}

		if ( ! empty( $args['per_page'] ) ) {
			$paged  = ! empty( $args['paged'] ) ? absint( $args['paged'] ) : 1;
			$offset = ( $paged - 1 ) * $args['per_page'];
			$sql   .= " LIMIT {$args['per_page']} OFFSET {$offset}";
		}

		return $sql;
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
				'quiz_id'          => 0,
				'player_id'        => 0,
				'correct_answered' => null,
				'result'           => '',
				'quiz_type'        => '',
				'quiz_data'        => '',
				'answered_data'    => '',
				'quiz_url'         => '',
			)
		);

		if ( ! intval( $data['quiz_id'] ) ) {
			return new WP_Error( 'empty-quiz-id', __( 'Empty quiz ID', 'wp-quiz-pro' ) );
		}

		if ( ! isset( $data['quiz_data'] ) ) {
			$quiz_data = get_post_field( 'post_content', $data['quiz_id'] );
		} elseif ( ! is_string( $data['quiz_data'] ) ) {
			$quiz_data = wp_json_encode( $data['quiz_data'] );
		} else {
			$quiz_data = $data['quiz_data'];
		}

		if ( ! isset( $data['answered_data'] ) ) {
			$answered_data = '';
		} elseif ( ! is_string( $data['answered_data'] ) ) {
			$answered_data = wp_json_encode( $data['answered_data'] );
		} else {
			$answered_data = $data['answered_data'];
		}

		$insert_data = array(
			'quiz_id'          => intval( $data['quiz_id'] ),
			'player_id'        => intval( $data['player_id'] ),
			'played_at'        => date( 'Y-m-d H:i:s' ),
			'correct_answered' => $data['correct_answered'],
			'result'           => $data['result'],
			'quiz_type'        => $data['quiz_type'],
			'quiz_data'        => $quiz_data,
			'answered_data'    => $answered_data,
			'quiz_url'         => $data['quiz_url'],
		);

		/**
		 * Allows changing sanitized inserting play data.
		 *
		 * @since 2.0.0
		 *
		 * @param array $insert_data Insert data.
		 * @param array $data        Unprocessed data.
		 */
		$insert_data = apply_filters( 'wp_quiz_sanitized_inserting_play_data', $insert_data, $data );

		/**
		 * Allows changing play insert format.
		 *
		 * @since 2.0.0
		 *
		 * @param array $insert_format Insert format.
		 * @param array $insert_data   Insert data.
		 */
		$insert_format = apply_filters( 'wp_quiz_play_data_insert_format', array( '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s' ), $insert_data );

		$result = $this->wpdb->insert(
			$this->table_name,
			$insert_data,
			$insert_format
		);

		if ( ! $result ) {
			return new WP_Error( 'insert-play-data-failed', __( 'Unable to insert play data', 'wp-quiz-pro' ) );
		}

		$play_data_id = $this->wpdb->insert_id;

		/**
		 * Fires after inserting play data.
		 *
		 * @since 2.0.0
		 *
		 * @param int   $play_data_id Play ID.
		 * @param array $insert_data  Insert data.
		 */
		do_action( 'wp_quiz_inserted_play_data', $play_data_id, $insert_data );

		return $play_data_id;
	}

	/**
	 * Delete play data item by ID.
	 *
	 * @param int $id Play data item ID.
	 */
	public function delete( $id ) {
		$this->wpdb->delete(
			$this->table_name,
			array( 'id' => $id ),
			array( '%d' )
		);

		/**
		 * Fires after deleting a play data item.
		 *
		 * @since 2.0.0
		 *
		 * @param int $id Play data item ID.
		 */
		do_action( 'wp_quiz_deleted_play_data', $id );
	}
}
