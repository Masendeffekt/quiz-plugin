<?php
/**
 * Template for trivia answers image
 *
 * @package WPQuiz
 *
 * @var array        $answers
 * @var array        $question
 * @var \WPQuiz\Quiz $quiz
 */

$quiz_type = $quiz->get_quiz_type();
$cols      = apply_filters( 'wp_quiz_pro_img_answer_cols', 3 ); // 2 columns if anything else is passed
if ( 3 === $cols ) {
	$col_class = 'col-md-wq-4';
} else {
	$col_class = 'col-md-wq-6';
	$cols      = 2;
}
?>
<div class="<?php echo esc_attr( $quiz_type->get_answers_classes( $quiz, $question ) ); ?>">
	<div class="row">
		<?php
		$i = 0;
		foreach ( $answers as $id => $answer ) :
			$answer = wp_parse_args(
				$answer,
				array(
					'title'     => '',
					'isCorrect' => 0,
					'imageId'   => '',
					'image'     => '',
				)
			);
			?>

			<div class="<?php echo esc_attr( $col_class ); ?>">
				<div class="<?php echo esc_attr( $quiz_type->get_answer_classes( $quiz, $question, $answer ) ); ?>" data-id="<?php echo esc_attr( $id ); ?>">

					<?php if ( ! empty( $answer['imageId'] ) ) : ?>
						<div class="wq_answerImgCtr">
							<?php echo wp_get_attachment_image( $answer['imageId'], 'full' ); ?>
						</div>
					<?php elseif ( ! empty( $answer['image'] ) ) : ?>
						<div class="wq_answerImgCtr">
							<img src="<?php echo esc_url( $answer['image'] ); ?>">
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $answer['title'] ) ) : ?>
						<label class="wq_answerTxtCtr"><?php echo wp_kses_post( $answer['title'] ); ?></label>
					<?php endif; ?>
				</div>
			</div>

			<?php
			$i++;
			if ( 0 === $i % $cols ) {
				echo '</div><!-- End .row --><div class="row">';
			}
			?>

		<?php endforeach; ?>
	</div>
</div>
