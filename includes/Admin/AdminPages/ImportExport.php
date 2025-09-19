<?php
/**
 * Import/Export page
 *
 * Register Image/Export page.
 *
 * @package WPQuiz
 */

namespace WPQuiz\Admin\AdminPages;

use WP_Query;
use WPQuiz\Admin\AdminHelper;
use WPQuiz\Exporter;
use WPQuiz\Helper;
use WPQuiz\Importer;
use WPQuiz\PostTypeQuiz;

/**
 * Class ImportExport
 */
class ImportExport {

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
	protected $page_id;

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
	 * ImportExport constructor.
	 */
	public function __construct() {
		$this->page_id     = 'wp_quiz_ie';
		$this->parent_slug = 'edit.php?post_type=' . PostTypeQuiz::get_name();
		$this->page_title  = __( 'Import/Export', 'wp-quiz-pro' );
		$this->menu_title  = $this->page_title;
		$this->capability  = 'manage_options';
	}

	/**
	 * Initializes.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'register' ), 99 );
	}

	/**
	 * Gets current tab.
	 *
	 * @return string
	 */
	protected function get_current_tab() {
		return ! empty( $_GET['tab'] ) ? $_GET['tab'] : 'import'; // WPCS: csrf, sanitization ok.
	}

	/**
	 * Registers page.
	 */
	public function register() {
		$page_title = $this->page_title;
		$tab        = $this->get_current_tab();
		if ( 'export' === $tab ) {
			$page_title = __( 'Export', 'wp-quiz-pro' );
		} elseif ( 'import' === $tab ) {
			$page_title = __( 'Import', 'wp-quiz-pro' );
		}
		$hook = add_submenu_page(
			$this->parent_slug,
			$page_title,
			$this->menu_title,
			$this->capability,
			$this->page_id,
			array( $this, 'render' )
		);
		add_action( "load-{$hook}", array( $this, 'load' ) );
	}

	/**
	 * Loads page.
	 */
	public function load() {
		if ( ! empty( $_POST['export_settings'] ) ) { // WPCS: csrf ok.
			$this->export_settings();
			exit;
		}

		if ( ! empty( $_POST['export_quizzes'] ) ) { // WPCS: csrf ok.
			$this->export_quizzes();
			exit;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Exports settings.
	 */
	protected function export_settings() {
		$exporter  = new Exporter();
		$file_name = 'wp-quiz-settings-' . date( 'Y-m-d-H-i-s' ) . '.json';

		// Send export.
		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-type: application/json' );
		header( "Content-Disposition: attachment; filename={$file_name};" );
		header( 'Content-Transfer-Encoding: binary' );
		echo $exporter->export_settings(); // WPCS: xss ok.
		die;
	}

	/**
	 * Exports quizzes.
	 */
	protected function export_quizzes() {
		if ( empty( $_POST['wp_quizzes'] ) ) { // WPCS: csrf ok.
			wp_die( esc_html__( 'You did not choose any quizzes', 'wp-quiz-pro' ) );
			return;
		}
		$file_name = 'wp-quiz-' . date( 'Y-m-d-H-i-s' ) . '.json';
		$exporter  = new Exporter();
		$query     = new WP_Query(
			array(
				'post_type'   => PostTypeQuiz::get_name(),
				'post__in'    => $_POST['wp_quizzes'], // WPCS: csrf, sanitization ok.
				'post_status' => 'any',
				'nopaging'    => true,
			)
		);

		// Send export.
		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-type: application/json' );
		header( "Content-Disposition: attachment; filename={$file_name};" );
		header( 'Content-Transfer-Encoding: binary' );
		echo $exporter->export_quizzes( $query ); // WPCS: xss ok.
		die;
	}

	/**
	 * Enqueues styles and scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'common' );

		$css = <<<CSS
.nav-tab-wrapper { margin-bottom: 25px; }
table#export_wp_quiz { border: 0; }
table#export_wp_quiz tr td { border-bottom: 1px solid #eee; padding: 10px; color: #666; font-size: 14px; }
CSS;

		wp_add_inline_style( 'wp-quiz-admin', $css );
	}

	/**
	 * Gets the list of demos.
	 *
	 * @return array
	 */
	public function get_demos() {
		$demos = array();

		if ( 'on' === Helper::get_option( 'enable_personality' ) ) {
			$demos['personality'] = array(
				'title'       => __( 'Personality', 'wp-quiz-pro' ),
				'link'        => 'https://demo.mythemeshop.com/wp-quiz-pro/category/personality/',
				'image'       => wp_quiz()->plugin_url() . 'demo/personality/image.jpg',
				'import_file' => wp_quiz()->plugin_dir() . 'demo/personality/quizzes.json',
			);
		}

		if ( 'on' === Helper::get_option( 'enable_trivia' ) ) {
			$demos['trivia'] = array(
				'title'       => __( 'Trivia', 'wp-quiz-pro' ),
				'link'        => 'https://demo.mythemeshop.com/wp-quiz-pro/category/trivia/',
				'image'       => wp_quiz()->plugin_url() . 'demo/trivia/image.jpg',
				'import_file' => wp_quiz()->plugin_dir() . 'demo/trivia/quizzes.json',
			);
		}

		if ( 'on' === Helper::get_option( 'enable_swiper' ) ) {
			$demos['swiper'] = array(
				'title'       => __( 'Swiper', 'wp-quiz-pro' ),
				'link'        => 'https://demo.mythemeshop.com/wp-quiz-pro/category/swiper/',
				'image'       => wp_quiz()->plugin_url() . 'demo/swiper/image.jpg',
				'import_file' => wp_quiz()->plugin_dir() . 'demo/swiper/quizzes.json',
			);
		}

		if ( 'on' === Helper::get_option( 'enable_flip' ) ) {
			$demos['flip'] = array(
				'title'       => __( 'Flip', 'wp-quiz-pro' ),
				'link'        => 'https://demo.mythemeshop.com/wp-quiz-pro/category/flip-cards/',
				'image'       => wp_quiz()->plugin_url() . 'demo/flip/image.jpg',
				'import_file' => wp_quiz()->plugin_dir() . 'demo/flip/quizzes.json',
			);
		}

		if ( 'on' === Helper::get_option( 'enable_fb_quiz' ) ) {
			$demos['fb_quiz'] = array(
				'title'       => __( 'Facebook Quiz', 'wp-quiz-pro' ),
				'link'        => 'https://demo.mythemeshop.com/wp-quiz-pro/category/facebook-quiz/',
				'image'       => wp_quiz()->plugin_url() . 'demo/fb-quiz/image.jpg',
				'import_file' => wp_quiz()->plugin_dir() . 'demo/fb-quiz/quizzes.json',
			);
		}

		if ( 'on' === Helper::get_option( 'enable_listquiz' ) ) {
			$demos['listquiz'] = array(
				'title'       => __( 'List Items', 'wp-quiz-pro' ),
				'link'        => 'https://demo.mythemeshop.com/wp-quiz-pro/category/list-items/',
				'image'       => wp_quiz()->plugin_url() . 'demo/list/image.jpg',
				'import_file' => wp_quiz()->plugin_dir() . 'demo/list/quizzes.json',
			);
		}

		/**
		 * Allows adding demos.
		 *
		 * @since 2.0.0
		 *
		 * @param array $demos List of demos.
		 */
		return apply_filters( 'wp_quiz_demos', $demos );
	}

	/**
	 * Gets tab page url.
	 *
	 * @param string $tab Tab name.
	 * @return string
	 */
	public function get_tab_url( $tab ) {
		return admin_url(
			sprintf(
				'edit.php?post_type=%1$s&page=%2$s&tab=%3$s',
				PostTypeQuiz::get_name(),
				$this->page_id,
				$tab
			)
		);
	}

	/**
	 * Renders page.
	 */
	public function render() {
		$action         = ! empty( $_POST['action'] ) ? $_POST['action'] : ''; // WPCS: csrf, sanitization ok.
		$import_quizzes = false;
		$error          = false;
		$success        = false;
		$page_heading   = $this->page_title;

		// Read import data from file to show the progress.
		if ( 'import-quizzes' === $action ) {
			$data = $this->get_import_quizzes_from_file();
			if ( is_array( $data ) ) {
				$import_quizzes = $data;
				$page_heading   = __( 'Import quizzes', 'wp-quiz-pro' );
			} else {
				$error = $data;
			}
		} elseif ( 'import-demo' === $action ) {
			$data = $this->get_import_quizzes_from_demo();
			if ( is_array( $data ) ) {
				$import_quizzes = $data;
				$page_heading   = __( 'Import quizzes', 'wp-quiz-pro' );
			} else {
				$error = $data;
			}
		} elseif ( 'import-settings' === $action ) {
			$data = $this->get_settings_from_file();
			if ( is_array( $data ) ) {
				$importer = new Importer();
				$importer->import_settings( $data );
				$success = __( 'Import settings successfully', 'wp-quiz-pro' );
			} else {
				$error = $data;
			}
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( $page_heading ); ?></h1>

			<?php
			if ( $error ) {
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $error ) . '</p></div>';
			} elseif ( $success ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $success ) . '</p></div>';
			} elseif ( $import_quizzes ) {
				AdminHelper::load_view(
					'admin-pages/import-export/import-progress.php',
					array(
						'page'    => $this,
						'quizzes' => $import_quizzes,
					)
				);

				wp_localize_script(
					'wp-quiz-admin',
					'wqImportQuizzes',
					array(
						'quizzes' => $import_quizzes,
					)
				);

				return;
			}

			$tab = ! empty( $_GET['tab'] ) ? $_GET['tab'] : 'import'; // WPCS: csrf, sanitization ok.
			?>

			<div class="nav-tab-wrapper">
				<a href="<?php echo esc_attr( add_query_arg( 'tab', 'import' ) ); ?>" class="nav-tab <?php echo 'import' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Import', 'wp-quiz-pro' ); ?></a>
				<a href="<?php echo esc_attr( add_query_arg( 'tab', 'export' ) ); ?>" class="nav-tab <?php echo 'export' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Export', 'wp-quiz-pro' ); ?></a>
			</div>

			<?php AdminHelper::load_view( "admin-pages/import-export/{$tab}.php", array( 'page' => $this ) ); ?>
		</div>
		<?php
	}

	/**
	 * Gets import quizzes from uploaded file.
	 *
	 * @return array|string Array of quizzes on success or error message on failure.
	 */
	protected function get_import_quizzes_from_file() {
		if ( empty( $_FILES['wp_quizzes']['tmp_name'] ) ) {
			return __( 'Please select the file', 'wp-quiz-pro' );
		}

		$content = file_get_contents( $_FILES['wp_quizzes']['tmp_name'] ); // phpcs:ignore
		if ( ! $content ) {
			return __( 'Can not import the file content or file is empty', 'wp-quiz-pro' );
		}

		$content = json_decode( $content, true );
		if ( ! $content ) {
			return __( 'Can not parse file content', 'wp-quiz-pro' );
		}

		return $content;
	}

	/**
	 * Gets import quizzes from demo.
	 *
	 * @return array|string Array of quizzes on success or error message on failure.
	 */
	protected function get_import_quizzes_from_demo() {
		if ( empty( $_POST['demo'] ) ) { // WPCS: csrf ok.
			return __( 'Please choose the demo', 'wp-quiz-pro' );
		}

		$demos = $this->get_demos();
		if ( empty( $demos[ $_POST['demo'] ]['import_file'] ) ) { // WPCS: csrf ok.
			return __( 'The demo you chose does not exist', 'wp-quiz-pro' );
		}

		return json_decode( file_get_contents( $demos[ $_POST['demo'] ]['import_file'] ), true ); // phpcs:ignore
	}

	/**
	 * Gets settings from uploaded file.
	 *
	 * @return array|string Return settings data on success or error message on failure.
	 */
	protected function get_settings_from_file() {
		if ( empty( $_FILES['wp_settings']['tmp_name'] ) ) {
			return __( 'Please select the file', 'wp-quiz-pro' );
		}

		$content = file_get_contents( $_FILES['wp_settings']['tmp_name'] ); // phpcs:ignore
		if ( ! $content ) {
			return __( 'Can not import the file content or file is empty', 'wp-quiz-pro' );
		}

		$content = json_decode( $content, true );
		if ( ! $content ) {
			return __( 'Can not parse file content', 'wp-quiz-pro' );
		}

		return $content;
	}
}
