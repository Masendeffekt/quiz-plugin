<?php
/**
 * Template for swiper question
 *
 * @package WPQuiz
 *
 * @var array $questions
 * @var Quiz  $quiz
 */

use WPQuiz\Quiz;
use WPQuiz\Helper;

$questions     = array_values( $questions );
$quiz_type     = $quiz->get_quiz_type();
$css           = '';
$auto_height   = 1;
$custom_width  = $quiz->get_setting( 'custom_width' );
$custom_height = $quiz->get_setting( 'custom_height' );
if ( 'custom' === $quiz->get_setting( 'size' ) ) {
	if ( $custom_width ) {
		$width = $custom_width + 12;
		$css  .= "max-width: {$width}px;";
	}
	if ( $custom_height ) {
		$height      = $custom_height + 127;
		$css        .= "height: {$height}px;";
		$auto_height = 0;
	}
}
?>
<div class="<?php echo esc_attr( $quiz_type->get_questions_classes( $quiz ) ); ?>">

	<div class="wq_swiperQuizPreviewInfoCtr">
		<p><?php esc_html_e( 'This is a swiper quiz, swipe right for yes, swipe left for no.', 'wp-quiz-pro' ); ?></p>
		<button type="button" class="wq_beginQuizSwiperCtr"><?php esc_html_e( 'Begin!', 'wp-quiz-pro' ); ?></button>
	</div>

	<div class="wq_QuestionWrapperSwiper">
		<div class="wq-swiper" data-auto-height="<?php echo intval( $auto_height ); ?>" data-direction="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">

			<div class="wq-swiper-items" style="<?php echo esc_attr( $css ); ?>">
				<?php
				$count = count( $questions );
				foreach ( $questions as $index => $question ) {
					$question          = wp_parse_args( $question, $quiz_type->get_default_question() );
					$question['index'] = $index;
					$question['count'] = $count;
					$index_count       = ( $index + 1 ) . '/' . $count;
					$quiz_type->add_question_extra_data( $question );

					/**
					 * Fires before rendering question.
					 *
					 * @param array $question Question data.
					 * @param Quiz  $quiz     Quiz object.
					 */
					do_action( 'wp_quiz_before_question', $question, $quiz );

					$quiz_type->load_template( 'questions/question.php', compact( 'question', 'quiz' ) );

					/**
					 * Fires after rendering question.
					 *
					 * @param array $question Question data.
					 * @param Quiz  $quiz     Quiz object.
					 */
					do_action( 'wp_quiz_after_question', $question, $quiz );
				}
				?>

			</div>

			<div class="wq-swiper-actions">
				<button class="wq-swiper-dislike-btn" type="button"><i class="sprite sprite-thumbs-down"></i></button>
				<button class="wq-swiper-like-btn" type="button"><i class="sprite sprite-thumbs-up"></i></button>
			</div>

		</div>
	</div>

</div>
