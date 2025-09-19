<?php
/**
 * Flip quiz type
 *
 * @package WPQuizPro
 */

namespace WPQuiz\QuizTypes;

use WPQuiz\Helper;
use WPQuiz\PostTypeQuiz;
use WPQuiz\Quiz;
use WPQuiz\QuizType;

/**
 * Class Flip
 */
class Flip extends QuizType {

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
		$this->name = 'flip';
		$this->desc = __( 'A multi-utility quiz type that can be used to used to create a variety of content, each of is equally engaging.', 'wp-quiz-pro' );

		add_action( 'save_post_' . PostTypeQuiz::get_name(), array( $this, 'save_custom_data' ) );
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
	 * Gets default data for question.
	 *
	 * @return array
	 */
	public function get_default_question() {
		return array(
			'title'           => '',
			'frontDesc'       => '',
			'image'           => '',
			'imageId'         => '',
			'imageCredit'     => '',
			'frontBgColor'    => '',
			'frontFontColor'  => '',
			'desc'            => '',
			'backImage'       => '',
			'backImageId'     => '',
			'backImageCredit' => '',
			'backBgColor'     => '',
			'backFontColor'   => '',
		);
	}

	/**
	 * Prints backend questions list.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_questions_list( Quiz $quiz ) {
		?>
		<div class="wp-quiz-questions wp-quiz-<?php echo esc_attr( $this->name ); ?>-questions">
			<div class="wp-quiz-questions-heading">
				<h3><?php esc_html_e( 'Questions', 'wp-quiz-pro' ); ?></h3>
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
		<div class="wp-quiz-question-heading">
			<div class="wp-quiz-question-heading-inner">

				<div class="wp-quiz-question-title-wrapper">
					<textarea class="widefat wp-quiz-question-title" name="{{ baseName }}[title]" placeholder="<?php esc_attr_e( 'Question title?', 'wp-quiz-pro' ); ?>" rows="1" data-gramm_editor="false" data-autoresize>{{ data.question.title }}</textarea>
				</div>

				<?php $this->backend_remove_question_btn(); ?>
			</div>

		</div><!-- End .wp-quiz-question-heading -->

		<div class="wp-quiz-question-content">

			<div class="wp-quiz-question-flip-btns">
				<button type="button" class="wp-quiz-question-flip-front-btn is-active"><?php esc_html_e( 'Front', 'wp-quiz-pro' ); ?></button>
				<button type="button" class="wp-quiz-question-flip-back-btn"><?php esc_html_e( 'Back', 'wp-quiz-pro' ); ?></button>
			</div>

			<div class="wp-quiz-question-flip-container wp-quiz-flip-container">
				<div class="wp-quiz-flipper">
					<div class="wp-quiz-flip-front-container">

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
							<label for="wp-quiz-question-{{ data.question.id }}-front-desc"><strong><?php esc_html_e( 'Front Text (Optional)', 'wp-quiz-pro' ); ?></strong></label>
							<textarea name="{{ baseName }}[frontDesc]" id="wp-quiz-question-{{ data.question.id }}-front-desc" class="widefat" rows="3" style="width: 100%;">{{ data.question.frontDesc }}</textarea>
						</p>

						<p class="wp-quiz-flip-colors">
							<span>
								<label for="wp-quiz-question-{{ data.question.id }}-front-bg-color"><strong><?php esc_html_e( 'Front background color (Optional)', 'wp-quiz-pro' ); ?></strong></label>
								<input type="text" name="{{ baseName }}[frontBgColor]" id="wp-quiz-question-{{ data.question.id }}-front-bg-color" class="wq-color-picker" value="{{ data.question.frontBgColor }}">
							</span>

							<span>
								<label for="wp-quiz-question-{{ data.question.id }}-front-font-color"><strong><?php esc_html_e( 'Front font color (Optional)', 'wp-quiz-pro' ); ?></strong></label>
								<input type="text" name="{{ baseName }}[frontFontColor]" id="wp-quiz-question-{{ data.question.id }}-front-font-color" class="wq-color-picker" value="{{ data.question.frontFontColor }}">
							</span>
						</p>

					</div><!-- End .wp-quiz-flip-front-container -->

					<div class="wp-quiz-flip-back-container">

						<div class="wp-quiz-question-image">

							<div class="wp-quiz-image-upload style-overlay {{ data.question.backImage ? '' : 'no-image' }}" data-edit-text="{{ data.i18n.editImage }}" data-upload-text="{{ data.i18n.uploadImage }}">
								<div class="wp-quiz-image-upload-preview">
									<# if (data.question.backImage) { #>
									<img src="{{ data.question.backImage }}" alt="">
									<# } #>
								</div><!-- End .wp-quiz-image-upload-preview -->

								<button type="button" class="wp-quiz-image-upload-btn">
									{{ data.question.backImage ? data.i18n.editImage : data.i18n.uploadImage }}
								</button>

								<?php $this->backend_remove_image_btn( __( 'Remove Image', 'wp-quiz-pro' ) ); ?>

								<input type="text" class="wp-quiz-image-upload-credit" name="{{ baseName }}[backImageCredit]" placeholder="<?php esc_attr_e( 'Credit', 'wp-quiz-pro' ); ?>" value="{{ data.question.backImageCredit }}">

								<input type="hidden" class="wp-quiz-image-upload-url" name="{{ baseName }}[backImage]" value="{{ data.question.backImage }}">
								<input type="hidden" class="wp-quiz-image-upload-id" name="{{ baseName }}[backImageId]" value="{{ data.question.backImageId }}">
							</div><!-- End .wp-quiz-image-upload -->

						</div><!-- End .wp-quiz-question-image-back -->

						<p>
							<label for="wp-quiz-question-back-desc"><strong><?php esc_html_e( 'Back Text (Optional)', 'wp-quiz-pro' ); ?></strong></label>
							<textarea name="{{ baseName }}[desc]" id="wp-quiz-question-back-desc" class="widefat" rows="3" style="width: 100%;i">{{ data.question.desc }}</textarea>
						</p>

						<p class="wp-quiz-flip-colors">
							<span>
								<label for="wp-quiz-question-{{ data.question.id }}-back-bg-color"><strong><?php esc_html_e( 'Back background color (Optional)', 'wp-quiz-pro' ); ?></strong></label>
								<input type="text" name="{{ baseName }}[backBgColor]" id="wp-quiz-question-{{ data.question.id }}-back-bg-color" class="wq-color-picker" value="{{ data.question.backBgColor }}">
							</span>

							<span>
								<label for="wp-quiz-question-{{ data.question.id }}-back-font-color"><strong><?php esc_html_e( 'Back font color (Optional)', 'wp-quiz-pro' ); ?></strong></label>
								<input type="text" name="{{ baseName }}[backFontColor]" id="wp-quiz-question-{{ data.question.id }}-back-font-color" class="wq-color-picker" value="{{ data.question.backFontColor }}">
							</span>
						</p>

					</div><!-- End .wp-quiz-flip-back-container -->
				</div>
			</div><!-- End .wp-quiz-flip-container -->

		</div><!-- End .wp-quiz-question-content -->
		<?php
	}

	/**
	 * Gets question classes data.
	 *
	 * @param Quiz  $quiz     Quiz object.
	 * @param array $question Question data.
	 * @return array
	 */
	protected function get_question_classes_data( Quiz $quiz, array $question ) {
		$classes = parent::get_question_classes_data( $quiz, $question );
		if ( ! $this->has_front_card( $question ) || ! $this->has_back_card( $question ) ) {
			$classes[] = 'no-flip';
		}
		return $classes;
	}

	/**
	 * Checks if a question has front card.
	 *
	 * @param array $question Question data.
	 * @return bool
	 */
	public function has_front_card( array $question ) {
		return ! empty( $question['image'] ) || ! empty( $question['frontDesc'] );
	}

	/**
	 * Checks if a question has back card.
	 *
	 * @param array $question Question data.
	 * @return bool
	 */
	public function has_back_card( array $question ) {
		return ! empty( $question['backImage'] ) || ! empty( $question['desc'] );
	}
}
