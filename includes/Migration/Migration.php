<?php
/**
 * Migration.
 *
 * @package WPQuiz
 */

namespace WPQuiz\Migration;

/**
 * Class Migration
 */
class Migration {

	/**
	 * Gets new animation in from the old value.
	 *
	 * @param string $old_animation Old animation value.
	 * @return string
	 */
	public static function transition_animation_in( $old_animation ) {
		switch ( $old_animation ) {
			case 'fade':
				return 'fadeIn';
			case 'scale':
				return 'zoomIn';
			case 'fade up':
				return 'fadeInUp';
			case 'fade down':
				return 'fadeInDown';
			case 'fade left':
				return 'fadeInLeft';
			case 'fade right':
				return 'fadeInRight';
			case 'horizontal flip':
				return 'flipInX';
			case 'vertical flip':
				return 'flipInY';
			case 'drop':
				return 'zoomInDown';
			case 'fly left':
				return 'bounceInLeft';
			case 'fly right':
				return 'bounceInRight';
			case 'fly up':
				return 'bounceInUp';
			case 'fly down':
				return 'bounceInDown';
			case 'swing left':
			case 'swing right':
				return 'flipInX';
			case 'swing up':
			case 'swing down':
				return 'flipInY';
			case 'browse':
				return 'bounceInLeft';
			case 'browse right':
				return 'bounceInRight';
			case 'slide down':
				return 'slideInDown';
			case 'slide up':
				return 'slideInUp';
			case 'slide left':
				return 'slideInLeft';
			case 'slide right':
				return 'slideInRight';
			default:
				return $old_animation;
		}
	}

	/**
	 * Gets new animation out from the old value.
	 *
	 * @param string $old_animation Old animation value.
	 * @return string
	 */
	public static function transition_animation_out( $old_animation ) {
		switch ( $old_animation ) {
			case 'fade':
				return 'fadeOut';
			case 'scale':
				return 'zoomOut';
			case 'fade up':
				return 'fadeOutUp';
			case 'fade down':
				return 'fadeOutDown';
			case 'fade left':
				return 'fadeOutLeft';
			case 'fade right':
				return 'fadeOutRight';
			case 'horizontal flip':
				return 'flipOutX';
			case 'vertical flip':
				return 'flipOutY';
			case 'drop':
				return 'zoomOutDown';
			case 'fly left':
				return 'bounceOutLeft';
			case 'fly right':
				return 'bounceOutRight';
			case 'fly up':
				return 'bounceOutUp';
			case 'fly down':
				return 'bounceOutDown';
			case 'swing left':
			case 'swing right':
				return 'flipOutX';
			case 'swing up':
			case 'swing down':
				return 'flipOutY';
			case 'browse':
				return 'bounceOutLeft';
			case 'browse right':
				return 'bounceOutRight';
			case 'slide down':
				return 'slideOutDown';
			case 'slide up':
				return 'slideOutUp';
			case 'slide left':
				return 'slideOutLeft';
			case 'slide right':
				return 'slideOutRight';
			default:
				return $old_animation;
		}
	}
}
