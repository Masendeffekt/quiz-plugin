<?php
/**
 * Helper class
 *
 * @package WPQuiz
 */

namespace WPQuiz;

use WP_Error;
use WPQuiz\Modules\Subscription\MailServices\Manager;
use WPQuiz\Storages\Cookie;

/**
 * Class Helper
 */
class Helper {

	/**
	 * Gets default options.
	 *
	 * @return array
	 */
	public static function get_default_options() {
		$mts_username = '';
		// MTS Connect plugin username.
		$mts_connect_data = get_option( 'mts_connect_data' );
		if ( false !== $mts_connect_data ) {
			$mts_username = $mts_connect_data['username'];
		}

		$defaults = array(
			'quiz_slug'                       => 'wp_quiz ', // Adding space at the end because `wp_quiz` is a callable.
			'record_guest_method'             => 'ip',
			'allow_user_multi_votes'          => 'off',
			'subscribe_box_title'             => __( 'Just tell us who you are to view your results!', 'wp-quiz-pro' ),
			'subscribe_locker_box_title'      => __( 'Just tell us who you are to play the quiz!', 'wp-quiz-pro' ),
			'subscribe_box_user_consent'      => __( 'I consent to having form collect my name and email!', 'wp-quiz-pro' ),
			// translators: %1$s: privacy policy link open tag, %2$s: privacy policy link close tag.
			'subscribe_box_user_consent_desc' => sprintf( __( 'The form collects name and email so that we can add you to our newsletter list. Check out our %1$sprivacy policy%2$s to know how we protect and manage your submitted data.', 'wp-quiz-pro' ), '<a href="#">', '</a>' ),
			'rand_questions'                  => 'off',
			'rand_answers'                    => 'off',
			'restart_questions'               => 'off',
			'promote_plugin'                  => 'off',
			'embed_toggle'                    => 'off',
			'share_buttons'                   => '',
			'share_meta'                      => 'off',
			'auto_scroll'                     => 'off',
			'continue_as_btn'                 => 'off',
			'css_footer'                      => 'off',
			'show_ads'                        => 'off',
			'repeat_ads'                      => 'off',
			'result_popup'                    => 'off',
			'mts_username'                    => $mts_username,
			'ga_event_tracking'               => 'off',
			'ga_no_print_code'                => 'off',
			'ga_event_category'               => 'quiz',
			'ga_event_action'                 => 'submit',
			'ga_event_label'                  => '%QUIZ_NAME%',
			'players_tracking'                => 'on',
			'email_result_to_admin'           => 'off',
			'fb_app_id'                       => '',
			'stripe_api_key'                  => '',
			'paypal_client_id'                => '',
			'currency'                        => 'USD',
			'countdown_timer'                 => 0,
			'refresh_step'                    => 0,
			'result_method'                   => 'show',
			'ad_nth_display'                  => 0,
			'ad_codes'                        => array(),
			'ga_tracking_id'                  => '',
			'ad_title'                        => __( 'Advertisement', 'wp-quiz-pro' ),
			'show_timer_minute'               => 'off',
			'subscribe_to_play'               => 'off',
		);

		$quiz_types = QuizTypeManager::get_all( true );
		foreach ( $quiz_types as $name => $quiz_type ) {
			$defaults[ 'enable_' . $name ] = 'on';
		}

		/**
		 * Allows changing plugin default options.
		 *
		 * @since 2.0.0
		 *
		 * @param array $options Default options.
		 */
		return apply_filters( 'wp_quiz_default_options', $defaults );
	}

	/**
	 * Gets option.
	 *
	 * @param string $name Option name.
	 * @return mixed
	 */
	public static function get_option( $name = '' ) {
		$options = get_option( 'wp_quiz_pro_default_settings' );
		if ( ! $name ) {
			return $options;
		}

		$defaults = self::get_default_options();
		$value    = isset( $options[ $name ] ) ? $options[ $name ] : ( isset( $defaults[ $name ] ) ? $defaults[ $name ] : null );

		/**
		 * Filters the value of an option.
		 * The dynamic portion of the hook name, `$name`, refers to the option name.
		 *
		 * @since 2.0.0
		 *
		 * @param mixed $value Value of the option.
		 */
		$value = apply_filters( "wp_quiz_option_{$name}", $value );

		/**
		 * Filters the value of an option.
		 *
		 * @since 2.0.0
		 *
		 * @param mixed  $value Value of the option.
		 * @param string $name  Option name.
		 */
		$value = apply_filters( 'wp_quiz_option', $value, $name );
		return $value;
	}

	/**
	 * Updates option.
	 *
	 * @since 2.0.8
	 *
	 * @param string $name  Option name.
	 * @param mixed  $value Option value.
	 */
	public static function update_option( $name, $value ) {
		$options = get_option( 'wp_quiz_pro_default_settings', array() );
		$options[ $name ] = $value;
		update_option( 'wp_quiz_pro_default_settings', $options );
	}

	/**
	 * Deletes option.
	 *
	 * @since 2.0.8
	 *
	 * @param string $name Option name.
	 */
	public static function delete_option( $name ) {
		$options = get_option( 'wp_quiz_pro_default_settings', array() );
		if ( isset( $options[ $name ] ) ) {
			unset( $options[ $name ] );
		}
		update_option( 'wp_quiz_pro_default_settings', $options );
	}

	/**
	 * Gets quiz ad codes.
	 *
	 * @param int $quiz_id Quiz ID or object.
	 * @return array|false Return empty array if there is no ad code, false if invalid quiz.
	 */
	public static function get_ad_codes( $quiz_id ) {
		$quiz = PostTypeQuiz::get_quiz( $quiz_id );
		if ( ! $quiz ) {
			return false;
		}
		$ad_codes = $quiz->get_setting( 'ad_codes' );
		if ( $ad_codes ) {
			return is_string( $ad_codes ) ? explode( ',', $ad_codes ) : (array) $ad_codes;
		}
		$settings = self::get_option( 'defaults' );
		if ( ! empty( $settings['ad_codes'] ) ) {
			return $settings['ad_codes'];
		}
		return array();
	}

	/**
	 * Generates random string.
	 *
	 * @param int $length String length.
	 * @return string
	 */
	public static function generate_random_string( $length = 5 ) {
		if ( ! $length ) {
			return '';
		}

		$str      = '';
		$possible = 'abcdefghijklmnopqrstuvwxyz0123456789';
		for ( $i = 0; $i < $length; $i++ ) {
			$str .= $possible[ wp_rand( 0, strlen( $possible ) - 1 ) ];
		}
		return $str;
	}

	/**
	 * Gets current ip address.
	 *
	 * @return string
	 */
	public static function get_current_ip() {
		// phpcs:disable
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		// phpcs:enable

		return $ip;
	}

	/**
	 * Gets the current URL.
	 *
	 * @since 2.0.7
	 *
	 * @return string
	 */
	public static function get_current_url() {
		global $wp;
		return home_url( $wp->request );
	}

	/**
	 * Records current user action.
	 *
	 * @param string $action Action name.
	 */
	public static function record_current_user_action( $action ) {
		$user_actions = new UserActions();

		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			$user_actions->add( $action, $user_id );
			return;
		}

		$method = self::get_option( 'record_guest_method' );
		if ( 'ip' === $method ) {
			// Record by ip address.
			$user_ip = self::get_current_ip();
			$user_actions->add( $action, 0, $user_ip );
			return;
		}

		// Record by cookie.
		$cookie = new Cookie();
		$cookie->update( $action, 1 );
	}

	/**
	 * Checks if user did action.
	 *
	 * @param string $action Action name.
	 * @return bool
	 */
	public static function current_user_has_action( $action ) {
		$user_actions = new UserActions();

		if ( is_user_logged_in() ) {
			return $user_actions->has( $action, get_current_user_id() );
		}

		$method = self::get_option( 'record_guest_method' );
		if ( 'ip' === $method ) {
			return $user_actions->has( $action, 0, self::get_current_ip() );
		}

		// Record by cookie.
		$cookie = new Cookie();
		return $cookie->has( $action );
	}

	/**
	 * Checks if user can vote.
	 *
	 * @param string $action Vote action.
	 * @return bool
	 */
	public static function current_user_can_vote( $action ) {
		return 'on' === self::get_option( 'allow_user_multi_votes' ) || ! self::current_user_has_action( $action );
	}

	/**
	 * Shuffles assoc array.
	 *
	 * @param array $list Assoc array.
	 * @return array
	 */
	public static function shuffle_assoc( $list ) {
		if ( ! is_array( $list ) ) {
			return $list;
		}

		$keys = array_keys( $list );
		shuffle( $keys );
		$random = array();
		foreach ( $keys as $key ) {
			$random[ $key ] = $list[ $key ];
		}

		return $random;
	}

	/**
	 * Prints spiner.
	 */
	public static function spinner() {
		printf(
			'<img class="wq-spinner" src="%s">',
			esc_url( wp_quiz()->assets() . 'images/image_spinner.gif' )
		);
	}

	/**
	 * Saves base64 image.
	 *
	 * @param string $image_code Image code.
	 * @param string $file_path  Image file path.
	 * @return bool|WP_Error
	 */
	public static function save_base64_image( $image_code, $file_path ) {
		// phpcs:disable
		$image = base64_decode( $image_code );
		$bytes = file_put_contents( $file_path, $image );
		if ( ! $bytes ) {
			@unlink( $file_path );
			return new WP_Error( 'error-saving-image', __( 'Error saving image', 'wp-quiz-pro' ) );
		}
		if ( ! self::is_image_file( $file_path ) ) {
			@unlink( $file_path );
			return new WP_Error( 'invalid-image', __( 'Invalid image', 'wp-quiz-pro' ) );
		}
		// phpcs:enable
		return true;
	}

	/**
	 * Checks if a file is an image.
	 *
	 * @param string $file_path File path.
	 * @return bool
	 */
	public static function is_image_file( $file_path ) {
		$info = getimagesize( $file_path );
		return $info[0] > 0 && $info[1] > 0 && $info['mime'];
	}

	/**
	 * Gets player data.
	 *
	 * @param int $player_id Player ID.
	 * @return array
	 */
	public static function get_player( $player_id ) {
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}wp_quiz_players WHERE id = %d",
				intval( $player_id )
			),
			ARRAY_A
		); // WPCS: db call ok, cache ok.
	}

	/**
	 * Converts JSON string to array.
	 *
	 * @param string $str JSON string.
	 * @return array
	 */
	public static function json_decode( $str ) {
		return json_decode( $str, true );
	}

	/**
	 * Handles the exception.
	 *
	 * @param \Exception $exception Exception object.
	 * @return WP_Error
	 */
	public static function handle_exception( \Exception $exception ) {
		return new WP_Error( $exception->getCode(), $exception->getMessage() );
	}

	/**
	 * Subscribes email.
	 *
	 * @param string $email Email address.
	 * @param string $name  Name.
	 * @return bool
	 */
	public static function subscribe_email( $email, $name ) {
		$mail_service = self::get_option( 'mail_service' );
		if ( ! $mail_service ) {
			return false;
		}
		$mail_service = Manager::get( $mail_service );
		if ( ! $mail_service ) {
			return false;
		}
		return $mail_service->subscribe( $email, $name );
	}

	/**
	 * Downloads image file.
	 *
	 * @param string $file    Image file url.
	 * @param string $post_id Post ID.
	 * @param string $desc    Attachment description.
	 * @return array|false Return an array contains `id`, `path` and `url`. Return `false` on failure.
	 */
	public static function download_image_file( $file, $post_id = '', $desc = '' ) {
		if ( ! $file ) {
			return false;
		}

		// Need to require these files.
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
		}

		error_log( 'Start downloading: ' . $file );

		// Download file to temp location.
		$tmp = download_url( $file );
		if ( ! self::is_image_file( $tmp ) ) {
			return false;
		}

		// Set variables for storage, fix file filename for query strings.
		preg_match( '/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $file, $matches );
		$file_array['name']     = basename( $matches[0] );
		$file_array['tmp_name'] = $tmp;

		// If error storing temporarily, unlink.
		if ( is_wp_error( $tmp ) ) {
			@unlink( $file_array['tmp_name'] ); // phpcs:ignore
			$file_array['tmp_name'] = '';
			return false;
		}
		if ( ! $desc ) {
			$desc = $file_array['name'];
		}
		$id = media_handle_sideload( $file_array, $post_id, $desc );
		// If error storing permanently, unlink.
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] ); // phpcs:ignore
			return false;
		}

		$image = array(
			'id'   => $id,
			'path' => get_attached_file( $id ),
			'url'  => wp_get_attachment_url( $id ),
		);
		error_log( 'Downloaded: ' . $image['url'] );

		return $image;
	}

	/**
	 * Checks if is in embed page.
	 *
	 * @return bool
	 */
	public static function is_embed() {
		return ! empty( $_GET['wp_quiz_id'] );
	}

	/**
	 * Gets video content.
	 *
	 * @param string $video_url      Video url.
	 * @param string $poster_url     Poster image url.
	 * @param bool   $unload_scripts Unload styles and scripts.
	 * @return string
	 */
	public static function video_content( $video_url, $poster_url = '', $unload_scripts = false ) {
		if ( '.mp4' === substr( strtolower( $video_url ), -4 ) || '.webm' === substr( strtolower( $video_url ), -5 ) ) {
			ob_start();
			?>
			<video controls poster="<?php echo esc_url( $poster_url ); ?>" width="640" height="360">
				<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
			</video>
			<?php
			return ob_get_clean();
		}

		$video_attrs  = array(
			'src'    => $video_url,
			'poster' => $poster_url,
		);
		$video_output = wp_video_shortcode( $video_attrs );

		if ( wp_strip_all_tags( $video_output ) !== $video_url ) {
			if ( $unload_scripts ) {
				return $video_output;
			}

			$styles     = '';
			$mce_styles = wpview_media_sandbox_styles();
			foreach ( $mce_styles as $style ) {
				$styles .= sprintf( '<link rel="stylesheet" href="%s"/>', $style ); // phpcs:ignore
			}

			global $wp_scripts;
			if ( ! empty( $wp_scripts ) ) {
				$wp_scripts->done = array();
			}
			ob_start();
			wp_print_scripts( array( 'mediaelement-vimeo', 'wp-mediaelement' ) );
			$scripts = ob_get_clean();

			return $styles . $video_output . $scripts;
		}

		$embed_output = wp_oembed_get( $video_url );
		if ( ! $embed_output ) {
			$embed_output = '<iframe src="' . $video_url . '" width="100%" height="100%" frameborder="0" scrolling="no" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
		}

		$output = '<div class="wq-embed">';
		if ( $poster_url ) {
			$output .= sprintf( '<img src="%s" alt="" class="wq-embed-placeholder"><span class="wq-embed-icon"></span>', esc_url( $poster_url ) );
		}
		$output .= $embed_output;
		$output .= '</div>';

		return $output;
	}

	/**
	 * Gets FB result image path from image file name.
	 *
	 * @param string $file_name Image file name.
	 * @return string
	 */
	public static function get_fb_result_image_path( $file_name ) {
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'] . '/wp_quiz-result-images/' . $file_name;
	}

	/**
	 * Gets FB result image url from image file name.
	 *
	 * @param string $file_name Image file name.
	 * @return string
	 */
	public static function get_fb_result_image_url( $file_name ) {
		$upload_dir = wp_upload_dir();
		return $upload_dir['baseurl'] . '/wp_quiz-result-images/' . $file_name;
	}

	/**
	 * Gets force actions.
	 *
	 * @return array
	 */
	public static function get_force_actions() {
		/**
		 * Allows adding force actions.
		 *
		 * @since 2.0.0
		 *
		 * @param array $force_actions Force actions.
		 */
		return apply_filters(
			'wp_quiz_force_actions',
			array(
				'0'     => __( 'No Action', 'wp-quiz-pro' ),
				'1'     => __( 'Capture Email', 'wp-quiz-pro' ),
				'2'     => __( 'Facebook Share', 'wp-quiz-pro' ),
				'login' => __( 'Login/Register', 'wp-quiz-pro' ),
			)
		);
	}

	/**
	 * Gets currencies.
	 *
	 * @return array
	 */
	public static function get_currencies() {
		$currencies = array(
			'USD' => array(
				'title'  => 'USD',
				'symbol' => 'USD',
			),
			'AED' => array(
				'title'  => 'AED',
				'symbol' => 'AED',
			),
			'AFN' => array(
				'title'  => 'AFN',
				'symbol' => 'AFN',
			),
			'ALL' => array(
				'title'  => 'ALL',
				'symbol' => 'ALL',
			),
			'AMD' => array(
				'title'  => 'AMD',
				'symbol' => 'AMD',
			),
			'ANG' => array(
				'title'  => 'ANG',
				'symbol' => 'ANG',
			),
			'AOA' => array(
				'title'  => 'AOA',
				'symbol' => 'AOA',
			),
			'ARS' => array(
				'title'  => 'ARS',
				'symbol' => 'ARS',
			),
			'AUD' => array(
				'title'  => 'AUD',
				'symbol' => 'AUD',
			),
			'AWG' => array(
				'title'  => 'AWG',
				'symbol' => 'AWG',
			),
			'AZN' => array(
				'title'  => 'AZN',
				'symbol' => 'AZN',
			),
			'BAM' => array(
				'title'  => 'BAM',
				'symbol' => 'BAM',
			),
			'BBD' => array(
				'title'  => 'BBD',
				'symbol' => 'BBD',
			),
			'BDT' => array(
				'title'  => 'BDT',
				'symbol' => 'BDT',
			),
			'BGN' => array(
				'title'  => 'BGN',
				'symbol' => 'BGN',
			),
			'BIF' => array(
				'title'  => 'BIF',
				'symbol' => 'BIF',
			),
			'BMD' => array(
				'title'  => 'BMD',
				'symbol' => 'BMD',
			),
			'BND' => array(
				'title'  => 'BND',
				'symbol' => 'BND',
			),
			'BOB' => array(
				'title'  => 'BOB',
				'symbol' => 'BOB',
			),
			'BRL' => array(
				'title'  => 'BRL',
				'symbol' => 'BRL',
			),
			'BSD' => array(
				'title'  => 'BSD',
				'symbol' => 'BSD',
			),
			'BWP' => array(
				'title'  => 'BWP',
				'symbol' => 'BWP',
			),
			'BZD' => array(
				'title'  => 'BZD',
				'symbol' => 'BZD',
			),
			'CAD' => array(
				'title'  => 'CAD',
				'symbol' => 'CAD',
			),
			'CDF' => array(
				'title'  => 'CDF',
				'symbol' => 'CDF',
			),
			'CHF' => array(
				'title'  => 'CHF',
				'symbol' => 'CHF',
			),
			'CLP' => array(
				'title'  => 'CLP',
				'symbol' => 'CLP',
			),
			'CNY' => array(
				'title'  => 'CNY',
				'symbol' => 'CNY',
			),
			'COP' => array(
				'title'  => 'COP',
				'symbol' => 'COP',
			),
			'CRC' => array(
				'title'  => 'CRC',
				'symbol' => 'CRC',
			),
			'CVE' => array(
				'title'  => 'CVE',
				'symbol' => 'CVE',
			),
			'CZK' => array(
				'title'  => 'CZK',
				'symbol' => 'CZK',
			),
			'DJF' => array(
				'title'  => 'DJF',
				'symbol' => 'DJF',
			),
			'DKK' => array(
				'title'  => 'DKK',
				'symbol' => 'DKK',
			),
			'DOP' => array(
				'title'  => 'DOP',
				'symbol' => 'DOP',
			),
			'DZD' => array(
				'title'  => 'DZD',
				'symbol' => 'DZD',
			),
			'EGP' => array(
				'title'  => 'EGP',
				'symbol' => 'EGP',
			),
			'ETB' => array(
				'title'  => 'ETB',
				'symbol' => 'ETB',
			),
			'EUR' => array(
				'title'  => 'EUR',
				'symbol' => 'EUR',
			),
			'FJD' => array(
				'title'  => 'FJD',
				'symbol' => 'FJD',
			),
			'FKP' => array(
				'title'  => 'FKP',
				'symbol' => 'FKP',
			),
			'GBP' => array(
				'title'  => 'GBP',
				'symbol' => 'GBP',
			),
			'GEL' => array(
				'title'  => 'GEL',
				'symbol' => 'GEL',
			),
			'GIP' => array(
				'title'  => 'GIP',
				'symbol' => 'GIP',
			),
			'GMD' => array(
				'title'  => 'GMD',
				'symbol' => 'GMD',
			),
			'GNF' => array(
				'title'  => 'GNF',
				'symbol' => 'GNF',
			),
			'GTQ' => array(
				'title'  => 'GTQ',
				'symbol' => 'GTQ',
			),
			'GYD' => array(
				'title'  => 'GYD',
				'symbol' => 'GYD',
			),
			'HKD' => array(
				'title'  => 'HKD',
				'symbol' => 'HKD',
			),
			'HNL' => array(
				'title'  => 'HNL',
				'symbol' => 'HNL',
			),
			'HRK' => array(
				'title'  => 'HRK',
				'symbol' => 'HRK',
			),
			'HTG' => array(
				'title'  => 'HTG',
				'symbol' => 'HTG',
			),
			'HUF' => array(
				'title'  => 'HUF',
				'symbol' => 'HUF',
			),
			'IDR' => array(
				'title'  => 'IDR',
				'symbol' => 'IDR',
			),
			'ILS' => array(
				'title'  => 'ILS',
				'symbol' => 'ILS',
			),
			'INR' => array(
				'title'  => 'INR',
				'symbol' => 'INR',
			),
			'ISK' => array(
				'title'  => 'ISK',
				'symbol' => 'ISK',
			),
			'JMD' => array(
				'title'  => 'JMD',
				'symbol' => 'JMD',
			),
			'JPY' => array(
				'title'  => 'JPY',
				'symbol' => 'JPY',
			),
			'KES' => array(
				'title'  => 'KES',
				'symbol' => 'KES',
			),
			'KGS' => array(
				'title'  => 'KGS',
				'symbol' => 'KGS',
			),
			'KHR' => array(
				'title'  => 'KHR',
				'symbol' => 'KHR',
			),
			'KMF' => array(
				'title'  => 'KMF',
				'symbol' => 'KMF',
			),
			'KRW' => array(
				'title'  => 'KRW',
				'symbol' => 'KRW',
			),
			'KYD' => array(
				'title'  => 'KYD',
				'symbol' => 'KYD',
			),
			'KZT' => array(
				'title'  => 'KZT',
				'symbol' => 'KZT',
			),
			'LAK' => array(
				'title'  => 'LAK',
				'symbol' => 'LAK',
			),
			'LBP' => array(
				'title'  => 'LBP',
				'symbol' => 'LBP',
			),
			'LKR' => array(
				'title'  => 'LKR',
				'symbol' => 'LKR',
			),
			'LRD' => array(
				'title'  => 'LRD',
				'symbol' => 'LRD',
			),
			'LSL' => array(
				'title'  => 'LSL',
				'symbol' => 'LSL',
			),
			'MAD' => array(
				'title'  => 'MAD',
				'symbol' => 'MAD',
			),
			'MDL' => array(
				'title'  => 'MDL',
				'symbol' => 'MDL',
			),
			'MGA' => array(
				'title'  => 'MGA',
				'symbol' => 'MGA',
			),
			'MKD' => array(
				'title'  => 'MKD',
				'symbol' => 'MKD',
			),
			'MMK' => array(
				'title'  => 'MMK',
				'symbol' => 'MMK',
			),
			'MNT' => array(
				'title'  => 'MNT',
				'symbol' => 'MNT',
			),
			'MOP' => array(
				'title'  => 'MOP',
				'symbol' => 'MOP',
			),
			'MRO' => array(
				'title'  => 'MRO',
				'symbol' => 'MRO',
			),
			'MUR' => array(
				'title'  => 'MUR',
				'symbol' => 'MUR',
			),
			'MVR' => array(
				'title'  => 'MVR',
				'symbol' => 'MVR',
			),
			'MWK' => array(
				'title'  => 'MWK',
				'symbol' => 'MWK',
			),
			'MXN' => array(
				'title'  => 'MXN',
				'symbol' => 'MXN',
			),
			'MYR' => array(
				'title'  => 'MYR',
				'symbol' => 'MYR',
			),
			'MZN' => array(
				'title'  => 'MZN',
				'symbol' => 'MZN',
			),
			'NAD' => array(
				'title'  => 'NAD',
				'symbol' => 'NAD',
			),
			'NGN' => array(
				'title'  => 'NGN',
				'symbol' => 'NGN',
			),
			'NIO' => array(
				'title'  => 'NIO',
				'symbol' => 'NIO',
			),
			'NOK' => array(
				'title'  => 'NOK',
				'symbol' => 'NOK',
			),
			'NPR' => array(
				'title'  => 'NPR',
				'symbol' => 'NPR',
			),
			'NZD' => array(
				'title'  => 'NZD',
				'symbol' => 'NZD',
			),
			'PAB' => array(
				'title'  => 'PAB',
				'symbol' => 'PAB',
			),
			'PEN' => array(
				'title'  => 'PEN',
				'symbol' => 'PEN',
			),
			'PGK' => array(
				'title'  => 'PGK',
				'symbol' => 'PGK',
			),
			'PHP' => array(
				'title'  => 'PHP',
				'symbol' => 'PHP',
			),
			'PKR' => array(
				'title'  => 'PKR',
				'symbol' => 'PKR',
			),
			'PLN' => array(
				'title'  => 'PLN',
				'symbol' => 'PLN',
			),
			'PYG' => array(
				'title'  => 'PYG',
				'symbol' => 'PYG',
			),
			'QAR' => array(
				'title'  => 'QAR',
				'symbol' => 'QAR',
			),
			'RON' => array(
				'title'  => 'RON',
				'symbol' => 'RON',
			),
			'RSD' => array(
				'title'  => 'RSD',
				'symbol' => 'RSD',
			),
			'RUB' => array(
				'title'  => 'RUB',
				'symbol' => 'RUB',
			),
			'RWF' => array(
				'title'  => 'RWF',
				'symbol' => 'RWF',
			),
			'SAR' => array(
				'title'  => 'SAR',
				'symbol' => 'SAR',
			),
			'SBD' => array(
				'title'  => 'SBD',
				'symbol' => 'SBD',
			),
			'SCR' => array(
				'title'  => 'SCR',
				'symbol' => 'SCR',
			),
			'SEK' => array(
				'title'  => 'SEK',
				'symbol' => 'SEK',
			),
			'SGD' => array(
				'title'  => 'SGD',
				'symbol' => 'SGD',
			),
			'SHP' => array(
				'title'  => 'SHP',
				'symbol' => 'SHP',
			),
			'SLL' => array(
				'title'  => 'SLL',
				'symbol' => 'SLL',
			),
			'SOS' => array(
				'title'  => 'SOS',
				'symbol' => 'SOS',
			),
			'SRD' => array(
				'title'  => 'SRD',
				'symbol' => 'SRD',
			),
			'STD' => array(
				'title'  => 'STD',
				'symbol' => 'STD',
			),
			'SZL' => array(
				'title'  => 'SZL',
				'symbol' => 'SZL',
			),
			'THB' => array(
				'title'  => 'THB',
				'symbol' => 'THB',
			),
			'TJS' => array(
				'title'  => 'TJS',
				'symbol' => 'TJS',
			),
			'TOP' => array(
				'title'  => 'TOP',
				'symbol' => 'TOP',
			),
			'TRY' => array(
				'title'  => 'TRY',
				'symbol' => 'TRY',
			),
			'TTD' => array(
				'title'  => 'TTD',
				'symbol' => 'TTD',
			),
			'TWD' => array(
				'title'  => 'TWD',
				'symbol' => 'TWD',
			),
			'TZS' => array(
				'title'  => 'TZS',
				'symbol' => 'TZS',
			),
			'UAH' => array(
				'title'  => 'UAH',
				'symbol' => 'UAH',
			),
			'UGX' => array(
				'title'  => 'UGX',
				'symbol' => 'UGX',
			),
			'UYU' => array(
				'title'  => 'UYU',
				'symbol' => 'UYU',
			),
			'UZS' => array(
				'title'  => 'UZS',
				'symbol' => 'UZS',
			),
			'VND' => array(
				'title'  => 'VND',
				'symbol' => 'VND',
			),
			'VUV' => array(
				'title'  => 'VUV',
				'symbol' => 'VUV',
			),
			'WST' => array(
				'title'  => 'WST',
				'symbol' => 'WST',
			),
			'XAF' => array(
				'title'  => 'XAF',
				'symbol' => 'XAF',
			),
			'XCD' => array(
				'title'  => 'XCD',
				'symbol' => 'XCD',
			),
			'XOF' => array(
				'title'  => 'XOF',
				'symbol' => 'XOF',
			),
			'XPF' => array(
				'title'  => 'XPF',
				'symbol' => 'XPF',
			),
			'YER' => array(
				'title'  => 'YER',
				'symbol' => 'YER',
			),
			'ZAR' => array(
				'title'  => 'ZAR',
				'symbol' => 'ZAR',
			),
			'ZMW' => array(
				'title'  => 'ZMW',
				'symbol' => 'ZMW',
			),
		);

		return apply_filters( 'wp_quiz_currencies', $currencies );
	}
}
