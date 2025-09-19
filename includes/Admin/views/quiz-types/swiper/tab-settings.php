<?php
/**
 * Swiper quiz settings options
 *
 * @package WPQuiz
 * @var CMB2 $cmb
 */

use WPQuiz\Helper;

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Randomize questions', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_rand_questions',
		'default' => Helper::get_option( 'rand_questions' ),
	)
);

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Restart questions', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_restart_questions',
		'default' => Helper::get_option( 'restart_questions' ),
	)
);

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Promote the plugin', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_promote_plugin',
		'default' => 'off',
	)
);

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Show embed code toggle', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_embed_toggle',
		'default' => 'off',
	)
);

$cmb->add_field(
	array(
		'type'    => 'pw_multiselect',
		'name'    => __( 'Share buttons', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_share_buttons',
		'options' => array(
			'fb' => __( 'Facebook', 'wp-quiz-pro' ),
			'tw' => __( 'Twitter', 'wp-quiz-pro' ),
			'vk' => __( 'VK', 'wp-quiz-pro' ),
		),
		'default' => array( 'fb', 'tw', 'vk' ),
	)
);

/**
 * Fires after registering swiper settings tab.
 *
 * @since 2.0.0
 *
 * @param CMB2 $cmb CMB2 object.
 */
do_action( 'wp_quiz_swiper_settings_tab', $cmb );
