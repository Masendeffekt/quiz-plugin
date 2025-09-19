<?php
/**
 * Personality content options
 *
 * @package WPQuiz
 * @var CMB2 $cmb
 * @var \WPQuiz\QuizType $quiz_type
 */

$cmb->add_field(
	array(
		'id'        => 'wp_quiz_personality_content_settings',
		'type'      => 'quiz_content',
		'quiz_type' => $quiz_type,
	)
);

/**
 * Fires after registering personality content tab.
 *
 * @since 2.0.0
 *
 * @param CMB2 $cmb CMB2 object.
 */
do_action( 'wp_quiz_personality_content_tab', $cmb );
