<?php
/**
 * Facebook quiz settings options
 *
 * @package WPQuiz
 * @var CMB2 $cmb
 */

use WPQuiz\Helper;

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Promote the plugin', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_promote_plugin',
		'default' => Helper::get_option( 'promote_plugin' ),
	)
);

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Show embed code toggle', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_embed_toggle',
		'default' => Helper::get_option( 'embed_toggle' ),
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
		'default' => Helper::get_option( 'share_buttons' ),
	)
);

/**
 * Fires after registering fb_quiz settings tab.
 *
 * @since 2.0.0
 *
 * @param CMB2 $cmb CMB2 object.
 */
do_action( 'wp_quiz_fb_quiz_settings_tab', $cmb );
