<?php
/**
 * Trivia quiz
 *
 * @package WPQuiz
 */

namespace WPQuiz\QuizTypes;

use WP_Error;
use WPQuiz\MetaTags;
use WPQuiz\PlayDataTracking\PlayData;
use WPQuiz\PostTypeQuiz;
use WPQuiz\QuizType;
use WPQuiz\Quiz;
use WPQuiz\Helper;
use WPQuiz\Traits\SupportRefreshPage;

/**
 * Class Trivia
 */
class Trivia extends QuizType {

	use SupportRefreshPage;

	/**
	 * Trivia constructor.
	 */
	public function __construct() {
		$this->name = 'trivia';
		$this->desc = __( 'Create Trivia Quizzes to test your users\' knowledge about a subject and then surprise them with the results.', 'wp-quiz-pro' );
	}

	/**
	 * Gets default settings.
	 *
	 * @return array
	 */
	public function get_default_settings() {
		return array(
			'question_layout'  => 'single',
			'skin'             => 'flat',
			'bar_color'        => '#00c479',
			'font_color'       => '#444',
			'background_color' => '#ecf0f1',
			'animation_in'     => 'fadeIn',
			'animation_out'    => 'fadeOut',
			'show_ads'         => false,
			'repeat_ads'       => false,
			'ad_nth_display'   => 0,
			'refresh_step'     => $this->get_default_refresh_step(),
		);
	}

	/**
	 * Gets default data for question.
	 *
	 * @return array
	 */
	public function get_default_question() {
		return array(
			'id'                 => '',
			'title'              => '',
			'desc'               => '',
			'hint'               => '',
			'mediaType'          => 'image',
			'image'              => '',
			'imageId'            => '',
			'video'              => '',
			'imagePlaceholder'   => '',
			'imagePlaceholderId' => '',
			'imageCredit'        => '',
			'answerType'         => 'text',
			'answers'            => array(),
		);
	}

	/**
	 * Gets default data for answer.
	 *
	 * @return array
	 */
	public function get_default_answer() {
		return array(
			'id'        => '',
			'title'     => '',
			'image'     => '',
			'imageId'   => '',
			'isCorrect' => '',
		);
	}

	/**
	 * Gets default data for result.
	 *
	 * @return array
	 */
	public function get_default_result() {
		return array(
			'id'           => '',
			'title'        => '',
			'image'        => '',
			'imageId'      => '',
			'min'          => 0,
			'max'          => 1,
			'desc'         => '',
			'redirect_url' => '',
		);
	}

	/**
	 * Gets processed questions.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return array
	 */
	protected function get_processed_questions( Quiz $quiz ) {
		$questions = parent::get_processed_questions( $quiz );
		if ( ! empty( $_COOKIE[ 'wp_quiz_progress_' . $quiz->get_id() ] ) ) {
			$progress = json_decode( wp_unslash( $_COOKIE[ 'wp_quiz_progress_' . $quiz->get_id() ] ), true );
			if ( ! empty( $progress['questions'] ) ) {
				$questions = $progress['questions'];
			} elseif ( ! empty( $progress['questionIds'] ) ) {
				$new_questions = array();
				foreach ( $progress['questionIds'] as $index => $qid ) {
					$new_questions[ $qid ]          = $questions[ $qid ];
					$new_questions[ $qid ]['index'] = $index;
				}
				return $new_questions;
			}
		}
		return $questions;
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
		<input type="hidden" class="wp-quiz-question-media-type" name="{{ baseName }}[mediaType]" value="{{ data.question.mediaType }}">
		<input type="hidden" class="wp-quiz-question-answer-type" name="{{ baseName }}[answerType]" value="{{ data.question.answerType }}">

		<div class="wp-quiz-question-heading">
			<div class="wp-quiz-question-number">{{ data.index + 1 }}</div>
			<div class="wp-quiz-question-types">
				<button type="button" title="<?php esc_attr_e( 'Image question', 'wp-quiz-pro' ); ?>" class="wp-quiz-set-question-type-btn" data-type="image">
					<span class="dashicons dashicons-format-image"></span>
				</button>

				<button type="button" title="<?php esc_html_e( 'Video question', 'wp-quiz-pro' ); ?>" class="wp-quiz-set-question-type-btn" data-type="video">
					<span class="dashicons dashicons-format-video"></span>
				</button>

				<?php $this->backend_remove_question_btn(); ?>
			</div>
		</div><!-- End .wp-quiz-question-heading -->

		<div class="wp-quiz-question-content">
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

					<?php $this->backend_remove_image_btn( __( 'Remove', 'wp-quiz-pro' ) ); ?>

					<input type="text" class="wp-quiz-image-upload-credit" name="{{ baseName }}[imageCredit]" placeholder="<?php esc_attr_e( 'Credit', 'wp-quiz-pro' ); ?>" value="{{ data.question.imageCredit }}">

					<input type="hidden" class="wp-quiz-image-upload-url" name="{{ baseName }}[image]" value="{{ data.question.image }}">
					<input type="hidden" class="wp-quiz-image-upload-id" name="{{ baseName }}[imageId]" value="{{ data.question.imageId }}">
				</div><!-- End .wp-quiz-image-upload -->

			</div><!-- End .wp-quiz-question-image -->

			<div class="wp-quiz-question-video">
				<div class="wp-quiz-video-upload {{ data.question.video ? '' : 'no-video' }}">
					<div class="wp-quiz-video-upload-error"></div>
					<div class="wp-quiz-video-upload-preview"></div>

					<div class="wp-quiz-video-upload-url-wrapper">
						<input type="url" class="wp-quiz-video-upload-url" placeholder="<?php esc_attr_e( 'Video URL', 'wp-quiz-pro' ); ?>" name="{{ baseName }}[video]" value="{{ data.question.video }}">
						<button type="button" class="button button-large wp-quiz-upload-video-btn"><?php esc_html_e( 'Upload video', 'wp-quiz-pro' ); ?></button>
						<!-- <button type="button" class="button button-large wp-quiz-load-video-preview-btn"><?php esc_html_e( 'Preview', 'wp-quiz-pro' ); ?></button> -->
					</div>

					<div class="wp-quiz-image-upload">
						<div class="wp-quiz-image-upload-url-wrapper">
							<input type="url" class="wp-quiz-image-upload-url" name="{{ baseName }}[imagePlaceholder]" value="{{ data.question.imagePlaceholder }}" placeholder="<?php esc_attr_e( 'Video image placeholder', 'wp-quiz-pro' ); ?>">
							<button type="button" class="button button-large wp-quiz-image-upload-btn"><?php esc_html_e( 'Upload image', 'wp-quiz-pro' ); ?></button>
						</div>
						<input type="hidden" class="wp-quiz-image-upload-id" name="{{ baseName }}[imagePlaceholderId]" value="{{ data.question.imagePlaceholderId }}">
					</div><!-- End .wp-quiz-image-upload -->

				</div><!-- End .question-video -->
			</div><!-- End .wp-quiz-question-video -->

			<p>
				<textarea class="widefat wp-quiz-question-title" name="{{ baseName }}[title]" placeholder="<?php esc_attr_e( 'Question text?', 'wp-quiz-pro' ); ?>" rows="1" data-autoresize>{{ data.question.title }}</textarea>
			</p>

			<p>
				<textarea class="widefat wp-quiz-question-desc" rows="4" name="{{ baseName }}[desc]" placeholder="<?php esc_attr_e( 'Answer Explanation', 'wp-quiz-pro' ); ?>">{{ data.question.desc }}</textarea>
			</p>

			<p>
				<label for="wp-quiz-question-hint-{{ data.question.id }}"><?php esc_html_e( 'Question hint (optional)', 'wp-quiz-pro' ); ?></label>
				<textarea id="wp-quiz-question-hint-{{ data.question.id }}" class="widefat wp-quiz-question-hint" rows="4" name="{{ baseName }}[hint]" style="width: 100%;">{{ data.question.hint }}</textarea>
			</p>

			<?php $this->backend_answers_list( $quiz ); ?>
		</div><!-- End .wp-quiz-question-content -->
		<?php
	}

	/**
	 * Prints backend answers list template for backend.
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
			<div class="wp-quiz-answers-heading">
				<h3><?php esc_html_e( 'Answers', 'wp-quiz-pro' ); ?></h3>

				<div class="wp-quiz-answer-type-btns">
					<button type="button" title="<?php esc_attr_e( 'Text answers', 'wp-quiz-pro' ); ?>" class="wp-quiz-set-answer-type-btn" data-type="text">
						<span class="dashicons dashicons-format-aside"></span>
					</button>
					<button type="button" title="<?php esc_attr_e( 'Image answers', 'wp-quiz-pro' ); ?>" class="wp-quiz-set-answer-type-btn" data-type="image">
						<span class="dashicons dashicons-format-image"></span>
					</button>
				</div>
			</div>

			<div class="wp-quiz-answers-list"></div><!-- End .wp-quiz-answers-list -->
			<?php $this->backend_add_answer_btn(); ?>
		</div><!-- End .wp-quiz-questions -->
		<?php
	}

	/**
	 * Prints answer js template for backend.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_answer_js_template( Quiz $quiz ) {
		?>
		<div class="wp-quiz-answer-image wp-quiz-image-upload style-overlay {{ data.answer.image ? '' : 'no-image' }}" data-edit-text="{{ data.i18n.editImage }}" data-upload-text="{{ data.i18n.uploadImage }}">
			<div class="wp-quiz-image-upload-preview">
				<# if (data.answer.image) { #>
				<img src="{{ data.answer.image }}" alt="">
				<# } #>
			</div><!-- End .wp-quiz-image-upload-preview -->

			<button type="button" class="wp-quiz-image-upload-btn">
				{{ data.answer.image ? data.i18n.editImage : data.i18n.uploadImage }}
			</button>

			<?php $this->backend_remove_image_btn( __( 'Remove', 'wp-quiz-pro' ) ); ?>

			<input type="hidden" class="wp-quiz-image-upload-url" name="{{ baseName }}[image]" value="{{ data.answer.image }}">
			<input type="hidden" class="wp-quiz-image-upload-id" name="{{ baseName }}[imageId]" value="{{ data.answer.imageId }}">
		</div><!-- End .wp-quiz-image-upload -->

		<p>
			<textarea class="widefat wp-quiz-answer-title" name="{{ baseName }}[title]" placeholder="<?php esc_attr_e( 'Answer text', 'wp-quiz-pro' ); ?>" rows="1" data-autoresize>{{ data.answer.title }}</textarea>
		</p>

		<p>
			<label><input type="checkbox" class="wp-quiz-answer-correct-checkbox" name="{{ baseName }}[isCorrect]" value="1" <# if (parseInt(data.answer.isCorrect)) { #>checked<# } #>> <?php esc_html_e( 'Correct answer', 'wp-quiz-pro' ); ?></label>
		</p>

		<?php $this->backend_remove_answer_btn(); ?>
		<?php
	}

	/**
	 * Prints result js template for backend.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_result_js_template( Quiz $quiz ) {
		?>
		<p>
			<textarea class="widefat wp-quiz-result-title" name="{{ baseName }}[title]" placeholder="<?php esc_attr_e( 'Result title', 'wp-quiz-pro' ); ?>" rows="1" data-autoresize>{{ data.result.title }}</textarea>
		</p>

		<div class="wp-quiz-result-image wp-quiz-image-upload style-overlay {{ data.result.image ? '' : 'no-image' }}" data-edit-text="{{ data.i18n.editImage }}" data-upload-text="{{ data.i18n.uploadImage }}">
			<div class="wp-quiz-image-upload-preview">
				<# if (data.result.image) { #>
				<img src="{{ data.result.image }}" alt="">
				<# } #>
			</div><!-- End .wp-quiz-image-upload-preview -->

			<button type="button" class="wp-quiz-image-upload-btn">
				{{ data.result.image ? data.i18n.editImage : data.i18n.uploadImage }}
			</button>

			<?php $this->backend_remove_image_btn( __( 'Remove', 'wp-quiz-pro' ) ); ?>

			<input type="hidden" class="wp-quiz-image-upload-url" name="{{ baseName }}[image]" value="{{ data.result.image }}">
			<input type="hidden" class="wp-quiz-image-upload-id" name="{{ baseName }}[imageId]" value="{{ data.result.imageId }}">
		</div><!-- End .wp-quiz-image-upload -->

		<div class="wp-quiz-result-range">
			<label>
				<span><?php esc_html_e( 'Min', 'wp-quiz-pro' ); ?></span>
				<input type="number" min="0" step="1" class="small-text" name="{{ baseName }}[min]" value="{{ data.result.min }}">
			</label>
			<label>
				<span><?php esc_html_e( 'Max', 'wp-quiz-pro' ); ?></span>
				<input type="number" min="0" step="1" class="small-text" name="{{ baseName }}[max]" value="{{ data.result.max }}">
			</label>
		</div><!-- End .wp-quiz-result-range -->

		<p>
			<textarea class="widefat wp-quiz-result-desc" name="{{ baseName }}[desc]" placeholder="<?php esc_attr_e( 'Description', 'wp-quiz-pro' ); ?>">{{ data.result.desc }}</textarea>
		</p>

		<p>
			<input type="url" class="widefat wp-quiz-result-redirect-url" name="{{ baseName }}[redirect_url]" placeholder="<?php esc_attr_e( 'Redirect URL(optional)', 'wp-quiz-pro' ); ?>" value="{{ data.result.redirect_url }}">
		</p>

		<?php $this->backend_remove_result_btn(); ?>
		<?php
	}

	/**
	 * Enqueues frontend scripts.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	public function enqueue_frontend_scripts( $quiz = null ) {
		if ( ! is_object( $quiz ) && ! empty( $this->current_quiz ) ) {
			$quiz = $this->current_quiz;
		}

		$result_popup = $quiz->get_setting( 'result_popup' ) ? 'yes' === $quiz->get_setting( 'result_popup' ) : 'on' === Helper::get_option( 'result_popup' );
		if ( $result_popup ) {
			wp_enqueue_script( 'magnific-popup' );
		}

		if ( intval( $quiz->get_setting( 'refresh_step' ) ) && 'multiple' === $this->get_question_layout( $quiz ) ) {
			wp_enqueue_script( 'js-cookie' );
		}

		if ( '1' === $quiz->get_setting( 'force_action' ) ) {
			wp_enqueue_script( 'js-cookie' );
		}

		parent::enqueue_frontend_scripts( $quiz );
	}

	/**
	 * Gets quiz results output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function quiz_results( Quiz $quiz ) {
		$output = parent::quiz_results( $quiz );

		if ( 1 !== intval( $quiz->get_setting( 'force_action' ) ) ) {
			return $output;
		}
		if ( 'send' !== $quiz->get_setting( 'result_method' ) && ( $quiz->get_setting( 'result_method' ) || 'send' !== Helper::get_option( 'result_method' ) ) ) {
			return $output;
		}

		return '<div class="wq-results wq_resultsCtr">' . esc_html__( 'Quiz result is sent to your email address. Please check inbox.', 'wp-quiz-pro' ) . '</div>';
	}

	/**
	 * Shows player tracking detail.
	 *
	 * @param PlayData $play_data Tracking data.
	 * @param bool     $no_result Not show the result.
	 */
	public function show_tracking_data( PlayData $play_data, $no_result = false ) {
		if ( empty( $play_data->quiz_data ) ) {
			printf( '<p>%s</p>', esc_html__( 'This feature only works with new entry since version 2.0', 'wp-quiz-pro' ) );
			return;
		}
		$quiz_data = $play_data->quiz_data;
		$result    = ! empty( $quiz_data['results'][ $play_data->result ] ) ? $quiz_data['results'][ $play_data->result ] : array();

		/**
		 * Played data
		 *
		 * Example: {
		 *     array(
		 *         'question_id' => array(
		 *             'answers' => array( 'answer_id1', 'answer_id2' ),
		 *             'time'    => 4.3,
		 *         ),
		 *     )
		 * }
		 */
		$played_data = $play_data->answered_data;
		?>
		<div class="wp-quiz-tracking <?php echo esc_attr( $this->name ); ?>-tracking">
			<ul class="questions">
				<?php
				foreach ( $quiz_data['questions'] as $qid => $question ) :
					$answer_type = ! empty( $question['answerType'] ) ? $question['answerType'] : 'text';
					$answered    = ! empty( $played_data[ $qid ]['answers'] ) ? $played_data[ $qid ]['answers'] : array();
					?>
					<li class="question">
						<div class="question-heading">
							<h4>
								<span class="number"><?php echo intval( $question['index'] + 1 ); ?>.</span>
								<?php echo esc_html( $question['title'] ); ?>
							</h4>
						</div>

						<div class="answers <?php echo esc_attr( $answer_type ); ?>-answers">

							<?php if ( ! $answered ) : ?>
								<p><?php esc_html_e( 'No answer', 'wp-quiz-pro' ); ?></p>
							<?php else : ?>
								<?php
								foreach ( $answered as $aid ) :
									if ( empty( $question['answers'][ $aid ] ) ) {
										// Answer doesn't exist.
										continue;
									}
									$answer  = $question['answers'][ $aid ];
									$correct = ! empty( $answer['isCorrect'] ) && '0' !== $answer['isCorrect'] ? 'correct' : 'incorrect';
									?>
									<div class="answer <?php echo esc_attr( $correct ); ?>">
										<?php if ( 'image' === $answer_type ) : ?>

											<?php if ( ! empty( $answer['imageId'] ) ) : ?>
												<?php echo wp_get_attachment_image( $answer['imageId'], 'full' ); ?>
											<?php elseif ( ! empty( $answer['image'] ) ) : ?>
												<img src="<?php echo esc_url( $answer['image'] ); ?>" alt="">
											<?php endif; ?>

										<?php endif; ?>

										<span><?php echo esc_html( $answer['title'] ); ?></span>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>

						</div>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( ! empty( $result ) && ! $no_result ) : ?>
				<div class="result">
					<h3><?php esc_html_e( 'Result:', 'wp-quiz-pro' ); ?></h3>

					<p>
						<?php
						// translators: %1$s: number of correct answers, %2$s: total questions.
						printf( esc_html__( 'Got %1$s out of %2$s answers', 'wp-quiz-pro' ), $play_data->correct_answered, count( $quiz_data['questions'] ) );
						?>
					</p>

					<?php if ( ! empty( $result['title'] ) ) : ?>
						<p><?php echo esc_html( $result['title'] ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $result['image'] ) ) : ?>
						<img src="<?php echo esc_url( $result['image'] ); ?>">
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Adds extra data for question.
	 *
	 * @param array $question Question data.
	 */
	public function add_question_extra_data( array &$question ) {
		$question['totalCorrects'] = $this->get_total_corrects( $question );

		// Check answer type for old version.
		if ( empty( $question['answerType'] ) ) {
			$answer_type = 'text';
			foreach ( $question['answers'] as $answer ) {
				if ( ! empty( $answer['image'] ) ) {
					$answer_type = 'image';
					break;
				}
			}
			$question['answerType'] = $answer_type;
		}
	}

	/**
	 * Gets number of correct answers.
	 *
	 * @param array $question Question data.
	 * @return int
	 */
	protected function get_total_corrects( array $question ) {
		$corrects_count = 0;
		if ( empty( $question['answers'] ) || ! is_array( $question['answers'] ) ) {
			return $corrects_count;
		}
		foreach ( $question['answers'] as $answer ) {
			if ( ! empty( $answer['isCorrect'] ) ) {
				$corrects_count++;
			}
		}
		return $corrects_count;
	}

	/**
	 * Gets js data.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return array
	 */
	protected function get_js_data( Quiz $quiz ) {
		$data = parent::get_js_data( $quiz );
		if ( is_singular( PostTypeQuiz::get_name() ) && ! empty( $_GET['wqtid'] ) ) { // WPCS: csrf ok.
			$player = Helper::get_player( $_GET['wqtid'] ); // WPCS: csrf, sanitization ok.
			if ( $player ) {
				if ( $player['answer_data'] ) {
					$player['answer_data'] = json_decode( $player['answer_data'] );
				}
				if ( $player['result_data'] ) {
					$player['result_data'] = json_decode( $player['result_data'] );
				}
				$data['answered'] = $player;
			}
		}

		$data['is_continued'] = $this->is_continued();

		return $data;
	}

	/**
	 * Gets play data to insert into DB.
	 *
	 * @param Quiz  $quiz           Quiz object.
	 * @param array $player_data    Player data get from REST request.
	 * @return array|WP_Error|false Return an array with keys corresponding to plays table columns.
	 *                              Return `false` if do not want to track player data on this quiz type.
	 */
	public function get_inserting_play_data( Quiz $quiz, array $player_data ) {
		$quiz_data = isset( $player_data['quiz_data'] ) ? $player_data['quiz_data'] : json_decode( $quiz->get_post()->post_content, true );
		$answered  = isset( $player_data['answered'] ) ? $player_data['answered'] : array();
		$corrects  = isset( $player_data['corrects'] ) ? $player_data['corrects'] : 0;
		$result_id = isset( $player_data['result_id'] ) ? $player_data['result_id'] : '';

		return array(
			'quiz_id'          => $quiz->get_id(),
			'correct_answered' => $corrects,
			'result'           => $result_id,
			'quiz_type'        => $this->name,
			'quiz_data'        => $quiz_data,
			'answered_data'    => $answered,
			'quiz_url'         => $player_data['current_url'],
		);
	}

	/**
	 * Prints content in <head> section.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	public function wp_head( Quiz $quiz ) {
		if ( empty( $_GET['wqtid'] ) ) {
			parent::wp_head( $quiz );
			return;
		}
		$tracking_id = intval( $_GET['wqtid'] );
		$play_data   = PlayData::get( $tracking_id );
		if ( ! $play_data ) {
			return;
		}

		$quiz_data = $play_data->quiz_data;
		$result_id = $play_data->result;

		if ( empty( $quiz_data['results'][ $result_id ] ) ) {
			return;
		}

		$result = $quiz_data['results'][ $result_id ];

		/*
		 * Prints tags for displaying meta data on social network.
		 */
		MetaTags::url( $result, $play_data );

		if ( ! empty( $result['title'] ) ) {
			MetaTags::title( $result, $play_data );
		}

		if ( ! empty( $result['desc'] ) ) {
			MetaTags::description( $result, $play_data );
		}

		if ( ! empty( $result['image'] ) ) {
			MetaTags::image( $result, $play_data );
		}
	}

	/**
	 * Gets quiz result email output.
	 *
	 * @param Quiz     $quiz      Quiz object.
	 * @param PlayData $play_data Play data.
	 * @return string
	 */
	public function quiz_result_email( Quiz $quiz, PlayData $play_data ) {
		$result_id = $play_data->result;
		if ( empty( $play_data->quiz_data['results'][ $result_id ] ) ) {
			return '';
		}
		$result = $play_data->quiz_data['results'][ $result_id ];
		ob_start();
		$this->load_template( 'email/quiz-result.php', compact( 'quiz', 'result', 'play_data' ) );
		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Gets overall timer.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return int
	 */
	public function get_overall_timer( Quiz $quiz ) {
		if ( intval( $quiz->get_setting( 'refresh_step' ) ) ) {
			return 0;
		}
		if ( ! empty( $quiz->displayed_question ) ) {
			return 0;
		}
		return intval( $quiz->get_setting( 'overall_time' ) );
	}

	/**
	 * Gets question timer.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return int
	 */
	public function get_question_timer( Quiz $quiz ) {
		if ( ! empty( $quiz->displayed_question ) || 'multiple' !== $this->get_question_layout( $quiz ) ) {
			return 0;
		}
		return intval( $quiz->get_setting( 'countdown_timer' ) );
	}

	/**
	 * Gets question layout.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function get_question_layout( Quiz $quiz ) {
		if ( ! empty( $quiz->displayed_question ) ) {
			return 'single';
		}
		return parent::get_question_layout( $quiz );
	}
}
