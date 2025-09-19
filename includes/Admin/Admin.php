<?php
/**
 * Plugin admin class
 *
 * @package WPQuiz
 */

namespace WPQuiz\Admin;

use WP_Post;
use WPQuiz\Admin\AdminPages\EmailSubscribers;
use WPQuiz\Admin\AdminPages\ImportExport;
use WPQuiz\Admin\AdminPages\NewQuiz;
use WPQuiz\Admin\AdminPages\Settings;
use WPQuiz\Admin\AdminPages\Support;
use WPQuiz\Admin\CMB2Custom\CustomFields;
use WPQuiz\Admin\MetaBoxes\QuizMetaBox;
use WPQuiz\Admin\MetaBoxes\QuizShortcodeMetaBox;
use WPQuiz\Helper;
use WPQuiz\PostTypeQuiz;
use WPQuiz\Repositories\Database;

/**
 * Class Admin
 */
class Admin {

	/**
	 * Initializes admin functions.
	 */
	public function init() {
		$this->includes();

		// CMB2 custom fields.
		( new CustomFields() )->register();

		// Meta boxes.
		( new QuizMetaBox() )->init();
		( new QuizShortcodeMetaBox() )->register();

		// Settings pages.
		( new NewQuiz() )->init();

		if ( wp_quiz()->mts_activated() ) {
			( new Settings() )->init();
			( new ImportExport() )->init();
		}

		$assets = new Assets();
		$assets->init();

		$editor_button = new EditorButtons();
		$editor_button->init();

		$this->hooks();
	}

	/**
	 * Registers placeholder page.
	 */
	public function register_placeholder_page() {
		add_submenu_page(
			'edit.php?post_type=wp_quiz',
			esc_html__( 'Settings', 'wp-quiz-pro' ),
			esc_html__( 'Settings', 'wp-quiz-pro' ),
			'manage_options',
			'wp_quiz_config',
			array( $this, 'render_placeholder_page' )
		);
	}

	/**
	 * Prints placeholder page.
	 */
	public function render_placeholder_page() {
		?>
		<div class="wrap wp-review">
			<h2><?php esc_html_e( 'Settings', 'wp-quiz-pro' ); ?></h2>
			<p><?php printf( __( 'Please install and activate %1$s the latest version of the MyThemeShop Updater plugin %2$s to edit the plugin settings.', 'wp-quiz-pro' ), '<a href="https://mythemeshop.com/plugins/mythemeshop-theme-plugin-updater/" target="_blank">', '</a>' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Redirects to the new quiz page.
	 */
	public function redirect_to_new_quiz_page() {
		if ( wp_quiz()->mts_activated() ) {
			return;
		}
		$screen = get_current_screen();
		if ( 'wp_quiz' === $screen->id && 'add' === $screen->action && ! empty( $_GET['wp_quiz_type'] ) ) {
			wp_redirect( admin_url( 'edit.php?post_type=wp_quiz&page=wp-quiz-new' ) );
			exit;
		}
	}

	/**
	 * Adds hooks.
	 */
	protected function hooks() {
		add_filter( 'cmb2_localized_data', array( $this, 'cmb2_change_codemirror_defaults' ) );
		add_action( 'save_post_' . PostTypeQuiz::get_name(), array( $this, 'add_default_ad_codes' ), 10, 3 );

		if ( ! wp_quiz()->mts_activated() ) {
			add_action( 'admin_menu', array( $this, 'register_placeholder_page' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'redirect_to_new_quiz_page' ), 99 );
		}
	}

	/**
	 * Includes files.
	 */
	protected function includes() {
		require_once wp_quiz()->plugin_dir() . 'vendor/cmb2/cmb2/init.php';
		require_once wp_quiz()->libraries_dir() . 'cmb-field-select2/cmb-field-select2.php';
	}

	/**
	 * Changes codemirror defaults.
	 *
	 * @param array $l10n_data Codemirror defaults.
	 * @return mixed
	 */
	public function cmb2_change_codemirror_defaults( $l10n_data ) {
		$l10n_data['defaults']['code_editor']['codemirror']['direction'] = is_rtl() ? 'rtl' : 'ltr';
		return $l10n_data;
	}

	/**
	 * Adds default ad codes for when create quiz.
	 *
	 * @param int     $post_id Quiz ID.
	 * @param WP_Post $post    Quiz post object.
	 * @param bool    $update  Is updating quiz or not.
	 */
	public function add_default_ad_codes( $post_id, $post, $update ) {
		if ( $update ) {
			return;
		}
		$ad_codes = Helper::get_option( 'ad_codes' );
		if ( ! $ad_codes ) {
			return;
		}
		update_post_meta( $post_id, 'wp_quiz_ad_codes', $ad_codes );
	}
}
