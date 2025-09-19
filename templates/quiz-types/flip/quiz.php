<?php
/**
 * Flip quiz template
 *
 * @package WPQuiz
 *
 * @var \WPQuiz\Quiz $quiz
 */

$quiz_type = $quiz->get_quiz_type();


// Quiz open tag.
$quiz_type->load_template( 'quiz-open.php', compact( 'quiz' ) );


/**
 * Fires when begin a quiz.
 *
 * @since 2.0.0
 *
 * @param Quiz $quiz Quiz object.
 */
do_action( 'wp_quiz_begin_quiz', $quiz );

echo $quiz_type->quiz_questions( $quiz ); // WPCS: xss ok.

if ( 'on' === $quiz->get_setting( 'embed_toggle' ) ) {
	echo $quiz_type->embed_toggle( $quiz ); // WPCS: xss ok.
}

if ( 'on' === $quiz->get_setting( 'promote_plugin' ) ) {
	echo $quiz_type->promote_link( $quiz ); // WPCS: xss ok.
}

echo $quiz_type->sharing( $quiz, __( 'Share:', 'wp-quiz-pro' ) ); // WPCS: xss ok.


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
