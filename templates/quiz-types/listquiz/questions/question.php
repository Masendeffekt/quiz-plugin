<?php
/**
 * Template for list question
 *
 * @package WPQuiz
 *
 * @var array $question
 * @var Quiz  $quiz
 */

use WPQuiz\Helper;
use WPQuiz\Quiz;

$quiz_type  = $quiz->get_quiz_type();
$list_type  = $quiz->get_setting( 'list_type' );
$list_color = $quiz->get_setting( 'list_color' );
$disabled   = Helper::current_user_can_vote( "vote_question_up_{$quiz->get_id()}_{$question['id']}" ) && Helper::current_user_can_vote( "vote_question_down_{$quiz->get_id()}_{$question['id']}" ) ? '' : 'disabled';
?>
<div class="<?php echo esc_attr( $quiz_type->get_question_classes( $quiz, $question ) ); ?>" data-id="<?php echo esc_attr( $question['id'] ); ?>" style="--wq-question-color: <?php echo esc_attr( $list_color ); ?>">

	<?php
	/**
	 * Fires when begin printing question content.
	 *
	 * @param array $question Question data.
	 * @param Quiz  $quiz     Quiz object.
	 */
	do_action( 'wp_quiz_begin_question', $question, $quiz );
	?>

	<div class="item_top">
		<div class="title_container">
			<div class="wq_questionTextCtr wq-question-list-type-<?php echo esc_attr( $list_type ); ?>">

				<?php if ( 'number' === $list_type ) : ?>
					<div class="wq-question-number"><?php echo intval( $question['index'] ) + 1; ?></div>
				<?php elseif ( 'bullet' === $list_type ) : ?>
					<div class="wq-question-bullet"></div>
				<?php endif; ?>

				<h4 class="wq-question-title"><?php echo wp_kses_post( $question['title'] ); ?></h4>

				<?php if ( 'interactive' === $list_type ) : ?>
					<div class="wq-question-votes">

						<?php
						if ( $question['showVotesDown'] ) :
							$is_voted = Helper::current_user_can_vote( "vote_question_down_{$quiz->get_id()}_{$question['id']}" ) ? '' : 'is-voted';
							?>
							<button type="button" class="wq-question-vote-btn wq-question-vote-down-btn <?php echo esc_attr( $is_voted ); ?>" <?php echo $disabled; ?>>
								<span class="icon"></span>
								<span class="number"><?php echo $question['votesDown'] ? intval( $question['votesDown'] ) : ''; ?></span>
								<span class="text"><?php $question['votesDown'] > 1 ? esc_html_e( 'Votes', 'wp-quiz-pro' ) : esc_html_e( 'Vote', 'wp-quiz-pro' ); ?></span>
							</button>
						<?php endif; ?>

						<?php
						if ( $question['showVotesUp'] ) :
							$is_voted = Helper::current_user_can_vote( "vote_question_up_{$quiz->get_id()}_{$question['id']}" ) ? '' : 'is-voted';
							?>
							<button type="button" class="wq-question-vote-btn wq-question-vote-up-btn <?php echo esc_attr( $is_voted ); ?>" <?php echo $disabled; ?>>
								<span class="icon"></span>
								<span class="number"><?php echo $question['votesUp'] ? intval( $question['votesUp'] ) : ''; ?></span>
								<span class="text"><?php $question['votesUp'] > 1 ? esc_html_e( 'Votes', 'wp-quiz-pro' ) : esc_html_e( 'Vote', 'wp-quiz-pro' ); ?></span>
							</button>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php $quiz->get_quiz_type()->load_template( 'questions/card.php', compact( 'quiz', 'question' ) ); ?>

	<?php
	/**
	 * Fires when end printing question content.
	 *
	 * @param array $question Question data.
	 * @param Quiz  $quiz     Quiz object.
	 */
	do_action( 'wp_quiz_end_question', $question, $quiz );
	?>

</div>
