<?php
/**
 * Trivia quiz template
 *
 * @package WPQuiz
 *
 * @var Quiz  $quiz
 * @var array $args
 */

use WPQuiz\Quiz;

$quiz_type     = $quiz->get_quiz_type();
$overall_time  = $quiz_type->get_overall_timer( $quiz );
$question_time = $quiz_type->get_question_timer( $quiz );


// Quiz open tag.
$quiz_type->load_template( 'quiz-open.php', compact( 'quiz' ) );


/**
 * Fires when beginning rendering a quiz.
 *
 * @since 2.0.0
 *
 * @param Quiz $quiz Quiz object.
 */
do_action( 'wp_quiz_begin_quiz', $quiz );


if ( $overall_time > 0 ) {
	// translators: number of seconds.
	$message = sprintf( esc_html__( 'This is a timed quiz. You will be given %s seconds to answer all questions. Are you ready?', 'wp-quiz-pro' ), $overall_time );
	echo $quiz_type->quiz_intro( $quiz, $message ); // WPCS: xss ok.

	echo $quiz_type->overall_timer( $quiz ); // WPCS: xss ok.
}


if ( 'multiple' === $quiz_type->get_question_layout( $quiz ) ) {

	// Disable question time if overall time is set.
	if ( $question_time > 0 && $overall_time <= 0 ) {

		// translators: number of seconds.
		$message = sprintf( esc_html__( 'This is a timed quiz. You will be given %s seconds per question. Are you ready?', 'wp-quiz-pro' ), $question_time );
		echo $quiz_type->quiz_intro( $quiz, $message ); // WPCS: xss ok.

		echo $quiz_type->question_timer( $quiz ); // WPCS: xss ok.
	}

	echo $quiz_type->progress_bar( $quiz ); // WPCS: xss ok.
}

echo $quiz_type->quiz_questions( $quiz ); // WPCS: xss ok.

echo $quiz_type->quiz_results( $quiz ); // WPCS: xss ok.

echo $quiz_type->force_action( $quiz ); // WPCS: xss ok.

if ( 'on' === $quiz->get_setting( 'embed_toggle' ) ) {
	echo $quiz_type->embed_toggle( $quiz ); // WPCS: xss ok.
}

if ( 'on' === $quiz->get_setting( 'promote_plugin' ) ) {
	echo $quiz_type->promote_link( $quiz ); // WPCS: xss ok.
}

if ( 'on' === $quiz->get_setting( 'restart_questions' ) ) {
	echo $quiz_type->restart_quiz_button( $quiz ); // WPCS: xss ok.
}


/**
 * Fires when end a quiz.
 *
 * @since 2.0.0
 *
 * @param Quiz $quiz Quiz object.
 */
do_action( 'wp_quiz_end_quiz', $quiz );


// Quiz close tag.
$quiz_type->load_template( 'quiz-close.php', compact( 'quiz' ) );
