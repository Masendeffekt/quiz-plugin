<?php
/**
 * Database class
 *
 * @package WPQuiz
 */

namespace WPQuiz;

use WP_Query;

/**
 * Class DB
 */
class Install {

	/**
	 * Current plugin db version.
	 *
	 * @var string
	 */
	protected $db_version = '2.1.4';

	/**
	 * WPDB object.
	 *
	 * @var \wpdb
	 */
	protected $wpdb;

	/**
	 * List of migrate versions and methods.
	 *
	 * @var array
	 */
	protected $migrate_versions = array();

	/**
	 * DB constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb             = $wpdb;
		$this->migrate_versions = array(
			'2.0.0.8' => array( $this, 'migrate_2_0_0' ),
			'2.1.4'   => array( $this, 'migrate_2_1_4' ),
		);
	}

	/**
	 * Installs database.
	 */
	public function install() {
		$old_db_version = get_option( 'wp_quiz_pro_db_version' );
		if ( version_compare( $old_db_version, $this->db_version ) >= 0 ) {
			// You are running latest db version.
			return;
		}

		$this->migrate( $old_db_version );

		update_option( 'wp_quiz_pro_db_version', $this->db_version );
	}

	/**
	 * Creates tables.
	 */
	protected function create_tables() {
		$charset_collate = $this->wpdb->get_charset_collate();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// User actions table.
		$sql = "CREATE TABLE {$this->wpdb->prefix}wp_quiz_user_actions (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			action VARCHAR(255) NOT NULL,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			user_ip VARCHAR(40) NOT NULL,
			time DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id)
		) {$charset_collate};";

		/**
		 * Allows changing user actions table schema.
		 *
		 * @since 2.0.0
		 *
		 * @param string $sql Table schema. See {@see dbDelta()}.
		 */
		$sql = apply_filters( 'wp_quiz_user_actions_table_schema', $sql );
		dbDelta( $sql );

		// Emails table.
		$sql = "CREATE TABLE {$this->wpdb->prefix}wp_quiz_emails (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			pid BIGINT(20) UNSIGNED NOT NULL,
			play_data_id BIGINT(20) UNSIGNED NOT NULL,
			username VARCHAR(255) NOT NULL,
			email VARCHAR(255) NOT NULL,
			time DATETIME NOT NULL,
			consent TINYINT(1) UNSIGNED NULL,
			mail_service VARCHAR(50) NULL,
			PRIMARY KEY  (id),
			KEY pid (pid),
			KEY play_data_id (play_data_id)
		) {$charset_collate};";

		/**
		 * Allows changing emails table schema.
		 *
		 * @since 2.0.0
		 *
		 * @param string $sql Table schema. See {@see dbDelta()}.
		 */
		$sql = apply_filters( 'wp_quiz_emails_table_schema', $sql );
		dbDelta( $sql );

		// Players table.
		$sql = "CREATE TABLE {$this->wpdb->prefix}wp_quiz_players (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			user_ip VARCHAR(50) NOT NULL,
			fb_user_id VARCHAR(20) NOT NULL,
			email VARCHAR(255) NOT NULL,
			first_name VARCHAR(255) NOT NULL DEFAULT '',
			last_name VARCHAR(255) NOT NULL DEFAULT '',
			gender VARCHAR(16) NOT NULL DEFAULT '',
			picture VARCHAR(255) NOT NULL DEFAULT '',
			friends LONGTEXT NOT NULL DEFAULT '',
			type VARCHAR(50) NOT NULL,
			PRIMARY KEY  (id)
		) {$charset_collate};";

		/**
		 * Allows changing players table schema.
		 *
		 * @since 2.0.0
		 *
		 * @param string $sql Table schema. See {@see dbDelta()}.
		 */
		$sql = apply_filters( 'wp_quiz_players_table_schema', $sql );
		dbDelta( $sql );

		// Play data table.
		$sql = "CREATE TABLE {$this->wpdb->prefix}wp_quiz_play_data (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			player_id BIGINT(20) UNSIGNED NOT NULL,
			quiz_id BIGINT(20) UNSIGNED NOT NULL,
			played_at DATETIME NOT NULL,
			correct_answered SMALLINT(5) UNSIGNED NULL,
			result VARCHAR(255) NOT NULL,
			quiz_type VARCHAR(30) NOT NULL,
			quiz_data TEXT NOT NULL,
			answered_data TEXT NOT NULL,
			quiz_url VARCHAR(255) NOT NULL DEFAULT '',
			PRIMARY KEY  (id),
			KEY player_id (player_id),
			KEY quiz_id (quiz_id)
		) {$charset_collate};";

		/**
		 * Allows changing plays table schema.
		 *
		 * @since 2.0.0
		 *
		 * @param string $sql Table schema. See {@see dbDelta()}.
		 */
		$sql = apply_filters( 'wp_quiz_play_data_table_schema', $sql );
		dbDelta( $sql );
	}

	/**
	 * Migrates from the old version.
	 *
	 * @param string $old_db_version Old db version.
	 */
	protected function migrate( $old_db_version ) {
		$this->create_tables();

		// For those use 1.x.x version.
		$old_version = get_option( 'wp_quiz_pro_version' );
		if ( $old_version ) {
			$old_db_version = $old_version;
		}

		if ( ! $old_db_version ) {
			// First time using plugin.
			return;
		}

		foreach ( $this->migrate_versions as $version => $method ) {
			if ( version_compare( $old_db_version, $version ) < 0 ) {
				call_user_func( $method );
				$old_db_version = $version;
			}
		}
	}

	/**
	 * Checks if table exists.
	 *
	 * @param string $table_name Table name.
	 * @return bool
	 */
	protected function check_table_exists( $table_name ) {
		return $this->wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name; // WPCS: unprepared SQL ok.
	}

	/**
	 * Migrates data to version 2.0.0.
	 */
	protected function migrate_2_0_0() {
		$this->migrate_fb_players_2_0_0();
		$this->migrate_players_2_0_0();
		$this->migrate_emails_2_0_0();
		$this->migrate_options_2_0_0();

		add_role( 'wp_quiz_player', __( 'WP Quiz player', 'wp-quiz-pro' ), array() );
	}

	/**
	 * Migrates fb players to 2.0.0 version.
	 */
	protected function migrate_fb_players_2_0_0() {
		if ( ! $this->check_table_exists( $this->wpdb->prefix . 'wp_quiz_fb_users' ) && $this->check_table_exists( $this->wpdb->prefix . 'wp_quiz_fb_users' ) ) {
			return;
		}
		$fb_players = $this->wpdb->get_results( "SELECT * FROM {$this->wpdb->prefix}wp_quiz_fb_users as players INNER JOIN {$this->wpdb->prefix}wp_quiz_fb_plays as plays WHERE players.id = plays.user_id" ); // WPCS: unprepared SQL ok.
		$has_error  = false;
		foreach ( $fb_players as $fb_player ) {
			// Create player record.
			$result = $this->wpdb->insert(
				$this->wpdb->prefix . 'wp_quiz_players',
				array(
					'user_id'    => null,
					'created_at' => date( 'Y-m-d H:i:s', strtotime( $fb_player->created_at ) ),
					'updated_at' => date( 'Y-m-d H:i:s', strtotime( $fb_player->updated_at ) ),
					'user_ip'    => '',
					'fb_user_id' => $fb_player->uid,
					'email'      => $fb_player->email,
					'first_name' => $fb_player->first_name,
					'last_name'  => $fb_player->last_name,
					'gender'     => $fb_player->gender,
					'picture'    => $fb_player->picture,
					'friends'    => $fb_player->friends,
					'type'       => 'fb_user',
				)
			);

			if ( ! $result ) {
				$has_error = true;
				error_log( sprintf( "DB ERROR: %s\n%s", $this->wpdb->last_error, $this->wpdb->last_query ) );
				continue;
			}

			// Create play record.
			$player_id = $this->wpdb->insert_id;
			$result    = $this->wpdb->insert(
				$this->wpdb->prefix . 'wp_quiz_play_data',
				array(
					'player_id'        => $player_id,
					'quiz_id'          => $fb_player->pid,
					'played_at'        => date( 'Y-m-d H:i:s', strtotime( $fb_player->updated_at ) ),
					'correct_answered' => null,
					'result'           => '',
					'quiz_type'        => 'fb_quiz',
					'quiz_data'        => '',
					'answered_data'    => '',
				)
			);
			if ( ! $result ) {
				$has_error = true;
				error_log( sprintf( "DB ERROR: %s\n%s", $this->wpdb->last_error, $this->wpdb->last_query ) );
			}
		} // End foreach() fb_players.

		// Remove tables if there is no error.
		if ( ! $has_error ) {
			$this->wpdb->query( "DROP TABLE IF EXISTS {$this->wpdb->prefix}wp_quiz_fb_users" ); // WPCS: unprepared SQL ok.
			$this->wpdb->query( "DROP TABLE IF EXISTS {$this->wpdb->prefix}wp_quiz_fb_plays" ); // WPCS: unprepared SQL ok.
		}
		unset( $fb_players );
	}

	/**
	 * Migrates players to 2.0.0 version.
	 */
	protected function migrate_players_2_0_0() {
		$players   = $this->wpdb->get_results( "SELECT * FROM {$this->wpdb->prefix}wp_quiz_players WHERE pid > 0" ); // WPCS: unprepared SQL ok.
		$has_error = false;
		foreach ( $players as $player ) {
			// Update player record with new schema.
			$user       = ! empty( $player->username ) ? get_user_by( 'login', $player->username ) : false;
			$created_at = date( 'Y-m-d H:i:s', strtotime( $player->date ) );
			$result     = $this->wpdb->update(
				$this->wpdb->prefix . 'wp_quiz_players',
				array(
					'type'       => $user ? 'user' : 'guest',
					'user_id'    => $user ? $user->ID : null,
					'pid'        => 0,
					'created_at' => $created_at,
					'updated_at' => $created_at,
				),
				array( 'id' => $player->id )
			);
			if ( ! $result ) {
				$has_error = true;
				error_log( sprintf( "DB ERROR: %s\n%s", $this->wpdb->last_error, $this->wpdb->last_query ) );
			}

			// Create play record.
			$result = $this->wpdb->insert(
				$this->wpdb->prefix . 'wp_quiz_play_data',
				array(
					'player_id'        => $player->id,
					'quiz_id'          => $player->pid,
					'played_at'        => date( 'Y-m-d H:i:s', strtotime( $player->date ) ),
					'correct_answered' => $player->correct_answered,
					'result'           => $player->result,
					'quiz_type'        => $player->quiz_type,
					'quiz_data'        => '',
					'answered_data'    => '',
				)
			);
			if ( ! $result ) {
				$has_error = true;
				error_log( sprintf( "DB ERROR: %s\n%s", $this->wpdb->last_error, $this->wpdb->last_query ) );
			}
		} // End foreach() players.

		// Remove columns if there is no error.
		if ( ! $has_error ) {
			$this->wpdb->query( "ALTER TABLE {$this->wpdb->prefix}wp_quiz_players DROP COLUMN `pid`" ); // WPCS: unprepared SQL ok.
			$this->wpdb->query( "ALTER TABLE {$this->wpdb->prefix}wp_quiz_players DROP COLUMN `date`" ); // WPCS: unprepared SQL ok.
			$this->wpdb->query( "ALTER TABLE {$this->wpdb->prefix}wp_quiz_players DROP COLUMN `username`" ); // WPCS: unprepared SQL ok.
			$this->wpdb->query( "ALTER TABLE {$this->wpdb->prefix}wp_quiz_players DROP COLUMN `correct_answered`" ); // WPCS: unprepared SQL ok.
			$this->wpdb->query( "ALTER TABLE {$this->wpdb->prefix}wp_quiz_players DROP COLUMN `result`" ); // WPCS: unprepared SQL ok.
			$this->wpdb->query( "ALTER TABLE {$this->wpdb->prefix}wp_quiz_players DROP COLUMN `quiz_type`" ); // WPCS: unprepared SQL ok.
		}

		unset( $players );
	}

	/**
	 * Migrates emails to 2.0.0 version.
	 */
	protected function migrate_emails_2_0_0() {
		$emails    = $this->wpdb->get_results( "SELECT * FROM {$this->wpdb->prefix}wp_quiz_emails WHERE time != '0000-00-00 00:00:00'" ); // WPCS: unprepared SQL ok.
		$has_error = false;
		foreach ( $emails as $email ) {
			$result = $this->wpdb->update(
				$this->wpdb->prefix . 'wp_quiz_emails',
				array( 'time' => date( 'Y-m-d H:i:s', strtotime( $email->date ) ) ),
				array( 'id' => $email->id )
			);
			if ( ! $result ) {
				$has_error = true;
				error_log( sprintf( "DB ERROR: %s\n%s", $this->wpdb->last_error, $this->wpdb->last_query ) );
			}
		} // End foreach() emails.

		// Remove columns if there is no error.
		if ( ! $has_error ) {
			$this->wpdb->query( "ALTER TABLE {$this->wpdb->prefix}wp_quiz_emails DROP COLUMN `date`" ); // WPCS: unprepared SQL ok.
		}

		unset( $emails );
	}

	/**
	 * Migrates options to version 2.0.0.
	 */
	protected function migrate_options_2_0_0() {
		$options = get_option( 'wp_quiz_pro_default_settings' );
		if ( ! empty( $options['analytics'] ) && is_array( $options['analytics'] ) ) {
			foreach ( $options['analytics'] as $key => $value ) {
				$options[ 'ga_' . $key ] = $value;
			}
			unset( $options['analytics'] );
		}

		if ( ! empty( $options['defaults'] ) && is_array( $options['defaults'] ) ) {
			foreach ( $options as $key => $value ) {
				if ( in_array( $key, array( 'rand_questions', 'rand_answers', 'restart_questions', 'promote_plugin', 'embed_toggle', 'show_ads', 'auto_scroll', 'share_meta', 'repeat_ads' ) ) ) {
					$value = intval( $value ) ? 'on' : 'off';
				}
				$options[ $key ] = $value;
			}
			unset( $options['defaults'] );
		}

		if ( ! empty( $options['mail_service'] ) ) {
			switch ( $options['mail_service'] ) {
				case 1:
					$options['mail_service'] = 'mailchimp';
					break;

				case 2:
					$options['mail_service'] = 'getresponse';
					break;

				case 3:
					$options['mail_service'] = 'aweber';
					break;
			}
		}

		foreach ( array( 'mailchimp', 'getresponse' ) as $option_name ) {
			if ( empty( $options[ $option_name ] ) || ! is_array( $options[ $option_name ] ) ) {
				continue;
			}
			foreach ( $options[ $option_name ] as $key => $value ) {
				$options[ $option_name . '_' . $key ] = $value;
			}
			unset( $options[ $option_name ] );
		}

		$options['ad_codes']         = ! empty( $options['ad_codes'] ) ? explode( ',', $options['ad_codes'] ) : array();
		$options['players_tracking'] = ! empty( $options['players_tracking'] ) && intval( $options['players_tracking'] ) ? 'on' : 'off';

		// Add dummy value for new options.
		$new_options = array(
			'record_guest_method',
			'allow_user_multi_votes',
			'subscribe_box_user_consent',
			'subscribe_box_user_consent_desc',
			'continue_as_btn',
			'css_footer',
			'ga_event_tracking',
			'ga_event_category',
			'ga_event_action',
			'ga_event_label',
		);

		$quiz_types = QuizTypeManager::get_all( true );
		foreach ( $quiz_types as $key => $quiz_type ) {
			$new_options[] = 'enable_' . $key;
		}

		$defaults = Helper::get_default_options();
		foreach ( $new_options as $key ) {
			if ( isset( $options[ $key ] ) || ! isset( $defaults[ $key ] ) ) {
				continue;
			}
			$options[ $key ] = $defaults[ $key ];
		}

		update_option( 'wp_quiz_pro_default_settings', $options );
		unset( $options );
	}

	protected function migrate_2_1_4() {
		global $wpdb;
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}wp_quiz_play_data MODIFY quiz_data LONGTEXT NOT NULL;" );
	}
}
