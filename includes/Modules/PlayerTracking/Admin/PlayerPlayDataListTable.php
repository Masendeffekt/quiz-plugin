<?php
/**
 * Player play data list table
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\PlayerTracking\Admin;

use WPQuiz\Exporter;
use WPQuiz\Modules\PlayerTracking\Database;
use WPQuiz\PlayDataTracking\Database as PlayDataDB;
use WPQuiz\PlayDataTracking\PlayData;
use WPQuiz\PostTypeQuiz;
use WPQuiz\QuizTypeManager;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class PlayerPlayData
 */
class PlayerPlayDataListTable extends \WP_List_Table {

	/**
	 * Capability.
	 *
	 * @var string
	 */
	protected $capability = 'manage_options';

	/**
	 * Player ID.
	 *
	 * @var int
	 */
	protected $player_id;

	/**
	 * Quiz ID.
	 *
	 * @var int
	 */
	protected $quiz_id;

	/**
	 * PlayerPlayData constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'plural'   => 'play_data_items',
				'singular' => 'play_data_item',
				'ajax'     => true,
			)
		);

		$this->player_id = isset( $_GET['player_id'] ) ? intval( $_GET['player_id'] ) : 0; // WPCS: csrf ok.
		$this->quiz_id   = isset( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : 0; // WPCS: csrf ok.
	}

	/**
	 * Checks if current user can use AJAX.
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

		$per_page     = $this->get_items_per_page( 'wq_player_play_data_item_per_page', 25 );
		$current_page = $this->get_pagenum();
		$total_items  = $this->record_count();

		$this->items = $this->get_play_data_items( $per_page, $current_page );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * Processes actions.
	 */
	protected function process_actions() {
		if ( ! current_user_can( $this->capability ) ) {
			return;
		}

		$database = new PlayDataDB();

		switch ( $this->current_action() ) {
			// Delete item.
			case 'delete':
				if ( ! empty( $_REQUEST['player_id'] ) && ! empty( $_REQUEST['items'] ) && is_array( $_REQUEST['items'] ) ) { // WPCS: csrf ok.
					check_admin_referer( 'bulk-play_data_items' );
					foreach ( $_REQUEST['items'] as $item_id ) { // WPCS: sanitization ok.
						$database->delete( $item_id );
					}

					if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
						$redirect_url = add_query_arg( 'message', count( $_REQUEST['items'] ) > 1 ? 6 : 2, $_REQUEST['_wp_http_referer'] ); // WPCS: sanitization ok.
						wp_safe_redirect( $redirect_url );
						exit;
					}
				}
				break;
		}
	}

	/**
	 * Adds screen options.
	 */
	protected function add_screen_options() {
		add_screen_option(
			'per_page',
			array(
				'label'   => esc_html__( 'Items per page', 'wp-quiz-pro' ),
				'default' => 25,
				'option'  => 'wq_player_play_data_item_per_page',
			)
		);
	}

	/**
	 * Shows message if no items.
	 */
	public function no_items() {
		esc_html_e( 'No Player.', 'wp-quiz-pro' );
	}

	/**
	 * Gets bulk actions.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'wp-quiz-pro' ),
		);
		return $actions;
	}

	/**
	 * Gets default content of column.
	 *
	 * @param PlayData $item        Item data.
	 * @param string   $column_name Column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		return isset( $item->$column_name ) ? $item->$column_name : '';
	}

	/**
	 * Gets columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns              = array();
		$columns['cb']        = '<input type="checkbox" />';
		$columns['title']     = esc_html__( 'Quiz title', 'wp-quiz-pro' );
		$columns['quiz_type'] = esc_html__( 'Quiz type', 'wp-quiz-pro' );
		$columns['result']    = esc_html__( 'Result', 'wp-quiz-pro' );
		$columns['played_at'] = esc_html__( 'Played at', 'wp-quiz-pro' );
		return $columns;
	}

	/**
	 * Gets sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'played_at' => 'played_at',
		);
	}

	/**
	 * Gets checkbox column content.
	 *
	 * @param PlayData $item Item data.
	 * @return string
	 */
	public function column_cb( $item ) {
		return '<input type="checkbox" name="items[]" value="' . $item->id . '" />';
	}

	/**
	 * Gets title column content.
	 *
	 * @param PlayData $item Item data.
	 * @return string
	 */
	public function column_title( $item ) {
		$output = $item->get_quiz()->get_title();

		$detail_url = add_query_arg(
			array(
				'post_type'    => PostTypeQuiz::get_name(),
				'page'         => 'wp_quiz_players',
				'play_data_id' => $item->id,
			),
			admin_url( 'edit.php' )
		);

		$delete_url = add_query_arg(
			array(
				'post_type'        => PostTypeQuiz::get_name(),
				'page'             => 'wp_quiz_players',
				'action'           => 'delete',
				'items[]'          => $item->id,
				'player_id'        => $this->player_id,
				'_wp_http_referer' => rawurlencode( add_query_arg( 'page', 'wp_quiz_players' ) ),
			),
			admin_url( 'edit.php' )
		);

		$actions = array(
			'view'   => sprintf( '<a href="%s">%s</a>', $detail_url, __( 'View detail', 'wp-quiz-pro' ) ),
			'delete' => sprintf(
				'<a class="submitdelete" href="%1$s" onclick="return showNotice.warn();">%2$s</a>',
				wp_nonce_url( $delete_url, 'bulk-' . $this->_args['plural'] ),
				__( 'Delete', 'wp-quiz-pro' )
			),
		);

		$output .= '<div class="player-normal">' . $this->row_actions( $actions ) . '</div>';

		return $output;
	}

	/**
	 * Gets played at column output.
	 *
	 * @param PlayData $item Item data.
	 * @return string
	 */
	public function column_played_at( $item ) {
		return date( 'Y-m-d H:i', strtotime( $item->played_at ) );
	}

	/**
	 * Gets result column output.
	 *
	 * @param PlayData $item Item data.
	 * @return string
	 */
	public function column_result( $item ) {
		if ( 'trivia' === $item->quiz_type ) {
			// translators: %1$s: number of correct answers, %2$s: total questions.
			return sprintf( __( '%1$s out of %2$s', 'wp-quiz-pro' ), $item->correct_answered, count( $item->quiz_data['questions'] ) );
		}
		if ( 'personality' === $item->quiz_type ) {
			$result_id = $item->result;
			$results   = $item->quiz_data['results'];
			if ( ! empty( $results[ $result_id ]['title'] ) ) {
				return $results[ $result_id ]['title'];
			}
		}
		return '';
	}

	/**
	 * Retrieves player data from the database.
	 *
	 * @param int $per_page    Number of items per page.
	 * @param int $page_number Current page number.
	 * @return mixed
	 */
	public function get_play_data_items( $per_page = 25, $page_number = 1 ) {
		$database = new PlayDataDB();
		$args     = array(
			'per_page'  => $per_page,
			'paged'     => $page_number,
			'player_id' => $this->player_id,
			'quiz_id'   => $this->quiz_id,
		);
		if ( ! empty( $_POST['s'] ) ) { // WPCS: csrf ok.
			$args['s'] = sanitize_text_field( wp_unslash( $_POST['s'] ) ); // WPCS: csrf ok.
		}
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$args['orderby'] = $_REQUEST['orderby']; // WPCS: csrf, sanitization ok.
			$args['order']   = ! empty( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'ASC'; // WPCS: csrf, sanitization ok.
		}

		return $database->get_all( $args );
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public function record_count() {
		$database = new PlayDataDB();
		$args     = array( 'player_id' => $this->player_id );
		if ( ! empty( $_POST['s'] ) ) { // WPCS: csrf ok.
			$args['s'] = sanitize_text_field( wp_unslash( $_POST['s'] ) ); // WPCS: csrf ok.
		}
		return $database->get_count( $args );
	}
}
