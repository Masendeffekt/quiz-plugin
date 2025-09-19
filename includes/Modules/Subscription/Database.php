<?php
/**
 * Email database handler
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\Subscription;

use wpdb;

/**
 * Class Emails
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
		$this->table_name = $wpdb->prefix . 'wp_quiz_emails';
	}

	/**
	 * Checks if given email exists.
	 *
	 * @param string $email Email address.
	 * @return bool
	 */
	public function has( $email ) {
		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table_name} WHERE email = %s",
				sanitize_text_field( $email )
			)
		); // WPCS: unprepared SQL ok.
		return intval( $result ) > 0;
	}

	/**
	 * Adds item.
	 *
	 * @param array $data Item data.
	 * @return int|false Return new item ID or `false`.
	 */
	public function add( array $data ) {
		$insert_data = array(
			'pid'          => $data['quiz_id'],
			'play_data_id' => ! empty( $data['play_data_id'] ) ? intval( $data['play_data_id'] ) : 0,
			'username'     => $data['username'],
			'email'        => $data['email'],
			'time'         => date( 'Y-m-d H:i:s' ),
			'consent'      => ! empty( $data['consent'] ) ? 1 : null,
			'mail_service' => ! empty( $data['mail_service'] ) ? $data['mail_service'] : '',
		);

		/**
		 * Allows changing email insert data.
		 *
		 * @since 2.0.0
		 *
		 * @param array $insert_data Insert data.
		 * @param array $data        Unprocessed data.
		 */
		$insert_data = apply_filters( 'wp_quiz_email_insert_data', $insert_data, $data );

		/**
		 * Allows changing email insert format.
		 *
		 * @since 2.0.0
		 *
		 * @param array $insert_format Insert format.
		 * @param array $insert_data   Insert data.
		 */
		$insert_format = apply_filters( 'wp_quiz_email_insert_format', array( '%d', '%d', '%s', '%s', '%s', '%d', '%s' ), $insert_data );

		$result = $this->wpdb->insert(
			$this->table_name,
			$insert_data,
			$insert_format
		);

		if ( ! $result ) {
			return false;
		}

		$email_id = $this->wpdb->insert_id;

		/**
		 * Fires after inserting email.
		 *
		 * @since 2.0.0
		 *
		 * @param int   $email_id    Email ID.
		 * @param array $insert_data Insert data.
		 */
		do_action( 'wp_quiz_inserted_email', $email_id, $insert_data );

		return $email_id;
	}

	/**
	 * Gets item by id or email.
	 *
	 * @param int|string $id_or_email Item ID or email address.
	 * @return array
	 */
	public function get( $id_or_email ) {
		if ( is_numeric( $id_or_email ) ) {
			$row = $this->wpdb->get_row(
				$this->wpdb->prepare(
					"SELECT * FROM {$this->table_name} WHERE id = %d",
					intval( $id_or_email )
				),
				ARRAY_A
			); // WPCS: unprepared SQL ok.
		} else {
			$row = $this->wpdb->get_row(
				$this->wpdb->prepare(
					"SELECT * FROM {$this->table_name} WHERE email = %s",
					sanitize_text_field( $id_or_email )
				),
				ARRAY_A
			); // WPCS: unprepared SQL ok.
		}

		if ( ! $row ) {
			return array();
		}

		// Make compatible with old version.
		if ( '0000-00-00 00:00:00' === $row['time'] && ! empty( $row['date'] ) ) {
			$row['time'] = $row['date'];
		}
		return $row;
	}

	/**
	 * Gets all items.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	public function get_all( array $args = array() ) {
		$sql = $this->build_sql( $args );
		return $this->wpdb->get_results( "SELECT * FROM {$this->table_name} {$sql}", 'ARRAY_A' ); // WPCS: unprepared SQL ok.
	}

	/**
	 * Gets number of items.
	 *
	 * @param array $args Query arguments.
	 * @return int
	 */
	public function get_count( array $args = array() ) {
		$sql = $this->build_sql( $args );
		return intval( $this->wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name} {$sql}" ) ); // WPCS: unprepared SQL ok.
	}

	/**
	 * Builds sql statement from query arguments.
	 *
	 * @param array $args Query arguments.
	 * @return string
	 */
	protected function build_sql( array $args = array() ) {
		$sql = '';
		if ( ! empty( $args['s'] ) ) {
			$sql .= " WHERE email LIKE '%{$args['s']}%'";
		} elseif ( ! empty( $args['ids'] ) ) {
			$ids  = is_array( $args['ids'] ) ? implode( ',', $args['ids'] ) : $args['ids'];
			$sql .= " WHERE id IN ({$ids})";
		}
		if ( ! empty( $args['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $args['orderby'] );
			$sql .= ! empty( $args['order'] ) ? ' ' . esc_sql( $args['order'] ) : ' ASC';
		} else {
			$sql .= ' ORDER BY email ASC';
		}
		if ( ! empty( $args['per_page'] ) ) {
			$sql  .= ' LIMIT ' . $args['per_page'];
			$paged = ! empty( $args['paged'] ) ? absint( $args['paged'] ) : 1;
			$sql  .= ' OFFSET ' . ( $paged - 1 ) * $args['per_page'];
		}

		return trim( $sql );
	}

	/**
	 * Deletes item by id or email address.
	 *
	 * @param int|string $id_or_email Item ID or email address.
	 */
	public function delete( $id_or_email ) {
		if ( is_numeric( $id_or_email ) ) {
			$this->wpdb->delete(
				$this->table_name,
				array( 'id' => $id_or_email ),
				array( '%d' )
			);
		} else {
			$this->wpdb->delete(
				$this->table_name,
				array( 'email' => $id_or_email ),
				array( '%s' )
			);

			/**
			 * Fires after deleting an email record.
			 *
			 * @since 2.0.0
			 *
			 * @param string $email Email address.
			 */
			do_action( 'wp_quiz_deleted_email', $id_or_email );
		}
	}
}
