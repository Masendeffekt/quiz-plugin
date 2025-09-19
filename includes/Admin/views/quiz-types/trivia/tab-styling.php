<?php
/**
 * Trivia styling options
 *
 * @package WPQuiz
 * @var CMB2 $cmb
 */

use WPQuiz\Admin\AdminHelper;

$cmb->add_field(
	array(
		'type'    => 'radio_inline',
		'name'    => __( 'Choose skin', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_skin',
		'options' => array(
			'traditional' => __( 'Traditional skin', 'wp-quiz-pro' ),
			'flat'        => __( 'Modern flat skin', 'wp-quiz-pro' ),
		),
		'default' => 'traditional',
	)
);

$cmb->add_field(
	array(
		'type'    => 'radio_inline',
		'name'    => __( 'Question layout', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_question_layout',
		'options' => array(
			'single'   => __( 'Show all', 'wp-quiz-pro' ),
			'multiple' => __( 'Multiple pages', 'wp-quiz-pro' ),
		),
		'default' => 'single',
	)
);

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Show Next Button', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_show_next_button',
		'default' => 'on',
		'dep'     => array(
			array( 'wp_quiz_question_layout', 'multiple' ),
		),
	)
);

$cmb->add_field(
	array(
		'type'    => 'colorpicker',
		'name'    => __( 'Progress bar color', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_bar_color',
		'default' => '#00c479',
		'dep'     => array(
			array( 'wp_quiz_question_layout', 'multiple' ),
		),
	)
);

$cmb->add_field(
	array(
		'type'    => 'colorpicker',
		'name'    => __( 'Questions font color', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_font_color',
		'default' => '#444',
	)
);

$cmb->add_field(
	array(
		'type'    => 'colorpicker',
		'name'    => __( 'Questions background color', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_background_color',
		'default' => '#f2f2f2',
	)
);

$cmb->add_field(
	array(
		'type'    => 'select_optgroup',
		'name'    => __( 'Animation in', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_animation_in',
		'options' => AdminHelper::get_animations_in(),
		'default' => 'fadeIn',
		'dep'     => array(
			array( 'wp_quiz_question_layout', 'multiple' ),
		),
	)
);

$cmb->add_field(
	array(
		'type'    => 'select_optgroup',
		'name'    => __( 'Animation out', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_animation_out',
		'options' => AdminHelper::get_animations_out(),
		'default' => 'fadeOut',
		'dep'     => array(
			array( 'wp_quiz_question_layout', 'multiple' ),
		),
	)
);

/**
 * Fires after registering trivia styling tab.
 *
 * @since 2.0.0
 *
 * @param CMB2 $cmb CMB2 object.
 */
do_action( 'wp_quiz_trivia_styling_tab', $cmb );
