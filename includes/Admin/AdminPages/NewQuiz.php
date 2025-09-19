<?php
/**
 * New Quiz page
 *
 * Register a page shows list of quiz types to create one.
 *
 * @package WPQuiz
 */

namespace WPQuiz\Admin\AdminPages;

use WPQuiz\Helper;
use WPQuiz\PostTypeQuiz;
use WPQuiz\QuizType;
use WPQuiz\QuizTypeManager;

/**
 * Class NewQuiz
 */
class NewQuiz {

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	protected $page_slug = 'wp-quiz-new';

	/**
	 * Capability.
	 *
	 * @var string
	 */
	protected $capability = 'edit_posts';

	/**
	 * Page hook suffix.
	 *
	 * @var string
	 */
	protected $hook_suffix = '';

	/**
	 * Initializes.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'register' ) );
		add_action( 'current_screen', array( $this, 'redirect_to_new_quiz_page' ) );
		add_action( 'submenu_file', array( $this, 'highlight_submenu' ) );
	}

	/**
	 * Registers page.
	 */
	public function register() {
		$this->hook_suffix = add_submenu_page(
			null,
			__( 'Add new quiz', 'wp-quiz-pro' ),
			__( 'Add new quiz', 'wp-quiz-pro' ),
			$this->capability,
			$this->page_slug,
			array( $this, 'render' )
		);
	}

	/**
	 * Renders page.
	 */
	public function render() {
		$quiz_types = QuizTypeManager::get_all();
		?>
		<div class="wrap">

			<?php if ( ! wp_quiz()->mts_activated() ) : ?>
				<div class="error">
					<p><?php printf( __( 'Please install and activate %1$s the latest version of the MyThemeShop Updater plugin %2$s to create a new quiz.', 'wp-quiz-pro' ), '<a href="https://mythemeshop.com/plugins/mythemeshop-theme-plugin-updater/" target="_blank">', '</a>' ); ?></p>
				</div>
			<?php endif; ?>

			<h1><?php esc_html_e( 'Add new quiz', 'wp-quiz-pro' ); ?></h1>

			<div class="wq-boxes">
				<?php
				foreach ( $quiz_types as $quiz_type ) :
					$url = sprintf(
						'post-new.php?post_type=%1$s&wp_quiz_type=%2$s',
						PostTypeQuiz::get_name(),
						$quiz_type->get_name()
					);
					?>
					<div class="postbox wq-box">
						<?php $quiz_type->show_title( '<h2 class="wq-box__header">', '</h2>' ); ?>
						<div class="wq-box__body">
							<?php $quiz_type->show_desc(); ?>
						</div>
						<div class="wq-box__footer">
							<a href="<?php echo esc_url( admin_url( $url ) ); ?>" class="button button-primary"><?php esc_html_e( '+ Create Quiz', 'wp-quiz-pro' ); ?></a>
						</div>
					</div><!-- End .wq-box -->
				<?php endforeach; ?>
			</div><!-- End .wq-boxes -->
		</div>

		<?php if ( ! wp_quiz()->mts_activated() ) : ?>
			<script>
				( function( $ ) {
					"use strict";

					var links = $( '.wq-box__footer a' );
					links.attr( 'disabled', 'disabled' );
					links.on( 'click', function( ev ) {
						ev.preventDefault();
					});
				})( jQuery );
			</script>
		<?php endif; ?>
		<?php
	}

	/**
	 * Highlights Add new quiz menu when visiting this page.
	 *
	 * @param string $submenu_file Submenu file.
	 * @return string
	 */
	public function highlight_submenu( $submenu_file ) {
		if ( get_current_screen()->id === $this->hook_suffix ) {
			$submenu_file = 'post-new.php?post_type=wp_quiz';
		}
		return $submenu_file;
	}

	/**
	 * Redirects to add new quiz page.
	 */
	public function redirect_to_new_quiz_page() {
		$screen = get_current_screen();

		if ( empty( $screen ) ) {
			return;
		}
		if ( 'add' !== $screen->action || 'post' !== $screen->base || PostTypeQuiz::get_name() !== $screen->post_type ) {
			return;
		}
		if ( isset( $_GET['wp_quiz_type'] ) && QuizTypeManager::get( $_GET['wp_quiz_type'] ) ) { // WPCS: csrf, sanitization ok.
			return;
		}

		wp_safe_redirect( 'edit.php?post_type=' . PostTypeQuiz::get_name() . '&page=' . $this->page_slug );
		exit;
	}
}
