<?php
/**
 * Admin helpers
 *
 * @package WPQuiz
 */

namespace WPQuiz\Admin;

/**
 * Class Helpers
 */
class AdminHelper {

	/**
	 * Loads view.
	 *
	 * @param string $file_path View file path.
	 * @param array  $data      Data passed to view.
	 */
	public static function load_view( $file_path, array $data = array() ) {
		// phpcs:ignore
		extract( $data );
		include wp_quiz()->admin_dir() . 'views/' . $file_path;
	}

	/**
	 * Gets list of animations in.
	 *
	 * @return array
	 */
	public static function get_animations_in() {
		return array(
			__( 'Attention Seekers', 'wp-quiz-pro' )  => array(
				'bounce'     => __( 'bounce', 'wp-quiz-pro' ),
				'flash'      => __( 'flash', 'wp-quiz-pro' ),
				'pulse'      => __( 'pulse', 'wp-quiz-pro' ),
				'rubberBand' => __( 'rubberBand', 'wp-quiz-pro' ),
				'shake'      => __( 'shake', 'wp-quiz-pro' ),
				'swing'      => __( 'swing', 'wp-quiz-pro' ),
				'tada'       => __( 'tada', 'wp-quiz-pro' ),
				'wobble'     => __( 'wobble', 'wp-quiz-pro' ),
				'jello'      => __( 'jello', 'wp-quiz-pro' ),
			),
			__( 'Bouncing Entrances', 'wp-quiz-pro' ) => array(
				'bounceIn'      => __( 'bounceIn', 'wp-quiz-pro' ),
				'bounceInDown'  => __( 'bounceInDown', 'wp-quiz-pro' ),
				'bounceInLeft'  => __( 'bounceInLeft', 'wp-quiz-pro' ),
				'bounceInRight' => __( 'bounceInRight', 'wp-quiz-pro' ),
				'bounceInUp'    => __( 'bounceInUp', 'wp-quiz-pro' ),
			),
			__( 'Fading Entrances', 'wp-quiz-pro' )   => array(
				'fadeIn'         => __( 'fadeIn', 'wp-quiz-pro' ),
				'fadeInDown'     => __( 'fadeInDown', 'wp-quiz-pro' ),
				'fadeInDownBig'  => __( 'fadeInDownBig', 'wp-quiz-pro' ),
				'fadeInLeft'     => __( 'fadeInLeft', 'wp-quiz-pro' ),
				'fadeInLeftBig'  => __( 'fadeInLeftBig', 'wp-quiz-pro' ),
				'fadeInRight'    => __( 'fadeInRight', 'wp-quiz-pro' ),
				'fadeInRightBig' => __( 'fadeInRightBig', 'wp-quiz-pro' ),
				'fadeInUp'       => __( 'fadeInUp', 'wp-quiz-pro' ),
				'fadeInUpBig'    => __( 'fadeInUpBig', 'wp-quiz-pro' ),
			),
			__( 'Flippers', 'wp-quiz-pro' )           => array(
				'flip'    => __( 'flip', 'wp-quiz-pro' ),
				'flipInX' => __( 'flipInX', 'wp-quiz-pro' ),
				'flipInY' => __( 'flipInY', 'wp-quiz-pro' ),
			),
			__( 'Lightspeed', 'wp-quiz-pro' )         => array(
				'lightSpeedIn' => __( 'lightSpeedIn', 'wp-quiz-pro' ),
			),
			__( 'Rotating Entrances', 'wp-quiz-pro' ) => array(
				'rotateIn'          => __( 'rotateIn', 'wp-quiz-pro' ),
				'rotateInDownLeft'  => __( 'rotateInDownLeft', 'wp-quiz-pro' ),
				'rotateInDownRight' => __( 'rotateInDownRight', 'wp-quiz-pro' ),
				'rotateInUpLeft'    => __( 'rotateInUpLeft', 'wp-quiz-pro' ),
				'rotateInUpRight'   => __( 'rotateInUpRight', 'wp-quiz-pro' ),
			),
			__( 'Sliding Entrances', 'wp-quiz-pro' )  => array(
				'slideInUp'    => __( 'slideInUp', 'wp-quiz-pro' ),
				'slideInDown'  => __( 'slideInDown', 'wp-quiz-pro' ),
				'slideInLeft'  => __( 'slideInLeft', 'wp-quiz-pro' ),
				'slideInRight' => __( 'slideInRight', 'wp-quiz-pro' ),
			),
			__( 'Zoom Entrances', 'wp-quiz-pro' )     => array(
				'zoomIn'      => __( 'zoomIn', 'wp-quiz-pro' ),
				'zoomInDown'  => __( 'zoomInDown', 'wp-quiz-pro' ),
				'zoomInLeft'  => __( 'zoomInLeft', 'wp-quiz-pro' ),
				'zoomInRight' => __( 'zoomInRight', 'wp-quiz-pro' ),
				'zoomInUp'    => __( 'zoomInUp', 'wp-quiz-pro' ),
			),
			__( 'Specials', 'wp-quiz-pro' )           => array(
				'jackInTheBox' => __( 'jackInTheBox', 'wp-quiz-pro' ),
				'rollIn'       => __( 'rollIn', 'wp-quiz-pro' ),
			),
		);
	}


	/**
	 * Gets list of animations out.
	 *
	 * @return array
	 */
	public static function get_animations_out() {
		return array(
			__( 'Attention Seekers', 'wp-quiz-pro' ) => array(
				'bounce'     => __( 'bounce', 'wp-quiz-pro' ),
				'flash'      => __( 'flash', 'wp-quiz-pro' ),
				'pulse'      => __( 'pulse', 'wp-quiz-pro' ),
				'rubberBand' => __( 'rubberBand', 'wp-quiz-pro' ),
				'shake'      => __( 'shake', 'wp-quiz-pro' ),
				'swing'      => __( 'swing', 'wp-quiz-pro' ),
				'tada'       => __( 'tada', 'wp-quiz-pro' ),
				'wobble'     => __( 'wobble', 'wp-quiz-pro' ),
				'jello'      => __( 'jello', 'wp-quiz-pro' ),
			),
			__( 'Bouncing Exits', 'wp-quiz-pro' )    => array(
				'bounceOut'      => __( 'bounceOut', 'wp-quiz-pro' ),
				'bounceOutDown'  => __( 'bounceOutDown', 'wp-quiz-pro' ),
				'bounceOutLeft'  => __( 'bounceOutLeft', 'wp-quiz-pro' ),
				'bounceOutRight' => __( 'bounceOutRight', 'wp-quiz-pro' ),
				'bounceOutUp'    => __( 'bounceOutUp', 'wp-quiz-pro' ),
			),
			__( 'Fading Exits', 'wp-quiz-pro' )      => array(
				'fadeOut'         => __( 'fadeOut', 'wp-quiz-pro' ),
				'fadeOutDown'     => __( 'fadeOutDown', 'wp-quiz-pro' ),
				'fadeOutDownBig'  => __( 'fadeOutDownBig', 'wp-quiz-pro' ),
				'fadeOutLeft'     => __( 'fadeOutLeft', 'wp-quiz-pro' ),
				'fadeOutLeftBig'  => __( 'fadeOutLeftBig', 'wp-quiz-pro' ),
				'fadeOutRight'    => __( 'fadeOutRight', 'wp-quiz-pro' ),
				'fadeOutRightBig' => __( 'fadeOutRightBig', 'wp-quiz-pro' ),
				'fadeOutUp'       => __( 'fadeOutUp', 'wp-quiz-pro' ),
				'fadeOutUpBig'    => __( 'fadeOutUpBig', 'wp-quiz-pro' ),
			),
			__( 'Flippers', 'wp-quiz-pro' )          => array(
				'flip'     => __( 'flip', 'wp-quiz-pro' ),
				'flipOutX' => __( 'flipOutX', 'wp-quiz-pro' ),
				'flipOutY' => __( 'flipOutY', 'wp-quiz-pro' ),
			),
			__( 'Lightspeed', 'wp-quiz-pro' )        => array(
				'lightSpeedOut' => __( 'lightSpeedOut', 'wp-quiz-pro' ),
			),
			__( 'Rotating Exits', 'wp-quiz-pro' )    => array(
				'rotateOut'          => __( 'rotateOut', 'wp-quiz-pro' ),
				'rotateOutDownLeft'  => __( 'rotateOutDownLeft', 'wp-quiz-pro' ),
				'rotateOutDownRight' => __( 'rotateOutDownRight', 'wp-quiz-pro' ),
				'rotateOutUpLeft'    => __( 'rotateOutUpLeft', 'wp-quiz-pro' ),
				'rotateOutUpRight'   => __( 'rotateOutUpRight', 'wp-quiz-pro' ),
			),
			__( 'Sliding Exits', 'wp-quiz-pro' )     => array(
				'slideOutUp'    => __( 'slideOutUp', 'wp-quiz-pro' ),
				'slideOutDown'  => __( 'slideOutDown', 'wp-quiz-pro' ),
				'slideOutLeft'  => __( 'slideOutLeft', 'wp-quiz-pro' ),
				'slideOutRight' => __( 'slideOutRight', 'wp-quiz-pro' ),
			),
			__( 'Zoom Exits', 'wp-quiz-pro' )        => array(
				'zoomOut'      => __( 'zoomOut', 'wp-quiz-pro' ),
				'zoomOutDown'  => __( 'zoomOutDown', 'wp-quiz-pro' ),
				'zoomOutLeft'  => __( 'zoomOutLeft', 'wp-quiz-pro' ),
				'zoomOutRight' => __( 'zoomOutRight', 'wp-quiz-pro' ),
				'zoomOutUp'    => __( 'zoomOutUp', 'wp-quiz-pro' ),
			),
			__( 'Specials', 'wp-quiz-pro' )          => array(
				'hinge'   => __( 'hinge', 'wp-quiz-pro' ),
				'rollOut' => __( 'rollOut', 'wp-quiz-pro' ),
			),
		);
	}

	/**
	 * Gets tooltip html.
	 *
	 * @param  string  $text    Display text.
	 * @param  string  $tooltip Tooltip text.
	 * @param  boolean $echo    Show the tooltip or not.
	 * @return string
	 */
	public static function tooltip( $text, $tooltip, $echo = true ) {
		$output = sprintf(
			'<span class="wp-quiz-tooltip" data-tooltip="%1$s">%2$s</span>',
			esc_attr( $tooltip ),
			wp_kses_post( $text )
		);
		if ( ! $echo ) {
			return $output;
		}
		echo $output; // WPCS: xss ok.
	}
}
