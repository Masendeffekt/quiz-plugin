<?php
/**
 * Email subscribers list table
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\Subscription\Admin;

use WPQuiz\Helper;
use WPQuiz\Modules\Subscription\Database;
use WPQuiz\PlayDataTracking\PlayData;
use WPQuiz\PostTypeQuiz;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class EmailSubscribersListTable
 */
class EmailSubscribersListTable extends \WP_List_Table {

	/**
	 * Capability.
	 *
	 * @var string
	 */
	protected $capability = 'manage_options';

	/**
	 * Emails database instance.
	 *
	 * @var Database
	 */
	protected $database;

	/**
	 * EmailSubscribersListTable constructor.
	 */
	public function __construct() {
		$this->database = new Database();

		parent::__construct(
			array(
				'plural'   => 'email-subscribers',
				'singular' => 'email-subscriber',
				'ajax'     => true,
			)
		);
	}

	/**
	 * Checks if user can use AJAX.
	 *
	 * @return bool
	 */
	public function ajax_user_can() {
		return current_user_can( $this->capability );
	}

	/**
	 * Prepares items.
	 */
	public function prepare_items() {
		$this->process_actions();
		$this->add_screen_options();

		$per_page     = $this->get_items_per_page( 'wq_email_per_page', 25 );
		$current_page = $this->get_pagenum();
		$total_items  = $this->record_count();

		$data = $this->get_email_subs( $per_page, $current_page );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$this->items = $data;
	}

	/**
	 * Processes actions.
	 */
	protected function process_actions() {
		if ( ! current_user_can( $this->capability ) ) {
			return;
		}

		switch ( $this->current_action() ) {
			case 'delete':
				if ( ! empty( $_GET['email_subs'] ) && is_array( $_GET['email_subs'] ) ) { // WPCS: csrf ok.
					check_admin_referer( 'bulk-' . $this->_args['plural'] );
					foreach ( $_GET['email_subs'] as $email_sub_id ) { // WPCS: sanitization ok.
						$this->database->delete( $email_sub_id );
					}
				}
				break;

			case 'export':
				check_admin_referer( 'export-email-subs' );
				$this->export_email_subs();
				exit;
		}
	}

	/**
	 * Adds screen options.
	 */
	protected function add_screen_options() {
		add_screen_option(
			'per_page',
			array(
				'label'   => esc_html__( 'Email Subscribers per page', 'wp-quiz-pro' ),
				'default' => 25,
				'option'  => 'wq_email_per_page',
			)
		);
	}

	/**
	 * Shows no items text.
	 */
	public function no_items() {
		esc_html_e( 'No Email Subscriber.', 'wp-quiz-pro' );
	}

	/**
	 * Gets bulk actions.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions           = array();
		$actions['delete'] = esc_html__( 'Delete', 'wp-quiz-pro' );
		return $actions;
	}

	/**
	 * Gets default content of column.
	 *
	 * @param array  $item        Item data.
	 * @param string $column_name Column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
	}

	/**
	 * Gets table columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns                = array();
		$columns['cb']          = '<input type="checkbox" />';
		$columns['avatar']      = '';
		$columns['username']    = esc_html__( 'Name', 'wp-quiz-pro' );
		$columns['email']       = esc_html__( 'E-mail', 'wp-quiz-pro' );
		$columns['pid']         = esc_html__( 'Quiz', 'wp-quiz-pro' );
		$columns['time']        = esc_html__( 'Subscribed On', 'wp-quiz-pro' );
		$columns['view_detail'] = '';

		return $columns;
	}

	/**
	 * Gets sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'email'    => 'email',
			'username' => 'username',
			'time'     => 'time',
		);
	}

	/**
	 * Gets checkbox column content.
	 *
	 * @param array $item Item data.
	 * @return string
	 */
	public function column_cb( $item ) {
		return '<input type="checkbox" name="email_subs[]" value="' . $item['id'] . '" />';
	}

	/**
	 * Gets avatar column content.
	 *
	 * @param array $item Item data.
	 * @return string
	 */
	public function column_avatar( $item ) {
		return get_avatar( $item['email'], 50 );
	}

	/**
	 * Gets email column content.
	 *
	 * @param array $item Item data.
	 * @return string
	 */
	public function column_email( $item ) {
		return '<span class="wq-email">' . esc_html( $item['email'] ) . '</span>';
	}

	/**
	 * Gets username column content.
	 *
	 * @param array $item Item data.
	 * @return string
	 */
	public function column_username( $item ) {
		$username = isset( $item['username'] ) ? $item['username'] : '';
		if ( 'on' !== Helper::get_option( 'players_tracking' ) ) {
			return $username;
		}

		$play_data = PlayData::get( $item['play_data_id'] );

		$output = '<a href="' . admin_url( 'edit.php?post_type=wp_quiz&page=wp_quiz_players&player_id=' . $play_data->player_id ) . '">' . $username . '</a>';

		$actions = array();
		if ( current_user_can( $this->capability ) ) {
			$actions['delete'] = sprintf(
				'<a class="submitdelete" href="%1$s" onclick="return showNotice.warn();">%2$s</a>',
				wp_nonce_url( admin_url() . 'edit.php?post_type=' . PostTypeQuiz::get_name() . '&page=wp_quiz_email_subs&action=delete&email_subs[]=' . $item['id'] . '&message=2', 'bulk-' . $this->_args['plural'] ),
				esc_html__( 'Delete', 'wp-quiz-pro' )
			);
		}

		$output .= '<div class="email-sub-normal">' . $this->row_actions( $actions ) . '</div>';

		return $output;
	}

	/**
	 * Gets quiz column content.
	 *
	 * @param array $item Item data.
	 * @return string
	 */
	public function column_pid( $item ) {
		$quiz = PostTypeQuiz::get_quiz( $item['pid'] );
		if ( ! $quiz ) {
			return __( 'Not exist', 'wp-quiz-pro' );
		}
		return sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( get_permalink( $quiz->get_id() ) ),
			esc_html( $quiz->get_title() )
		);
	}

	/**
	 * Gets date column content.
	 *
	 * @param array $item Item data.
	 * @return string
	 */
	public function column_time( $item ) {
		return date( 'Y-m-d H:i', strtotime( $item['time'] ) );
	}

	/**
	 * Retrieves email subscribers data from the database
	 *
	 * @param int $per_page    Number of items per page.
	 * @param int $page_number Current page number.
	 * @return mixed
	 */
	public function get_email_subs( $per_page = 25, $page_number = 1 ) {
		$args = array(
			'per_page' => $per_page,
			'paged'    => $page_number,
		);
		if ( ! empty( $_POST['s'] ) ) { // WPCS: csrf ok.
			$args['s'] = sanitize_text_field( wp_unslash( $_POST['s'] ) ); // WPCS: csrf ok.
		}
		if ( ! empty( $_REQUEST['orderby'] ) ) { // WPCS: csrf ok.
			$args['orderby'] = $_REQUEST['orderby']; // WPCS: csrf, sanitization ok.
			$args['order']   = ! empty( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'ASC'; // WPCS: csrf, sanitization ok.
		} else {
			$args['orderby'] = 'email';
			$args['order']   = 'ASC';
		}

		return $this->database->get_all( $args );
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public function record_count() {
		$args = array();
		if ( ! empty( $_POST['s'] ) ) { // WPCS: csrf ok.
			$args['s'] = sanitize_text_field( wp_unslash( $_POST['s'] ) ); // WPCS: csrf ok.
		}
		return $this->database->get_count( $args );
	}

	/**
	 * Exports emails and gives the download file.
	 */
	public function export_email_subs() {
		$file_name = 'wp-quiz-pro-emails-' . date( 'Y-m-d-H-i-s' ) . '.csv';

		$args = array();
		if ( ! empty( $_POST['ids'] ) ) { // WPCS: csrf ok.
			$args['ids'] = $_POST['ids']; // WPCS: csrf, sanitization ok.
		}

		$file_content = $this->export_emails_to_csv( $args );

		// Send export.
		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-Type: text/csv' );
		header( 'Content-Type: application/force-download' );
		header( 'Content-Type: application/octet-stream' );
		header( "Content-Disposition: attachment; filename={$file_name};" );
		echo $file_content; // WPCS: xss ok.
		die;
	}

	/**
	 * Exports emails to csv format.
	 *
	 * @param array $args Repository `get_all()` args.
	 * @return string
	 */
	protected function export_emails_to_csv( array $args = array() ) {
		/**
		 * Allows changing subscribers exported csv heading line.
		 *
		 * @since 2.0.0
		 *
		 * @param string $heading_line Heading line.
		 * @param array  $args         Custom args.
		 */
		$lines = array( apply_filters( 'wp_quiz_subscribers_csv_heading_line', 'name, email', $args ) );
		$repo  = new Database();
		$items = $repo->get_all( $args );

		foreach ( $items as $item ) {
			/**
			 * Allows changing subscribers exported csv item line.
			 *
			 * @since 2.0.0
			 *
			 * @param string $line Item line.
			 * @param array  $item Item data.
			 * @param array  $args Custom args.
			 */
			$lines[] = apply_filters( 'wp_quiz_subscribers_csv_item_line', $item['username'] . ',' . $item['email'], $item, $args );
		}

		return implode( "\r\n", $lines );
	}
}
