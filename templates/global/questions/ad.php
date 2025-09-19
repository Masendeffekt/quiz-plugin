<?php
/**
 * Template for question ad.
 *
 * @package WPQuiz
 *
 * @var string       $ad_code
 * @var array        $question
 * @var \WPQuiz\Quiz $quiz
 */

use WPQuiz\Helper;

$quiz_type = $quiz->get_quiz_type();
$ad_title  = $quiz->get_setting( 'ad_title' ) ? $quiz->get_setting( 'ad_title' ) : trim( Helper::get_option( 'ad_title' ) );
$el_class  = array(
	'wq-question',
	'wq_singleQuestionWrapper',
	'wq-is-' . $quiz_type->get_name(),
	'wq_isAd',
	'wq-is-ad',
);

$el_class = implode( ' ', $el_class );
?>
<div class="<?php echo esc_attr( $el_class ); ?>">
	<?php if ( $ad_title ) : ?>
		<p style="font-size: 12px; margin-bottom: 0;"><?php echo esc_html( $ad_title ); ?></p>
	<?php endif; ?>

	<?php echo $ad_code; // WPCS: xss ok. ?>

	<?php echo $quiz_type->next_question_button( $question, $quiz ); ?>
</div>
