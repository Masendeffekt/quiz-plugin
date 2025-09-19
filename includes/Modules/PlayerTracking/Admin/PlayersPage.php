<?php
/**
 * Players admin pages
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\PlayerTracking\Admin;

use WPQuiz\Modules\PlayerTracking\Database;
use WPQuiz\Modules\PlayerTracking\Player;
use WPQuiz\PlayDataTracking\PlayData;
use WPQuiz\PostTypeQuiz;
use WPQuiz\QuizTypeManager;

/**
 * Class Players
 */
class PlayersPage {

	/**
	 * Parent slug.
	 *
	 * @var string
	 */
	protected $parent_slug;

	/**
	 * Page ID.
	 *
	 * @var string
	 */
	protected $menu_slug;

	/**
	 * Page title.
	 *
	 * @var string
	 */
	protected $page_title;

	/**
	 * Menu title.
	 *
	 * @var string
	 */
	protected $menu_title;

	/**
	 * Capability.
	 *
	 * @var string
	 */
	protected $capability;

	/**
	 * Messages.
	 *
	 * @var array
	 */
	protected $messages = array();

	/**
	 * List table object.
	 *
	 * @var \WP_List_Table
	 */
	protected $list_table;

	/**
	 * Database instance.
	 *
	 * @var Database
	 */
	protected $database;

	/**
	 * Players constructor.
	 */
	public function __construct() {
		$this->database    = new Database();
		$this->menu_slug   = 'wp_quiz_players';
		$this->parent_slug = 'edit.php?post_type=' . PostTypeQuiz::get_name();
		$this->page_title  = esc_html__( 'Players', 'wp-quiz-pro' );
		$this->menu_title  = $this->page_title;
		$this->capability  = 'manage_options';

		$this->messages = array(
			0 => '', // Unused. Messages start at index 1.
			1 => esc_html__( 'Player added.', 'wp-quiz-pro' ),
			2 => esc_html__( 'Player deleted.', 'wp-quiz-pro' ),
			3 => esc_html__( 'Player updated.', 'wp-quiz-pro' ),
			4 => esc_html__( 'Player not added.', 'wp-quiz-pro' ),
			5 => esc_html__( 'Player not updated.', 'wp-quiz-pro' ),
			6 => esc_html__( 'Players deleted.', 'wp-quiz-pro' ),
		);

		add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );
	}

	/**
	 * Sets screen option value.
	 *
	 * @param mixed  $status If `status` is set to `false`, option won't be saved.
	 * @param string $option Option name.
	 * @param mixed  $value  Option value.
	 * @return mixed
	 */
	public function set_screen_option( $status, $option, $value ) {
		if ( 'wq_player_per_page' === $option || 'wq_player_play_data_item_per_page' === $option ) {
			return $value;
		}
		return $status;
	}

	/**
	 * Initializes.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'register_page' ) );
	}

	/**
	 * Register page.
	 */
	public function register_page() {
		// translators: number of players.
		$this->menu_title = sprintf( esc_html__( 'Players (%d)', 'wp-quiz-pro' ), $this->database->get_list_table_count() );

		if ( isset( $_GET['play_data_id'] ) && intval( $_GET['play_data_id'] ) ) { // WPCS: csrf ok.
			// translators: Play data ID.
			$this->page_title = sprintf( __( 'Play data #%d', 'wp-quiz-pro' ), absint( $_GET['play_data_id'] ) ); // WPCS: csrf ok.
			$render_method    = array( $this, 'render_play_data_page' );
		} elseif ( isset( $_GET['player_id'] ) && intval( $_GET['player_id'] ) ) { // WPCS: csrf ok.
			$player_id = intval( $_GET['player_id'] ); // WPCS: csrf ok.
			$player    = Player::get( $player_id );
			if ( $player ) {
				$this->page_title = $player->get_display_name() . ' #' . $player_id;
			} else {
				// translators: Player ID.
				$this->page_title = sprintf( __( 'Player #%d', 'wp-quiz-pro' ), absint( $player_id ) );
			}
			$render_method    = array( $this, 'render_player_page' );

			$this->messages = array(
				0 => '', // Unused. Messages start at index 1.
				1 => esc_html__( 'Item added.', 'wp-quiz-pro' ),
				2 => esc_html__( 'Item deleted.', 'wp-quiz-pro' ),
				3 => esc_html__( 'Item updated.', 'wp-quiz-pro' ),
				4 => esc_html__( 'Item not added.', 'wp-quiz-pro' ),
				5 => esc_html__( 'Item not updated.', 'wp-quiz-pro' ),
				6 => esc_html__( 'Items deleted.', 'wp-quiz-pro' ),
			);
		} elseif ( isset( $_GET['quiz_id'] ) && intval( $_GET['quiz_id'] ) ) { // WPCS: csrf ok.
			// translators: Player ID.
			$this->page_title = sprintf( __( 'Quiz #%d', 'wp-quiz-pro' ), absint( $_GET['quiz_id'] ) ); // WPCS: csrf ok.
			$render_method    = array( $this, 'render_player_page' );

			$this->messages = array(
				0 => '', // Unused. Messages start at index 1.
				1 => esc_html__( 'Item added.', 'wp-quiz-pro' ),
				2 => esc_html__( 'Item deleted.', 'wp-quiz-pro' ),
				3 => esc_html__( 'Item updated.', 'wp-quiz-pro' ),
				4 => esc_html__( 'Item not added.', 'wp-quiz-pro' ),
				5 => esc_html__( 'Item not updated.', 'wp-quiz-pro' ),
				6 => esc_html__( 'Items deleted.', 'wp-quiz-pro' ),
			);
		} else {
			$render_method = array( $this, 'render_players_page' );
		}

		$hook = add_submenu_page( $this->parent_slug, $this->page_title, $this->menu_title, $this->capability, $this->menu_slug, $render_method );
		add_action( "load-{$hook}", array( $this, 'load' ) );
	}

	/**
	 * Runs of list page load.
	 */
	public function load() {
		if ( isset( $_GET['play_data_id'] ) && intval( $_GET['play_data_id'] ) ) { // WPCS: csrf ok.
			// Do nothing.
		} elseif ( isset( $_GET['player_id'] ) && intval( $_GET['player_id'] ) || isset( $_GET['quiz_id'] ) && intval( $_GET['quiz_id'] ) ) { // WPCS: csrf ok.
			$this->list_table = new PlayerPlayDataListTable();
			$this->list_table->prepare_items();
		} else {
			$this->list_table = new PlayersListTable();
			$this->list_table->prepare_items();
		}
	}

	/**
	 * Renders players page content.
	 */
	public function render_players_page() {
		?>
		<div class="wrap" id="wp-quiz-players-page">
			<h2>
				<?php echo esc_html( $this->page_title ); ?>
				<button type="button" class="add-new-h2" id="wq-export-button" onclick="document.getElementById('wq-export-form').submit();"><?php esc_html_e( 'Export CSV', 'wp-quiz-pro' ); ?></button>
			</h2>

			<form action="<?php echo admin_url() . $this->parent_slug . '&page=' . $this->menu_slug; // WPCS: xss ok. ?>" method="post" id="wq-export-form" class="hidden">
				<?php wp_nonce_field( 'export-players' ); ?>
				<input type="hidden" name="action" value="export">
				<input type="hidden" id="wq-export-ids" name="ids" value="">
			</form>

			<?php $this->display_messages(); ?>

			<form method="post">
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->menu_slug ); ?>">
				<?php $this->list_table->search_box( __( 'Search Player', 'wp-quiz-pro' ), 'search_player' ); ?>
			</form>

			<form id="posts-filter" action="" method="get">
				<input type="hidden" name="post_type" value="<?php echo esc_attr( PostTypeQuiz::get_name() ); ?>">
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->menu_slug ); ?>">

				<?php if ( isset( $_GET['player_id'] ) && intval( $_GET['player_id'] ) ) : ?>
					<input type="hidden" name="player_id" value="<?php echo intval( $_GET['player_id'] ); ?>">
				<?php endif; ?>

				<?php $this->list_table->display(); ?>
			</form>

		</div>

		<style>
			.column-player img {
				width: 50px;
				height: auto;
				float: left;
				margin-right: 8px;
			}
		</style>
		<?php
	}

	/**
	 * Renders player page content.
	 */
	public function render_player_page() {
		$view_all_url = add_query_arg(
			array(
				'post_type' => PostTypeQuiz::get_name(),
				'page'      => $this->menu_slug,
			),
			admin_url( 'edit.php' )
		);
		?>
		<div class="wrap" id="wp-quiz-player-page">
			<h2>
				<?php echo esc_html( $this->page_title ); ?>
				<a href="<?php echo esc_url( $view_all_url ); ?>" class="add-new-h2"><?php esc_html_e( 'View all players', 'wp-quiz-pro' ); ?></a>
			</h2>

			<?php $this->display_messages(); ?>

			<form method="post">
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->menu_slug ); ?>">
				<?php $this->list_table->search_box( __( 'Search Player', 'wp-quiz-pro' ), 'search_player' ); ?>
			</form>

			<form id="posts-filter" action="" method="get">
				<input type="hidden" name="post_type" value="<?php echo esc_attr( PostTypeQuiz::get_name() ); ?>">
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->menu_slug ); ?>">

				<?php if ( isset( $_GET['player_id'] ) && intval( $_GET['player_id'] ) ) : ?>
					<input type="hidden" name="player_id" value="<?php echo intval( $_GET['player_id'] ); ?>">
				<?php endif; ?>

				<?php $this->list_table->display(); ?>
			</form>

		</div>
		<?php
	}

	/**
	 * Renders play data page.
	 */
	public function render_play_data_page() {
		$play_data_id = ! empty( $_GET['play_data_id'] ) ? intval( $_GET['play_data_id'] ) : 0; // WPCS: csrf ok.
		$play_data    = PlayData::get( $play_data_id );
		if ( ! $play_data ) {
			esc_html_e( 'This item does not exist', 'wp-quiz-pro' );
			return;
		}
		$quiz_type = QuizTypeManager::get( $play_data->quiz_type );
		?>
		<div class="wrap" id="detail-player-page">
			<h1>
				<?php // translators: player id. ?>
				<?php printf( esc_html__( 'Player #%s', 'wp-quiz-pro' ), intval( $play_data->id ) ); ?>
				<a href="<?php echo esc_url( admin_url() . $this->parent_slug . '&page=' . $this->menu_slug ); ?>" class="add-new-h2"><?php esc_html_e( 'All Players', 'wp-quiz-pro' ); ?></a>
			</h1>

			<div id="poststuff">
				<div class="postbox">
					<h2 class="hndle" style="cursor: auto;"><?php esc_html_e( 'Player info', 'wp-quiz-pro' ); ?></h2>

					<div class="inside">
						<?php
						if ( $quiz_type ) {
							$quiz_type->show_tracking_data( $play_data );
						} else {
							echo '<pre>';
							print_r( $play_data->to_array() );
							echo '</pre>';
						}
						?>
					</div>
				</div>
			</div>
		</div>

		<style>
			.wp-quiz-tracking h4 {
				font-size: 18px;
				line-height: 1.3;
				margin-bottom: 10px;
				max-width: 600px;
			}

			.wp-quiz-tracking img {
				max-width: 100%;
				height: auto;
			}

			.wp-quiz-tracking .answers:after {
				content: " ";
				display: block;
				height: 0;
				visibility: hidden;
				clear: both;
			}

			.wp-quiz-tracking .answers.image-answers .answer span {
				display: block;
				padding: 0 10px 4px 10px;
			}

			.wp-quiz-tracking .answers.image-answers .answer {
				background: #fff5b0;
				width: 180px;
				float: left;
				margin-right: 15px;
				border-radius: 3px;
				display: block;
			}

			.wp-quiz-tracking .answers.text-answers .answer {
				background: #fff5b0;
				display: inline-block;
				padding: 7px 10px;
				border-radius: 3px;
			}

			.wp-quiz-tracking .answers .answer.correct {
				background: #C8E6C9;
			}

			.wp-quiz-tracking .answers .answer.incorrect {
				background: #ffcdd2;
			}

			.wp-quiz-tracking .result {
				width: 500px;
				max-width: 100%;
				margin-top: 50px;
			}

			.wp-quiz-tracking .like {
				color: #7BAD40;
			}

			.wp-quiz-tracking .dislike {
				color: #D4471B;
			}
		</style>
		<?php
	}

	/**
	 * Displays message.
	 */
	public function display_messages() {
		$message = false;
		if ( isset( $_REQUEST['message'] ) && (int) $_REQUEST['message'] && isset( $this->messages[ (int) $_REQUEST['message'] ] ) ) { // WPCS: csrf ok.
			$message = $this->messages[ (int) $_REQUEST['message'] ];
		}
		$class = ( isset( $_REQUEST['error'] ) ) ? 'error' : 'updated'; // WPCS: csrf ok.

		if ( $message ) :
			?>
			<div id="message" class="<?php echo esc_attr( $class ); ?> notice is-dismissible">
				<p><?php echo wp_kses_post( $message ); ?></p>
			</div>
			<?php
		endif;
	}
}
