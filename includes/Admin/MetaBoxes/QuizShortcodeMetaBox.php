<?php
/**
 * Quiz shortcode meta box
 *
 * @package WPQuiz\Admin
 */

namespace WPQuiz\Admin\MetaBoxes;

use WPQuiz\Helper;
use WPQuiz\PostTypeQuiz;

/**
 * Class QuizShortcodeMetaBox
 */
class QuizShortcodeMetaBox {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = '';

	/**
	 * QuizShortcodeMetaBox constructor.
	 */
	public function __construct() {
		$this->post_type = PostTypeQuiz::get_name();
	}

	/**
	 * Registers meta box.
	 */
	public function register() {
		add_action( 'edit_form_after_title', array( $this, 'render' ) );
	}

	/**
	 * Renders meta box.
	 *
	 * @param \WP_Post $post Post object.
	 */
	public function render( \WP_Post $post ) {
		if ( $this->post_type !== $post->post_type ) {
			return;
		}
		?>
		<div class="inside">
			<strong style="padding: 0 10px;"><?php esc_html_e( 'Shortcode:', 'wp-quiz-pro' ); ?></strong>
			<input type="text" value='[wp_quiz_pro id="<?php echo intval( $post->ID ); ?>"]' readonly="readonly" onclick="this.select()">
		</div>
		<?php
	}
}
