<?php
/**
 * GDPR compliant
 *
 * @package WPQuiz
 */

namespace WPQuiz;

/**
 * Class GDPR
 */
class GDPR {

	/**
	 * Class initialize.
	 */
	public function init() {
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_data_exporter' ), 10 );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_data_eraser' ), 10 );
	}

	/**
	 * Registers data exporter.
	 *
	 * @param  array $exporters Exporters.
	 * @return array
	 */
	public function register_data_exporter( $exporters ) {
		$exporters[] = array(
			'exporter_friendly_name' => apply_filters( 'wp_quiz_exporter_friendly_name', __( 'WP Quiz Players', 'wp-quiz-pro' ) ),
			'callback'               => array( $this, 'players_data_exporter' ),
		);
		$exporters[] = array(
			'exporter_friendly_name' => apply_filters( 'wp_quiz_subscribers_exporter_friendly_name', __( 'WP Quiz Subscribers', 'wp-quiz-pro' ) ),
			'callback'               => array( $this, 'subscribers_data_exporter' ),
		);
		return $exporters;
	}

	/**
	 * Players data exporter callback.
	 *
	 * @param string $email Email address.
	 * @param int    $page  Page number.
	 * @return array
	 */
	public function players_data_exporter( $email, $page = 1 ) {
		global $wpdb;
		$number      = 100;
		$page        = (int) $page;
		$export_data = array();

		$user    = get_user_by( 'email', $email );
		$user_id = isset( $user->ID ) ? $user->ID : null;
		$records = $wpdb->get_results(
			$wpdb->prepare(
				"
SELECT * FROM {$wpdb->prefix}wp_quiz_players as players
WHERE players.email = %s
OR (SELECT user_email FROM {$wpdb->users} as users WHERE users.ID = %d) = %s
",
				$email,
				$user_id,
				$email
			)
		); // WPCS: db call ok, cache ok.

		foreach ( $records as $index => $record ) {
			$data      = array();
			$quiz_data = ! empty( $record->quiz_data ) ? json_decode( $record->quiz_data, true ) : array();

			if ( ! empty( $record->created_at ) ) {
				$data[] = array(
					'name'  => __( 'Created date', 'wp-quiz-pro' ),
					'value' => $record->created_at,
				);
			}

			if ( ! empty( $record->updated_at ) ) {
				$data[] = array(
					'name'  => __( 'Updated date', 'wp-quiz-pro' ),
					'value' => $record->updated_at,
				);
			}

			if ( ! empty( $record->user_ip ) ) {
				$data[] = array(
					'name'  => __( 'User IP', 'wp-quiz-pro' ),
					'value' => $record->user_ip,
				);
			}

			if ( ! empty( $record->fb_user_id ) ) {
				$data[] = array(
					'name'  => __( 'Facebook ID', 'wp-quiz-pro' ),
					'value' => $record->fb_user_id,
				);
			}

			if ( ! empty( $record->fb_email ) ) {
				$data[] = array(
					'name'  => __( 'Facebook email', 'wp-quiz-pro' ),
					'value' => $record->fb_email,
				);
			}

			if ( ! empty( $record->first_name ) ) {
				$data[] = array(
					'name'  => __( 'Facebook first name', 'wp-quiz-pro' ),
					'value' => $record->first_name,
				);
			}

			if ( ! empty( $record->last_name ) ) {
				$data[] = array(
					'name'  => __( 'Facebook last name', 'wp-quiz-pro' ),
					'value' => $record->last_name,
				);
			}

			if ( ! empty( $record->gender ) ) {
				$data[] = array(
					'name'  => __( 'Facebook gender', 'wp-quiz-pro' ),
					'value' => $record->gender,
				);
			}

			if ( ! empty( $record->picture ) ) {
				$data[] = array(
					'name'  => __( 'Facebook avatar', 'wp-quiz-pro' ),
					'value' => $record->picture,
				);
			}

			if ( ! empty( $record->friends ) ) {
				$friends_html = '';
				$friends      = json_decode( $record->friends );
				if ( $friends ) {
					foreach ( $friends as $friend ) {
						$friends_html .= sprintf(
							'%1$s %2$s | %3$s %4$s<br>',
							__( 'ID:', 'wp-quiz-pro' ),
							$friend->id,
							__( 'Name:', 'wp-quiz-pro' ),
							$friend->name
						);
					}
				}

				$data[] = array(
					'name'  => __( 'Facebook friends', 'wp-quiz-pro' ),
					'value' => $friends_html,
				);
			}

			if ( ! empty( $record->quiz_id ) ) {
				$data[] = array(
					'name'  => __( 'Quiz name', 'wp-quiz-pro' ),
					'value' => get_the_title( $record->quiz_id ),
				);
			}

			if ( ! empty( $record->quiz_type ) ) {
				$data[] = array(
					'name'  => __( 'Quiz type', 'wp-quiz-pro' ),
					'value' => $record->quiz_type,
				);
			}

			if ( ! empty( $record->played_at ) ) {
				$data[] = array(
					'name'  => __( 'Played at', 'wp-quiz-pro' ),
					'value' => $record->played_at,
				);
			}

			if ( ! empty( $record->correct_answered ) ) {
				$data[] = array(
					'name'  => __( 'Number of correct answers', 'wp-quiz-pro' ),
					'value' => $record->correct_answered,
				);
			}

			if ( ! empty( $record->answered_data ) && 'fb_quiz' !== $record->quiz_type ) {
				$data[] = array(
					'name'  => __( 'Answered', 'wp-quiz-pro' ),
					'value' => $record->answered_data,
				);
			}

			if ( ! empty( $record->result ) ) {
				if ( 'fb_quiz' === $record->quiz_type ) {
					$result = ! empty( $record->answered_data ) ? Helper::get_fb_result_image_url( $record->answered_data ) : '';
				} elseif ( $quiz_data && ! empty( $quiz_data['results'][ $record->result ]['title'] ) ) {
					$result = $quiz_data['results'][ $record->result ]['title'];
				} else {
					$result = $record->result;
				}

				$data[] = array(
					'name'  => __( 'Result', 'wp-quiz-pro' ),
					'value' => $result,
				);
			}

			$index_all     = $number * ( $page - 1 ) + $index + 1;
			$export_data[] = array(
				'group_id'    => 'wp_quiz_data_' . $index_all,
				// translators: player index.
				'group_label' => apply_filters( 'wp_quiz_data_group_label', sprintf( __( 'Player #%d', 'wp-quiz-pro' ), $index_all ) ),
				'item_id'     => 'wp_quiz_info_' . $index_all,
				'data'        => $data,
			);
		}

		$done = count( $records ) < $number;

		return array(
			'data' => $export_data,
			'done' => $done,
		);
	}

	/**
	 * Subscribers data exporter callback.
	 *
	 * @param string $email Email address.
	 * @param int    $page  Page number.
	 * @return array
	 */
	public function subscribers_data_exporter( $email, $page = 1 ) {
		global $wpdb;
		$number      = 100;
		$page        = (int) $page;
		$export_data = array();

		$records = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}wp_quiz_emails WHERE email = %s",
				$email
			)
		); // WPCS db call ok, cache ok.

		foreach ( $records as $index => $record ) {
			$data = array();

			if ( ! empty( $record->email ) ) {
				$data[] = array(
					'name'  => __( 'Subscribe email', 'wp-quiz-pro' ),
					'value' => $record->email,
				);
			}

			if ( ! empty( $record->username ) ) {
				$data[] = array(
					'name'  => __( 'Subscribe name', 'wp-quiz-pro' ),
					'value' => $record->username,
				);
			}

			if ( ! empty( $record->time ) ) {
				$data[] = array(
					'name'  => __( 'Subscribe at', 'wp-quiz-pro' ),
					'value' => $record->time,
				);
			}

			if ( ! empty( $record->consent ) ) {
				$data[] = array(
					'name'  => __( 'Consent', 'wp-quiz-pro' ),
					'value' => $record->consent,
				);
			}

			if ( ! empty( $record->mail_service ) ) {
				$data[] = array(
					'name'  => __( 'Mail service', 'wp-quiz-pro' ),
					'value' => $record->mail_service,
				);
			}

			$index_all     = $number * ( $page - 1 ) + $index + 1;
			$export_data[] = array(
				'group_id'    => 'wp_quiz_subscribers_data_' . $index_all,
				// translators: subscriber index.
				'group_label' => apply_filters( 'wp_quiz_subscribers_data_group_label', sprintf( __( 'Subscriber #%d', 'wp-quiz-pro' ), $index_all ) ),
				'item_id'     => 'wp_quiz_subscribers_info_' . $index_all,
				'data'        => $data,
			);
		}

		$done = count( $records ) < $number;

		return array(
			'data' => $export_data,
			'done' => $done,
		);
	}

	/**
	 * Registers data erasers.
	 *
	 * @param array $erasers Data erasers.
	 *
	 * @return array
	 */
	public function register_data_eraser( $erasers ) {
		$erasers[] = array(
			'eraser_friendly_name' => apply_filters( 'wpq_eraser_friendly_name', __( 'WP Quiz', 'wp-quiz-pro' ) ),
			'callback'             => array( $this, 'data_eraser' ),
		);
		return $erasers;
	}

	/**
	 * Erases data.
	 *
	 * @param string $email Email address.
	 * @param int    $page  Current page.
	 * @return array
	 */
	public function data_eraser( $email, $page = 1 ) {
		global $wpdb;

		$number = 100;
		$page   = (int) $page;

		$default_eraser_data = array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
		if ( empty( $email ) ) {
			return $default_eraser_data;
		}

		$items_removed  = false;
		$items_retained = false;
		$messages       = array();

		$user    = get_user_by( 'email', $email );
		$user_id = isset( $user->ID ) ? $user->ID : null;
		$records = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *, players.email AS fb_email, emails.id AS email_id, players.id AS player_id
				FROM {$wpdb->prefix}wp_quiz_players AS players
				LEFT JOIN {$wpdb->prefix}wp_quiz_play_data AS plays ON plays.player_id = players.id
				LEFT JOIN {$wpdb->prefix}wp_quiz_emails AS emails ON emails.play_data_id = plays.id
				WHERE players.user_id = %d OR players.email = %s OR emails.email = %s
				LIMIT %d OFFSET %d",
				$user_id,
				$email,
				$email,
				$number,
				( $page - 1 ) * $number
			)
		); // WPCS: db call ok, cache ok.

		foreach ( $records as $record ) {
			$update_data = array();

			if ( ! empty( $record->email ) ) {
				$update_data['email'] = wp_privacy_anonymize_data( 'email', $record->email );
			}

			if ( ! empty( $record->username ) ) {
				$update_data['username'] = wp_privacy_anonymize_data( 'text', $record->username );
			}

			if ( $update_data ) {
				$updated = $wpdb->update(
					"{$wpdb->prefix}wp_quiz_emails",
					$update_data,
					array( 'id' => $record->email_id )
				); // WPCS: db call ok, cache ok.

				if ( $updated ) {
					$items_removed = true;
					// translators: email address.
					$messages[] = sprintf( __( 'Removed subscriber %s', 'wp-quiz-pro' ), $record->email );
				} else {
					$items_retained = true;
					// translators: email address.
					$messages[] = sprintf( __( 'Email %s was unable to be removed at this time.', 'wp-quiz-pro' ), $record->email );
				}
			}

			$update_data = array();
			if ( ! empty( $record->user_ip ) ) {
				$update_data['user_ip'] = wp_privacy_anonymize_data( 'ip', $record->user_ip );
			}

			if ( ! empty( $record->fb_user_id ) ) {
				$update_data['fb_user_id'] = wp_privacy_anonymize_data( 'text', $record->fb_user_id );
			}

			if ( ! empty( $record->fb_email ) ) {
				$update_data['email'] = wp_privacy_anonymize_data( 'email', $record->fb_email );
			}

			if ( ! empty( $record->picture ) ) {
				$update_data['picture'] = wp_privacy_anonymize_data( 'text', $record->picture );
			}

			if ( ! empty( $record->friends ) ) {
				$update_data['friends'] = wp_privacy_anonymize_data( 'text', $record->friends );
			}

			if ( $update_data ) {
				$updated = $wpdb->update(
					"{$wpdb->prefix}wp_quiz_players",
					$update_data,
					array( 'id' => $record->player_id )
				); // WPCS: db call ok, cache ok.

				if ( $updated ) {
					$items_removed = true;
					// translators: player ID.
					$messages[] = sprintf( __( 'Removed player #%s', 'wp-quiz-pro' ), $record->player_id );
				} else {
					$items_retained = true;
					// translators: player ID.
					$messages[] = sprintf( __( 'Player #%s was unable to be removed at this time.', 'wp-quiz-pro' ), $record->player_id );
				}
			}
		}

		$done = count( $records ) < $number;

		if ( ! empty( $messages ) ) {
			return array(
				'items_removed'  => $items_removed,
				'items_retained' => $items_retained,
				'messages'       => $messages,
				'done'           => $done,
			);
		}

		return $default_eraser_data;
	}
}
