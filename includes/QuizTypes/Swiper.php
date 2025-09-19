<?php
/**
 * Swiper quiz type
 *
 * @package WPQuizPro
 */

namespace WPQuiz\QuizTypes;

use WP_Error;
use WPQuiz\PlayDataTracking\PlayData;
use WPQuiz\PostTypeQuiz;
use WPQuiz\Quiz;
use WPQuiz\QuizType;

/**
 * Class Swiper
 */
class Swiper extends QuizType {

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
	protected $icon = 'dashicons dashicons-tickets-alt';

	/**
	 * Swiper constructor.
	 */
	public function __construct() {
		$this->name = 'swiper';
		$this->desc = __( 'Swiper quizzes are fun to play, easy to create, and are great to capture and compare results from a group of people.', 'wp-quiz-pro' );
		add_action( 'wp_quiz_inserted_play_data', array( $this, 'update_voting' ), 10, 2 );
	}

	/**
	 * Gets default data for question.
	 *
	 * @return array
	 */
	public function get_default_question() {
		return array(
			'title'       => '',
			'votesUp'     => 0,
			'votesDown'   => 0,
			'image'       => '',
			'imageId'     => '',
			'imageCredit' => '',
		);
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
			<div class="wp-quiz-question-number">{{ data.index + 1 }}</div>
			<textarea class="widefat wp-quiz-question-title" name="{{ baseName }}[title]" placeholder="<?php esc_attr_e( 'Question title?', 'wp-quiz-pro' ); ?>" rows="1" data-autoresize data-gramm_editor="false">{{ data.question.title }}</textarea>

			<?php $this->backend_remove_question_btn(); ?>
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

			<div class="wp-quiz-question-votes">
				<div class="wp-quiz-question-votes-up">
					<label><?php esc_html_e( 'Votes Up', 'wp-quiz-pro' ); ?></label>
					<input type="text" value="{{ data.question.votesUp }}" readonly>
				</div>

				<div class="wp-quiz-question-votes-down">
					<label><?php esc_html_e( 'Votes Down', 'wp-quiz-pro' ); ?></label>
					<input type="text" value="{{ data.question.votesDown }}" readonly>
				</div>
			</div>

		</div><!-- End .wp-quiz-question-content -->
		<?php
	}

	/**
	 * Enqueues frontend scripts.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	public function enqueue_frontend_scripts( $quiz = null ) {
		parent::enqueue_frontend_scripts( $quiz );

		if ( ! $quiz && ! empty( $this->current_quiz ) ) {
			$quiz = $this->current_quiz;
		}

		wp_register_script( 'hammer', wp_quiz()->assets() . 'js/hammer.min.js', array(), '2.0.1', true );
		wp_register_script( 'wp-quiz-tinder', wp_quiz()->assets() . 'js/tinder.js', array( 'hammer' ), wp_quiz()->version, true );

		wp_enqueue_script( 'hammer' );
		wp_enqueue_script( 'wp-quiz-tinder' );
	}

	/**
	 * Gets results classes data.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return array
	 */
	protected function get_results_classes_data( Quiz $quiz ) {
		$classes   = parent::get_results_classes_data( $quiz );
		$classes[] = 'wq_IsSwiperResult';
		return $classes;
	}

	/**
	 * Gets result classes data.
	 *
	 * @param Quiz  $quiz   Quiz object.
	 * @param array $result Result data.
	 * @return array
	 */
	protected function get_result_classes_data( Quiz $quiz, array $result ) {
		$classes   = parent::get_result_classes_data( $quiz, $result );
		$classes[] = 'resultItem';
		return $classes;
	}

	/**
	 * Gets quiz results output.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return string
	 */
	public function quiz_results( Quiz $quiz ) {
		$questions = $this->get_processed_questions( $quiz );

		ob_start();
		$this->load_template( 'results/results.php', compact( 'questions', 'quiz' ) );
		$output = ob_get_clean();

		return apply_filters( 'wp_quiz_results', $output, $quiz );
	}

	/**
	 * Formats number.
	 *
	 * @param int $number Number.
	 * @return string
	 */
	public function format_number( $number ) {
		return $number >= 1000 ? round( $number / 1000, 1 ) . 'k' : $number;
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

		return array(
			'quiz_id'       => $quiz->get_id(),
			'quiz_type'     => $this->name,
			'quiz_data'     => $quiz_data,
			'answered_data' => $answered,
			'quiz_url'      => $player_data['current_url'],
		);
	}

	/**
	 * Updates voting.
	 *
	 * @param int   $play_data_id Play data ID.
	 * @param array $insert_data  Insert data.
	 */
	public function update_voting( $play_data_id, $insert_data ) {
		$quiz = PostTypeQuiz::get_quiz( $insert_data['quiz_id'] );
		if ( ! $quiz ) {
			return;
		}

		if ( $quiz->get_quiz_type()->get_name() !== $this->name ) {
			return;
		}

		$answered  = ! empty( $insert_data['answered_data'] ) ? json_decode( $insert_data['answered_data'], true ) : array();
		$questions = $quiz->get_questions();
		foreach ( $questions as $id => &$question ) {
			$question['votesUp']   = ! empty( $question['votesUp'] ) ? intval( $question['votesUp'] ) : 0;
			$question['votesDown'] = ! empty( $question['votesDown'] ) ? intval( $question['votesDown'] ) : 0;
			if ( empty( $answered[ $id ] ) || ! intval( $answered[ $id ] ) ) {
				$question['votesDown']++;
			} else {
				$question['votesUp']++;
			}
		}
		$quiz->update_questions( $questions );
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

		/**
		 * Played data
		 *
		 * Example: {
		 *     array(
		 *         question_id1 => true,
		 *         question_id2 => false,
		 *     )
		 * }
		 */
		$played_data = $play_data->answered_data;
		?>
		<div class="wp-quiz-tracking <?php echo esc_attr( $this->name ); ?>-tracking">
			<ul class="questions">
				<?php foreach ( $quiz_data['questions'] as $qid => $question ) : ?>
					<li class="question">
						<span class="number">#<?php echo intval( $question['index'] + 1 ); ?>.</span>
						<strong><?php echo esc_html( $question['title'] ); ?></strong>
						<?php if ( isset( $played_data[ $qid ] ) ) : ?>
							<?php if ( $played_data[ $qid ] ) : ?>
								<span class="like dashicons dashicons-thumbs-up"></span>
							<?php else : ?>
								<span class="dislike dashicons dashicons-thumbs-down"></span>
							<?php endif; ?>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Gets chart data.
	 *
	 * @param array $question   Question data.
	 * @param array $stats_data Stats data.
	 * @param Quiz  $quiz       Quiz object.
	 * @return array
	 */
	public function get_chart_data( array $question, array $stats_data, Quiz $quiz ) {
		$vote_up    = isset( $question['votesUp'] ) ? intval( $question['votesUp'] ) : 0;
		$vote_down  = isset( $question['votesDown'] ) ? intval( $question['votesDown'] ) : 0;
		$chart_data = array(
			array( __( 'Vote', 'wp-quiz-pro' ), __( 'count', 'wp-quiz-pro' ), array( 'role' => 'annotation' ), array( 'role' => 'style' ) ),
			array( __( 'Vote up', 'wp-quiz-pro' ), $vote_up, $vote_up, '#8bc34a' ),
			array( __( 'Vote down', 'wp-quiz-pro' ), $vote_down, $vote_down, '#ff9c7d' ),
		);

		return $chart_data;
	}
}
