<?php
/**
 * Swiper results template
 *
 * @package WPQuiz
 *
 * @var \WPQuiz\Quiz $quiz
 * @var array $questions
 */

$quiz_type = $quiz->get_quiz_type();
?>
<div class="<?php echo esc_attr( $quiz_type->get_results_classes( $quiz ) ); ?>">
	<div>
		<h3><?php esc_html_e( 'Results', 'wp-quiz-pro' ); ?></h3>
		<div class="wq-results-list resultList">
			<?php foreach ( $questions as $question ) : ?>
				<?php $quiz_type->load_template( 'results/result.php', compact( 'quiz', 'question' ) ); ?>
			<?php endforeach; ?>
		</div>

		<?php if ( 'on' === $quiz->get_setting( 'restart_questions' ) ) : ?>
			<div class="wq_retakeSwiperWrapper">
				<button type="button" class="wq_retakeSwiperBtn">
					<i class="wq-icon wq-icon-undo"></i>
					<?php esc_html_e( 'Play Again!', 'wp-quiz-pro' ); ?>
				</button>
			</div>
		<?php endif; ?>
	</div>

	<?php echo $quiz_type->sharing( $quiz ); // WPCS: xss ok. ?>
</div>
