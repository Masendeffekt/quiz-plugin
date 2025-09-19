<?php
/**
 * Players list table
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\PlayerTracking\Admin;

use WPQuiz\Exporter;
use WPQuiz\Modules\PlayerTracking\Database;
use WPQuiz\PostTypeQuiz;
use WPQuiz\QuizTypeManager;
use WPQuiz\Template;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class PlayersListTable
 */
class PlayersListTable extends \WP_List_Table {

	/**
	 * Capability.
	 *
	 * @var string
	 */
	protected $capability = 'manage_options';

	/**
	 * Players database instance.
	 *
	 * @var Database
	 */
	protected $database;

	/**
	 * PlayersListTable constructor.
	 */
	public function __construct() {
		$this->database = new Database();
		parent::__construct(
			array(
				'plural'   => 'players',
				'singular' => 'player',
				'ajax'     => true,
			)
		);
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

		$per_page     = $this->get_items_per_page( 'wq_player_per_page', 25 );
		$current_page = $this->get_pagenum();
		$total_items  = $this->record_count();
		$this->items  = $this->get_items( $per_page, $current_page );

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

		switch ( $this->current_action() ) {
			// Bulk delete players.
			case 'delete':
				if ( ! empty( $_REQUEST['players'] ) && is_array( $_REQUEST['players'] ) ) { // WPCS: csrf ok.
					check_admin_referer( 'bulk-players' );
					foreach ( $_REQUEST['players'] as $player_id ) { // WPCS: sanitization ok.
						$this->database->delete( $player_id );
					}

					if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
						$redirect_url = add_query_arg( 'message', count( $_REQUEST['players'] ) > 1 ? 6 : 2, $_REQUEST['_wp_http_referer'] ); // WPCS: sanitization ok.
						wp_safe_redirect( $redirect_url );
						exit;
					}
				}
				break;

			case 'export':
				check_admin_referer( 'export-players' );
				$this->export_players();
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
				'label'   => esc_html__( 'Players per page', 'wp-quiz-pro' ),
				'default' => 25,
				'option'  => 'wq_player_per_page',
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
	 * @param array  $item        Item data.
	 * @param string $column_name Column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
	}

	/**
	 * Gets columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns              = array();
		$columns['cb']        = '<input type="checkbox" />';
		$columns['player']    = esc_html__( 'Name', 'wp-quiz-pro' );
		$columns['quiz']      = esc_html__( 'Last played Quiz', 'wp-quiz-pro' );
		$columns['quiz_type'] = esc_html__( 'Quiz type', 'wp-quiz-pro' );
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
	 * @param array $item Item data.
	 * @return string
	 */
	public function column_cb( $item ) {
		return '<input type="checkbox" name="players[]" value="' . $item['player_id'] . '" />';
	}

	/**
	 * Gets avatar.
	 *
	 * @param array $item Item data.
	 * @return string
	 */
	protected function get_avatar_image( $item ) {
		$size = 50;
		switch ( $item['type'] ) {
			case 'guest':
				return get_avatar( '', $size );

			case 'fb_user':
				if ( '[deleted]' === $item['picture'] ) { // GDPR deleted.
					return get_avatar( '', $size );
				}
				return sprintf( '<img src="%s">', esc_url( $item['picture'] ) );

			case 'user':
				return get_avatar( $item['user_id'], $size );

			default:
				return '';
		}
	}

	/**
	 * Gets player column content.
	 *
	 * @param array $item Item data.
	 * @return string
	 */
	public function column_player( $item ) {
		$subscriber = $this->get_subscriber( $item['player_id'] );

		$output = $this->get_avatar_image( $item );
		switch ( $item['type'] ) {
			case 'guest':
				if ( ! empty( $subscriber['username'] ) ) {
					$output .= sprintf( '<strong>%s</strong>', esc_html( $subscriber['username'] ) );
				} else {
					$output .= sprintf( '<strong>%s</strong>', esc_html__( 'Guest', 'wp-quiz-pro' ) );
				}

				$output .= ' <span class="wq-ip">[' . $item['user_ip'] . ']</span>';

				if ( ! empty( $subscriber['email'] ) ) {
					$output .= '<br>' . esc_html( $subscriber['email'] );
				}
				break;

			case 'fb_user':
				$output .= sprintf( '<strong>%s</strong>', $item['first_name'] . ' ' . $item['last_name'] );
				$output .= ' <span class="wq-ip">[' . $item['user_ip'] . ']</span>';
				break;

			case 'user':
				if ( isset( $item['user_obj'] ) ) {
					$user = $item['user_obj'];
				} else {
					$user             = get_user_by( 'ID', $item['user_id'] );
					$item['user_obj'] = $user;
				}

				if ( $user ) {
					$output .= sprintf( '<strong>%s</strong>', esc_html( $user->display_name ) );
				} elseif ( ! empty( $subscriber['username'] ) ) {
					$output .= sprintf( '<strong>%s</strong>', esc_html( $subscriber['username'] ) );
				} else {
					$output .= sprintf( '<strong>%s</strong>', esc_html__( 'Guest', 'wp-quiz-pro' ) );
				}

				$output .= ' <span class="wq-ip">[' . $item['user_ip'] . ']</span>';

				if ( ! empty( $subscriber['email'] ) ) {
					$output .= '<br>' . esc_html( $subscriber['email'] );
				}
				break;
		}

		$detail_url = add_query_arg(
			array(
				'post_type' => PostTypeQuiz::get_name(),
				'page'      => 'wp_quiz_players',
				'player_id' => $item['player_id'],
			),
			admin_url( 'edit.php' )
		);

		$delete_url = add_query_arg(
			array(
				'post_type'        => PostTypeQuiz::get_name(),
				'page'             => 'wp_quiz_players',
				'action'           => 'delete',
				'players[]'        => $item['player_id'],
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
	 * Gets subscriber links with given play id.
	 *
	 * @param int $play_data_id Play ID.
	 * @return array|false
	 */
	protected function get_subscriber( $play_data_id ) {
		global $wpdb;
		$record = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wp_quiz_emails WHERE play_data_id = %d", intval( $play_data_id ) ), ARRAY_A ); // WPCS: unprepared SQL, cache ok.
		if ( ! $record ) {
			return false;
		}

		return $record;
	}

	/**
	 * Gets quiz column output.
	 *
	 * @param array $item Item data.
	 * @return string
	 */
	public function column_quiz( $item ) {
		return get_the_title( $item['quiz_id'] );
	}

	/**
	 * Gets played at column output.
	 *
	 * @param array $item Item data.
	 * @return string
	 */
	public function column_played_at( $item ) {
		return date( 'Y-m-d H:i', strtotime( $item['played_at'] ) );
	}

	/**
	 * Gets view detail column output.
	 *
	 * @param array $item Item data.
	 * @return string
	 */
	public function column_view_detail( $item ) {
		if ( empty( $item['quiz_data'] ) ) {
			return '';
		}
		$output = sprintf( '<a href="#" class="wp-quiz-toggle-player-tracking-data" data-id="%1$s">%2$s</a>', intval( $item['player_id'] ), esc_html__( 'Show/Hide detail', 'wp-quiz-pro' ) );
		ob_start();
		$quiz_type = QuizTypeManager::get( $item['quiz_type'] );
		$quiz_type->show_tracking_data( $item );
		$output .= ob_get_clean();
		return $output;
	}

	/**
	 * Retrieves player data from the database.
	 *
	 * @param int $per_page    Number of items per page.
	 * @param int $page_number Current page number.
	 * @return mixed
	 */
	public function get_items( $per_page = 25, $page_number = 1 ) {
		$args = array(
			'per_page' => $per_page,
			'paged'    => $page_number,
		);
		if ( ! empty( $_POST['s'] ) ) { // WPCS: csrf ok.
			$args['s'] = sanitize_text_field( wp_unslash( $_POST['s'] ) ); // WPCS: csrf ok.
		}
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$args['orderby'] = $_REQUEST['orderby']; // WPCS: csrf, sanitization ok.
			$args['order']   = ! empty( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'ASC'; // WPCS: csrf, sanitization ok.
		}

		return $this->database->get_list_table_items( $args );
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
		return $this->database->get_list_table_count( $args );
	}

	/**
	 * Exports players.
	 */
	public function export_players() {
		$file_name = 'wp-quiz-pro-players-' . date( 'Y-m-d-H-i-s' ) . '.csv';
		$args      = array();
		if ( ! empty( $_POST['ids'] ) ) { // WPCS: csrf ok.
			$args['ids'] = $_POST['ids']; // WPCS: csrf, sanitization ok.
		}

		$file_content = $this->get_export_players_content( $args );

		// Send export.
		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-Type: text/csv' );
		header( 'Content-Type: application/force-download' );
		header( 'Content-Type: application/octet-stream' );
		header( "Content-Disposition: attachment; filename={$file_name};" );
		echo $file_content; // WPCS; xss ok.
		die;
	}

	/**
	 * Exports players to csv format.
	 *
	 * @param array $args Repository `get_all()` args.
	 * @return string
	 */
	public function get_export_players_content( array $args = array() ) {
		$database = new Database();
		$items    = $database->get_list_table_items( $args );
		$line     = implode(
			',',
			array(
				'Id',
				__( 'Last played quiz', 'wp-quiz-pro' ),
				__( 'Played at', 'wp-quiz-pro' ),
				__( 'User IP', 'wp-quiz-pro' ),
				__( 'Player', 'wp-quiz-pro' ),
				__( 'Result', 'wp-quiz-pro' ),
				__( 'Quiz type', 'wp-quiz-pro' ),
			)
		);

		/**
		 * Allows changing players exported csv heading line.
		 *
		 * @since 2.0.0
		 *
		 * @param string $line Heading line.
		 * @param array  $args Custom args.
		 */
		$lines = array( apply_filters( 'wp_quiz_players_csv_heading_line', $line, $args ) );

		foreach ( $items as $item ) {
			$played_at = '0000-00-00 00:00:00' !== $item['played_at'] || empty( $item['date'] ) ? $item['played_at'] : $item['date'];

			$player_name = __( 'Guest', 'wp-quiz-pro' );
			if ( 'user' === $item['type'] ) {
				$player_name = get_user_by( 'ID', $item['user_id'] )->display_name;
			} elseif ( 'fb_user' === $item['type'] ) {
				$player_name = $item['first_name'] . ' ' . $item['last_name'];
			}

			$result = $item['result'];
			if ( 'fb_quiz' !== $item['quiz_type'] ) {
				$quiz_data = json_decode( $item['quiz_data'], true );
				if ( ! empty( $quiz_data['results'][ $item['result'] ]['title'] ) ) {
					$result = $quiz_data['results'][ $item['result'] ]['title'];
				}
			}

			$line = sprintf(
				'%d,%s,%s,%s,%s,%s,%s',
				$item['player_id'],
				esc_html( get_the_title( $item['quiz_id'] ) ),
				$played_at,
				$item['user_ip'],
				$player_name,
				$result,
				$item['quiz_type']
			);

			/**
			 * Allows changing players exported csv item line.
			 *
			 * @since 2.0.0
			 *
			 * @param string $line Item line.
			 * @param array  $item Item data.
			 * @param array  $args Custom args.
			 */
			$lines[] = apply_filters( 'wp_quiz_players_csv_item_line', $line, $item, $args );
		}

		return implode( "\r\n", $lines );
	}
}
