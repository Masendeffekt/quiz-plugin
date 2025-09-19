<?php
/**
 * Facebook quiz styling options
 *
 * @package WPQuiz
 * @var CMB2 $cmb
 */

use WPQuiz\Admin\AdminHelper;
use WPQuiz\Helper;

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
		'name'    => __( 'Result title color', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_title_color',
		'default' => '#444',
	)
);

$cmb->add_field(
	array(
		'type'    => 'text',
		'name'    => __( 'Result title font size', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_title_size',
		'default' => 16,
	)
);

$cmb->add_field(
	array(
		'type'    => 'select',
		'name'    => __( 'Result title font weight', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_title_weight',
		'options' => array(
			300 => 300,
			400 => 400,
			500 => 500,
			600 => 600,
			700 => 700,
			900 => 900,
		),
		'default' => 700,
	)
);

$cmb->add_field(
	array(
		'type'    => 'select',
		'name'    => __( 'Result title font style', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_title_style',
		'options' => array(
			'normal'  => __( 'Normal', 'wp-quiz-pro' ),
			'italic'  => __( 'Italic', 'wp-quiz-pro' ),
			'oblique' => __( 'Oblique', 'wp-quiz-pro' ),
		),
	)
);

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Use Continue As button', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_continue_as_btn',
		'default' => Helper::get_option( 'continue_as_btn' ),
	)
);

/**
 * Fires after registering fb_quiz styling tab.
 *
 * @since 2.0.0
 *
 * @param CMB2 $cmb CMB2 object.
 */
do_action( 'wp_quiz_fb_quiz_styling_tab', $cmb );
