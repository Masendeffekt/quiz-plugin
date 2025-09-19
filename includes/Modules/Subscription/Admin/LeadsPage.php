<?php
/**
 * Leads admin pages
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\Subscription\Admin;

use WPQuiz\Modules\Subscription\Database;
use WPQuiz\PostTypeQuiz;

/**
 * Class EmailSubscriber
 */
class LeadsPage {

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
	 * Emails repository.
	 *
	 * @var Database
	 */
	protected $database;

	/**
	 * EmailSubscriber constructor.
	 */
	public function __construct() {
		$this->database    = new Database();
		$this->menu_slug   = 'wp_quiz_email_subs';
		$this->parent_slug = 'edit.php?post_type=' . PostTypeQuiz::get_name();
		$this->page_title  = esc_html__( 'Leads', 'wp-quiz-pro' );
		$this->menu_title  = $this->page_title;
		$this->capability  = 'manage_options';

		$this->messages = array(
			0 => '', // Unused. Messages start at index 1.
			1 => esc_html__( 'Email Subscriber added.', 'wp-quiz-pro' ),
			2 => esc_html__( 'Email Subscriber deleted.', 'wp-quiz-pro' ),
			3 => esc_html__( 'Email Subscriber updated.', 'wp-quiz-pro' ),
			4 => esc_html__( 'Email Subscriber not added.', 'wp-quiz-pro' ),
			5 => esc_html__( 'Email Subscriber not updated.', 'wp-quiz-pro' ),
			6 => esc_html__( 'Email Subscribers deleted.', 'wp-quiz-pro' ),
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
		if ( 'wq_email_per_page' === $option ) {
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
	 * Registers page.
	 */
	public function register_page() {
		// translators: number of subscribers.
		$this->menu_title = sprintf( esc_html__( 'Leads (%d)', 'wp-quiz-pro' ), $this->database->get_count() );

		$hook = add_submenu_page(
			$this->parent_slug,
			$this->page_title,
			$this->menu_title,
			$this->capability,
			$this->menu_slug,
			array( $this, 'render_page' )
		);

		add_action( "load-{$hook}", array( $this, 'load' ) );
	}

	/**
	 * Runs on page load.
	 */
	public function load() {
		$this->list_table = new EmailSubscribersListTable();
		$this->list_table->prepare_items();
	}

	/**
	 * Renders page content.
	 */
	public function render_page() {
		?>
		<div class="wrap" id="email-page">
			<h2>
				<?php echo esc_html( $this->page_title ); ?>

				<button type="button" class="add-new-h2" id="wq-export-button" onclick="document.getElementById('wq-export-form').submit();"><?php esc_html_e( 'Export CSV', 'wp-quiz-pro' ); ?></button>
			</h2>

			<form action="<?php echo admin_url() . $this->parent_slug . '&page=' . $this->menu_slug; // WPCS: xss ok. ?>" method="post" id="wq-export-form" class="hidden">
				<?php wp_nonce_field( 'export-email-subs' ); ?>
				<input type="hidden" name="action" value="export">
				<input type="hidden" id="wq-export-ids" name="ids" value="">
			</form>

			<?php $this->display_messages(); ?>

			<form method="post">
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->menu_slug ); ?>">
				<?php $this->list_table->search_box( __( 'Search Email', 'wp-quiz-pro' ), 'search_email' ); ?>
			</form>

			<form id="posts-filter" action="" method="get">
				<input type="hidden" name="post_type" value="<?php echo esc_attr( PostTypeQuiz::get_name() ); ?>">
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->menu_slug ); ?>">

				<?php $this->list_table->display(); ?>
			</form>

		</div>

		<style>
			.column-avatar,
			.column-avatar img {
				width: 50px;
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
