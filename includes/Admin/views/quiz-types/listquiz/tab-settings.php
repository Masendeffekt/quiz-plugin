<?php
/**
 * List quiz settings options
 *
 * @package WPQuiz
 * @var CMB2 $cmb
 */

use WPQuiz\Helper;

$cmb->add_field(
	array(
		'type'             => 'select',
		'name'             => __( 'Sort questions by', 'wp-quiz-pro' ),
		'id'               => 'wp_quiz_question_orderby',
		'show_option_none' => __( 'Default', 'wp-quiz-pro' ),
		'options'          => array(
			'votes'  => __( 'Votes', 'wp-quiz-pro' ),
			'random' => __( 'Random', 'wp-quiz-pro' ),
		),
	)
);

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

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Show ads', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_show_ads',
		'default' => Helper::get_option( 'show_ads' ),
	)
);

$cmb->add_field(
	array(
		'id'      => 'wp_quiz_ad_title',
		'type'    => 'text',
		'name'    => __( 'Advertisement title', 'wp-quiz-pro' ),
		'default' => Helper::get_option( 'ad_title' ),
		'dep'     => array(
			array( 'wp_quiz_show_ads', 'on' ),
		),
	)
);

$cmb->add_field(
	array(
		'type'       => 'text',
		'name'       => __( 'Ads after every nth question', 'wp-quiz-pro' ),
		'id'         => 'wp_quiz_ad_nth_display',
		'attributes' => array(
			'type' => 'number',
			'min'  => 0,
			'step' => 1,
		),
		'dep'        => array(
			array( 'wp_quiz_show_ads', 'on' ),
		),
		'default'    => Helper::get_option( 'ad_nth_display' ),
	)
);

$cmb->add_field(
	array(
		'type'    => 'switch',
		'name'    => __( 'Repeat Ads', 'wp-quiz-pro' ),
		'id'      => 'wp_quiz_repeat_ads',
		'dep'     => array(
			array( 'wp_quiz_show_ads', 'on' ),
		),
		'default' => Helper::get_option( 'repeat_ads' ),
	)
);

$cmb->add_field(
	array(
		'type'       => 'textarea_code',
		'name'       => __( 'Ad Codes', 'wp-quiz-pro' ),
		'id'         => 'wp_quiz_ad_codes',
		'repeatable' => true,
		'dep'        => array(
			array( 'wp_quiz_show_ads', 'on' ),
		),
	)
);

/**
 * Fires after registering list quiz settings tab.
 *
 * @since 2.0.0
 *
 * @param CMB2 $cmb CMB2 object.
 */
do_action( 'wp_quiz_listquiz_settings_tab', $cmb );
