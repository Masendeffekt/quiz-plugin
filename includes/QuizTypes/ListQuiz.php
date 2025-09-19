<?php
/**
 * List quiz type
 *
 * @package WPQuizPro
 */

namespace WPQuiz\QuizTypes;

use WPQuiz\PostTypeQuiz;
use WPQuiz\Quiz;
use WPQuiz\QuizType;

/**
 * Class ListQuiz
 */
class ListQuiz extends QuizType {

	/**
	 * Has results or not.
	 *
	 * @var bool
	 */
	protected $has_results = false;

	/**
	 * Has answers or not.
	 *
	 * @var bool
	 */
	protected $has_answers = false;

	/**
	 * Quiz type icon class.
	 *
	 * @var string
	 */
	protected $icon = 'dashicons dashicons-format-gallery';

	/**
	 * Flip constructor.
	 */
	public function __construct() {
		$this->name = 'listquiz';
		$this->desc = __( 'Combine the power of listicles and quizzes to generate some incredible engagement from your users and great results for yourself.', 'wp-quiz-pro' );

		add_action( 'save_post_' . PostTypeQuiz::get_name(), array( $this, 'save_custom_data' ) );
	}

	/**
	 * Gets quiz type title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'List quiz', 'wp-quiz-pro' );
	}

	/**
	 * Save custom data.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_custom_data( $post_id ) {
		if ( isset( $_POST['wp_quiz_list_color'] ) ) { // WPCS: csrf ok.
			update_post_meta( $post_id, 'wp_quiz_list_color', sanitize_text_field( wp_unslash( $_POST['wp_quiz_list_color'] ) ) ); // WPCS: csrf ok.
		}
		if ( isset( $_POST['wp_quiz_list_type'] ) ) { // WPCS: csrf ok.
			update_post_meta( $post_id, 'wp_quiz_list_type', sanitize_text_field( wp_unslash( $_POST['wp_quiz_list_type'] ) ) ); // WPCS: csrf ok.
		}
	}

	/**
	 * Gets quiz default settings.
	 *
	 * @return array
	 */
	public function get_default_settings() {
		$settings               = parent::get_default_settings();
		$settings['list_color'] = '#009cff';
		return $settings;
	}

	/**
	 * Gets default data for question.
	 *
	 * @return array
	 */
	public function get_default_question() {
		return array(
			'title'         => '',
			'showVotesUp'   => 1,
			'showVotesDown' => 1,
			'votesUp'       => '',
			'votesDown'     => '',
			'desc'          => '',
			'image'         => '',
			'imageId'       => '',
			'imageCredit'   => '',
			'bgColor'       => '',
			'fontColor'     => '',
		);
	}

	/**
	 * Prints backend questions list.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_questions_list( Quiz $quiz ) {
		$list_color = $quiz->get_setting( 'list_color' );
		$list_type  = $quiz->get_setting( 'list_type' );
		?>
		<div class="wp-quiz-questions wp-quiz-<?php echo esc_attr( $this->name ); ?>-questions" style="--list-color: <?php echo esc_attr( $list_color ); ?>;">
			<div class="wp-quiz-questions-heading">
				<h3><?php esc_html_e( 'Questions', 'wp-quiz-pro' ); ?></h3>
				<div class="wp-quiz-list-options">
					<input type="hidden" name="wp_quiz_list_type" class="wp-quiz-list-type-input" value="<?php echo esc_attr( $list_type ); ?>">

					<button type="button" data-type="" class="<?php echo empty( $list_type ) ? 'is-active' : ''; ?>" title="<?php esc_html_e( 'Default', 'wp-quiz-pro' ); ?>">/</button>
					<button type="button" data-type="number" class="<?php echo 'number' === $list_type ? 'is-active' : ''; ?>" title="<?php esc_html_e( 'Numbered List', 'wp-quiz-pro' ); ?>">1</button>
					<button type="button" data-type="bullet" class="<?php echo 'bullet' === $list_type ? 'is-active' : ''; ?>" title="<?php esc_html_e( 'Bulleted List', 'wp-quiz-pro' ); ?>">
						<span class="dashicons dashicons-editor-ul"></span>
					</button>
					<button type="button" data-type="interactive" class="<?php echo 'interactive' === $list_type ? 'is-active' : ''; ?>" title="<?php esc_html_e( 'Interactive List', 'wp-quiz-pro' ); ?>">
						<span class="dashicons dashicons-sort"></span>
					</button>
					<button type="button" class="wp-quiz-list-color-btn"><span class="dashicons dashicons-art"></span></button>

					<div class="wp-quiz-list-color-panel">
						<input type="text" class="wp-quiz-list-color" name="wp_quiz_list_color" value="<?php echo esc_attr( $list_color ); ?>">
					</div>
				</div>
			</div>

			<div class="wp-quiz-questions-list"></div><!-- End .wp-quiz-questions-list -->
			<?php $this->backend_add_question_btn(); ?>
		</div><!-- End .wp-quiz-questions -->
		<?php
	}

	/**
	 * Prints question js template for backend.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_question_js_template( Quiz $quiz ) {
		/**
		 * Template variables:
		 *
		 * @type Object question
		 * @type String baseName
		 * @type Number index
		 * @type Object i18n
		 */
		?>
		<input type="hidden" name="{{ baseName }}[showVotesUp]" class="wp-quiz-question-votes-up" value="{{ data.question.showVotesUp }}">
		<input type="hidden" name="{{ baseName }}[showVotesDown]" class="wp-quiz-question-votes-down" value="{{ data.question.showVotesDown }}">

		<div class="wp-quiz-question-heading">
			<div class="wp-quiz-question-heading-inner">

				<div class="wp-quiz-question-number {{ 'number' === data.listType ? 'is-active' : '' }}">{{ data.index + 1 }}</div>

				<div class="wp-quiz-question-bullet {{ 'bullet' === data.listType ? 'is-active' : '' }}"></div>

				<div class="wp-quiz-question-title-wrapper">
					<textarea class="widefat wp-quiz-question-title" name="{{ baseName }}[title]" placeholder="<?php esc_attr_e( 'Question title?', 'wp-quiz-pro' ); ?>" rows="1" data-gramm_editor="false" data-autoresize>{{ data.question.title }}</textarea>
				</div>

				<div class="wp-quiz-question-interactive-btns {{ 'interactive' === data.listType ? 'is-active' : '' }}">
					<button type="button" class="wp-quiz-question-votes-down-btn {{ parseInt(data.question.showVotesDown) ? 'is-enabled' : 'is-disabled' }}">
						<span class="dashicons dashicons-arrow-down"></span>
					</button>
					<button type="button" class="wp-quiz-question-votes-up-btn {{ parseInt(data.question.showVotesUp) ? 'is-enabled' : 'is-disabled' }}">
						<span class="dashicons dashicons-arrow-up"></span>
					</button>
				</div>

				<?php $this->backend_remove_question_btn(); ?>
			</div>

		</div><!-- End .wp-quiz-question-heading -->

		<div class="wp-quiz-question-content">

			<div class="wp-quiz-question-listquiz-container wp-quiz-listquiz-container">
				<div class="wp-quiz-question-image">

					<div class="wp-quiz-image-upload style-overlay {{ data.question.image ? '' : 'no-image' }}" data-edit-text="{{ data.i18n.editImage }}" data-upload-text="{{ data.i18n.uploadImage }}">
						<div class="wp-quiz-image-upload-preview">
							<# if (data.question.image) { #>
							<img src="{{ data.question.image }}" alt="">
							<# } #>
						</div><!-- End .wp-quiz-image-upload-preview -->

						<button type="button" class="wp-quiz-image-upload-btn">
							{{ data.question.image ? data.i18n.editImage : data.i18n.uploadImage }}
						</button>

						<?php $this->backend_remove_image_btn( __( 'Remove Image', 'wp-quiz-pro' ) ); ?>

						<input type="text" class="wp-quiz-image-upload-credit" name="{{ baseName }}[imageCredit]" placeholder="<?php esc_attr_e( 'Credit', 'wp-quiz-pro' ); ?>" value="{{ data.question.imageCredit }}">

						<input type="hidden" class="wp-quiz-image-upload-url" name="{{ baseName }}[image]" value="{{ data.question.image }}">
						<input type="hidden" class="wp-quiz-image-upload-id" name="{{ baseName }}[imageId]" value="{{ data.question.imageId }}">
					</div><!-- End .wp-quiz-image-upload -->

				</div><!-- End .wp-quiz-question-image -->

				<p>
					<label for="wp-quiz-question-{{ data.question.id }}-desc"><strong><?php esc_html_e( 'Text (Optional)', 'wp-quiz-pro' ); ?></strong></label>
					<textarea name="{{ baseName }}[desc]" id="wp-quiz-question-{{ data.question.id }}-desc" class="widefat" rows="3" style="width: 100%;">{{ data.question.desc }}</textarea>
				</p>

				<p class="wp-quiz-listquiz-colors">
					<span>
						<label for="wp-quiz-question-{{ data.question.id }}-bg-color"><strong><?php esc_html_e( 'Background color (Optional)', 'wp-quiz-pro' ); ?></strong></label>
						<input type="text" name="{{ baseName }}[bgColor]" id="wp-quiz-question-{{ data.question.id }}-bg-color" class="wq-color-picker" value="{{ data.question.bgColor }}">
					</span>

					<span>
						<label for="wp-quiz-question-{{ data.question.id }}-font-color"><strong><?php esc_html_e( 'Font color (Optional)', 'wp-quiz-pro' ); ?></strong></label>
						<input type="text" name="{{ baseName }}[fontColor]" id="wp-quiz-question-{{ data.question.id }}-font-color" class="wq-color-picker" value="{{ data.question.fontColor }}">
					</span>
				</p>
			</div><!-- End .wp-quiz-listquiz-container -->

		</div><!-- End .wp-quiz-question-content -->
		<?php
	}
}
