<?php
/**
 * Quiz type backend trait
 *
 * @package WPQuiz
 */

namespace WPQuiz\Traits;

use CMB2;
use WPQuiz\Admin\AdminHelper;
use WPQuiz\PostTypeQuiz;
use WPQuiz\Quiz;

/**
 * Trait QuizTypeBackend
 */
trait QuizTypeBackend {

	/**
	 * Loads view for backend.
	 *
	 * @param string $view_file View file.
	 * @param array  $data      Data passed to view.
	 */
	protected function load_view( $view_file, array $data = array() ) {
		AdminHelper::load_view( "quiz-types/{$this->name}/{$view_file}", $data );
	}

	/**
	 * Loads backend tab options.
	 *
	 * @param string $tab_id Tab ID.
	 * @param CMB2   $cmb    CMB2 object.
	 */
	public function tab_options( $tab_id, CMB2 $cmb ) {
		$this->load_view(
			"tab-{$tab_id}.php",
			array(
				'cmb'       => $cmb,
				'quiz_type' => $this,
			)
		);
	}

	/**
	 * Enqueues backend scripts.
	 */
	public function enqueue_backend_scripts() {
		global $post;
		$quiz = PostTypeQuiz::get_quiz( $post );
		wp_enqueue_script( 'wp-quiz-admin-' . $this->name );
		wp_localize_script(
			'wp-quiz-admin-' . $this->name,
			$this->name . 'Quiz',
			array(
				'questions'       => $this->get_questions( $quiz ),
				'results'         => $this->get_results( $quiz ),
				'defaultQuestion' => $this->get_default_question(),
				'defaultAnswer'   => $this->get_default_answer(),
				'defaultResult'   => $this->get_default_result(),
				'settings'        => $this->get_settings( $quiz ),
			)
		);
	}

	/**
	 * Shows backend template.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	public function backend( Quiz $quiz ) {
		$classes = implode( ' ', $this->backend_quiz_classes() );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" data-type="<?php echo esc_attr( $this->name ); ?>">
			<?php $this->backend_content( $quiz ); ?>
		</div>
		<?php
	}

	/**
	 * Gets backend quiz classes.
	 *
	 * @return array
	 */
	protected function backend_quiz_classes() {
		return array(
			'wp-quiz-backend',
			"wp-quiz-{$this->name}-backend",
		);
	}

	/**
	 * Shows backend content.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_content( Quiz $quiz ) {
		$this->backend_custom_css( $quiz );
		$this->backend_questions_list( $quiz );
		if ( $this->has_results ) {
			$this->backend_results_list( $quiz );
		}
		$this->backend_js_templates( $quiz );
	}

	/**
	 * Prints custom css.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_custom_css( Quiz $quiz ) {}

	/**
	 * Prints questions list.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_questions_list( Quiz $quiz ) {
		?>
		<div class="wp-quiz-questions wp-quiz-<?php echo esc_attr( $this->name ); ?>-questions">
			<div class="wp-quiz-questions-list"></div><!-- End .wp-quiz-questions-list -->
			<?php $this->backend_add_question_btn(); ?>
		</div><!-- End .wp-quiz-questions -->
		<?php
	}

	/**
	 * Prints answers list.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_answers_list( Quiz $quiz ) {
		?>
		<div
			class="wp-quiz-answers wp-quiz-<?php echo esc_attr( $this->name ); ?>-answers"
			data-base-name="{{ baseName }}[answers]"
			data-type="{{ data.question.answerType }}"
		>
			<h3><?php esc_html_e( 'Answers', 'wp-quiz-pro' ); ?></h3>
			<div class="wp-quiz-answers-list"></div><!-- End .wp-quiz-answers-list -->
			<?php $this->backend_add_answer_btn(); ?>
		</div><!-- End .wp-quiz-questions -->
		<?php
	}

	/**
	 * Prints results list.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_results_list( Quiz $quiz ) {
		?>
		<div class="wp-quiz-results wp-quiz-<?php echo esc_attr( $this->name ); ?>-results">
			<h3><?php esc_html_e( 'Results', 'wp-quiz-pro' ); ?></h3>
			<div class="wp-quiz-results-list"></div><!-- End .wp-quiz-results-list -->
			<?php $this->backend_add_result_btn(); ?>
		</div><!-- End .wp-quiz-questions -->
		<?php
	}

	/**
	 * Prints js templates.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_js_templates( Quiz $quiz ) {
		?>
		<script type="text/html" id="tmpl-wp-quiz-<?php echo esc_attr( $this->name ); ?>-question-tpl">
			<div class="wp-quiz-question" data-id="{{ data.question.id }}">
				<# var baseName = data.baseName + '[' + data.question.id + ']'; #>
				<input type="hidden" name="{{ baseName }}[id]" value="{{ data.question.id }}">
				<?php $this->backend_question_js_template( $quiz ); ?>
			</div><!-- End .wp-quiz-question -->
		</script>

		<?php if ( $this->has_answers ) : ?>
			<script type="text/html" id="tmpl-wp-quiz-<?php echo esc_attr( $this->name ); ?>-answer-tpl">
				<div class="wp-quiz-answer" data-id="{{ data.answer.id }}">
					<# var baseName = data.baseName + '[' + data.answer.id + ']'; #>
					<input type="hidden" name="{{ baseName }}[id]" value="{{ data.answer.id }}">
					<?php $this->backend_answer_js_template( $quiz ); ?>
				</div><!-- End .wp-quiz-answer -->
			</script>
		<?php endif; ?>

		<?php if ( $this->has_results ) : ?>
			<script type="text/html" id="tmpl-wp-quiz-<?php echo esc_attr( $this->name ); ?>-result-tpl">
				<div class="wp-quiz-result" data-id="{{ data.result.id }}">
					<# var baseName = data.baseName + '[' + data.result.id + ']'; #>
					<input type="hidden" name="{{ baseName }}[id]" value="{{ data.result.id }}">
					<?php $this->backend_result_js_template( $quiz ); ?>
				</div><!-- End .wp-quiz-result -->
			</script>
			<?php
		endif;
	}

	/**
	 * Prints question js template for backend.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_question_js_template( Quiz $quiz ) {}

	/**
	 * Prints answer js template for backend.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_answer_js_template( Quiz $quiz ) {}

	/**
	 * Prints result js template for backend.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_result_js_template( Quiz $quiz ) {}

	/**
	 * Prints add question button.
	 *
	 * @param string $text Button text.
	 */
	protected function backend_add_question_btn( $text = '' ) {
		if ( ! $text ) {
			$text = __( '+ Add Question', 'wp-quiz-pro' );
		}
		printf(
			'<button type="button" class="button button-primary wp-quiz-add-question-btn">%s</button>',
			wp_kses_post( $text )
		);
	}

	/**
	 * Prints add answer button.
	 *
	 * @param string $text Button text.
	 */
	protected function backend_add_answer_btn( $text = '' ) {
		if ( ! $text ) {
			$text = __( '+ Add Answer', 'wp-quiz-pro' );
		}
		printf(
			'<button type="button" class="button wp-quiz-add-answer-btn">%s</button>',
			wp_kses_post( $text )
		);
	}

	/**
	 * Prints add result button.
	 *
	 * @param string $text Button text.
	 */
	protected function backend_add_result_btn( $text = '' ) {
		if ( ! $text ) {
			$text = __( '+ Add Result', 'wp-quiz-pro' );
		}
		printf(
			'<button type="button" class="button button-primary wp-quiz-add-result-btn">%s</button>',
			wp_kses_post( $text )
		);
	}

	/**
	 * Prints remove question button.
	 *
	 * @param string $text Button text.
	 */
	protected function backend_remove_question_btn( $text = '' ) {
		if ( ! $text ) {
			$text = '&times;';
		}
		printf(
			'<button type="button" title="%1$s" class="wp-quiz-remove-question-btn">%2$s</button>',
			esc_attr__( 'Remove question', 'wp-quiz-pro' ),
			wp_kses_post( $text )
		);
	}

	/**
	 * Prints remove answer button.
	 *
	 * @param string $text Button text.
	 */
	protected function backend_remove_answer_btn( $text = '' ) {
		if ( ! $text ) {
			$text = '&times;';
		}
		printf(
			'<button type="button" title="%1$s" class="wp-quiz-remove-answer-btn">%2$s</button>',
			esc_attr__( 'Remove answer', 'wp-quiz-pro' ),
			wp_kses_post( $text )
		);
	}

	/**
	 * Prints remove result button.
	 *
	 * @param string $text Button text.
	 */
	protected function backend_remove_result_btn( $text = '' ) {
		if ( ! $text ) {
			$text = '&times;';
		}
		printf(
			'<button type="button" title="%1$s" class="wp-quiz-remove-result-btn">%2$s</button>',
			esc_attr__( 'Remove result', 'wp-quiz-pro' ),
			wp_kses_post( $text )
		);
	}

	/**
	 * Prints remove image button.
	 *
	 * @param string $text Button text.
	 */
	protected function backend_remove_image_btn( $text = '' ) {
		if ( ! $text ) {
			$text = '&times;';
		}
		printf(
			'<button type="button" title="%1$s" class="wp-quiz-image-upload-remove-btn">%2$s</button>',
			esc_attr__( 'Remove image', 'wp-quiz-pro' ),
			wp_kses_post( $text )
		);
	}
}
