<?php
/**
 * Facebook quiz type
 *
 * @package WPQuizPro
 */

namespace WPQuiz\QuizTypes;

use WP_Error;
use WPQuiz\Helper;
use WPQuiz\MetaTags;
use WPQuiz\Modules\PlayerTracking\Player;
use WPQuiz\PlayDataTracking\PlayData;
use WPQuiz\PostTypeQuiz;
use WPQuiz\Quiz;
use WPQuiz\QuizType;
use WPQuiz\Admin\AdminHelper;
use WPQuiz\Template;

/**
 * Class FBQuiz
 */
class FBQuiz extends QuizType {

	/**
	 * Has answers or not.
	 *
	 * @var bool
	 */
	protected $has_answers = false;

	/**
	 * Quiz type icon class.
	 *
	 * @var string
	 */
	protected $icon = 'dashicons dashicons-facebook';

	/**
	 * FBQuiz constructor.
	 */
	public function __construct() {
		$this->name = 'fb_quiz';
		$this->desc = __( 'Create incredibly engaging quizzes which require very less effort on the user\'s part and always gets great engagement and shares.', 'wp-quiz-pro' );
		add_action( 'load-post.php', array( $this, 'preprocess_quiz' ) );
	}

	/**
	 * Preprocesses quiz.
	 */
	public function preprocess_quiz() {
		$screen = get_current_screen();
		if ( PostTypeQuiz::get_name() !== $screen->id ) {
			return;
		}
		if ( ! isset( $_GET['post'] ) ) {
			return;
		}
		$post_id = $_GET['post']; // WPCS: sanitization ok.
		$quiz    = PostTypeQuiz::get_quiz( $post_id );

		if ( $quiz->get_quiz_type()->get_name() !== $this->get_name() ) {
			return;
		}

		$questions = $quiz->get_quiz_type()->get_questions( $quiz );
		$question  = reset( $questions );
		if ( ! isset( $question['profile'] ) && $quiz->get_setting( 'profile' ) ) {
			$question['profile'] = $quiz->get_setting( 'profile' );
			delete_post_meta( $quiz->get_id(), 'wp_quiz_profile' );
			$questions[ $question['id'] ] = $question;
			$quiz->update_questions( $questions );
		}
	}

	/**
	 * Gets quiz type title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Facebook quiz', 'wp-quiz-pro' );
	}

	/**
	 * Gets quiz default settings.
	 *
	 * @return array
	 */
	public function get_default_settings() {
		return array(
			'font_color'   => '#444',
			'title_color'  => '#444',
			'title_size'   => 16,
			'title_weight' => 700,
			'title_style'  => 'normal',
			'profile'      => 'user',
		);
	}

	/**
	 * Gets default data for question.
	 *
	 * @return array
	 */
	public function get_default_question() {
		return array(
			'id'          => '',
			'title'       => '',
			'image'       => '',
			'imageCredit' => '',
			'imageId'     => '',
			'desc'        => '',
		);
	}

	/**
	 * Gets default data for result.
	 *
	 * @return array
	 */
	public function get_default_result() {
		return array(
			'id'              => '',
			'title'           => '',
			'desc'            => '',
			'imageRadius'     => 0,
			'image'           => '',
			'imageId'         => '',
			'pos_x'           => 40,
			'pos_y'           => 40,
			'proImageWidth'   => 150,
			'proImageHeight'  => 150,
			'pos_title_x'     => 40,
			'pos_title_y'     => 200,
			'titleImageWidth' => 150,
		);
	}

	/**
	 * Enqueues backend scripts.
	 */
	public function enqueue_backend_scripts() {
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( 'jquery-ui-slider' );
		parent::enqueue_backend_scripts();
	}

	/**
	 * Gets quiz questions.
	 *
	 * @param Quiz $quiz Quiz object.
	 * @return array
	 */
	protected function get_questions( Quiz $quiz ) {
		$questions = parent::get_questions( $quiz );
		if ( ! $questions ) {
			// Generate an empty question.
			$question       = $this->get_default_question();
			$rand           = Helper::generate_random_string();
			$question['id'] = $rand;
			return array(
				$rand => $question,
			);
		}
		return $questions;
	}

	/**
	 * Prints backend questions list.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_questions_list( Quiz $quiz ) {
		?>
		<div class="wp-quiz-questions wp-quiz-<?php echo esc_attr( $this->name ); ?>-questions">
			<div class="wp-quiz-questions-list"></div><!-- End .wp-quiz-questions-list -->
		</div><!-- End .wp-quiz-questions -->
		<?php
	}

	/**
	 * Prints question js template for backend.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_question_js_template( Quiz $quiz ) {
		/**
		 * Template variables:
		 *
		 * @type Object question
		 * @type String baseName
		 * @type Number index
		 * @type Object i18n
		 */
		?>
		<div class="wp-quiz-question-heading">
			<textarea class="widefat wp-quiz-question-title" name="{{ baseName }}[title]" placeholder="<?php esc_attr_e( 'Question title?', 'wp-quiz-pro' ); ?>" rows="1" data-autoresize>{{ data.question.title }}</textarea>
		</div><!-- End .wp-quiz-question-heading -->

		<div class="wp-quiz-question-content">

			<div class="wp-quiz-question-image">

				<div class="wp-quiz-image-upload style-overlay {{ data.question.image ? '' : 'no-image' }}" data-edit-text="{{ data.i18n.editImage }}" data-upload-text="{{ data.i18n.uploadImage }}">
					<div class="wp-quiz-image-upload-preview">
						<# if (data.question.image) { #>
						<img src="{{ data.question.image }}" alt="">
						<# } #>
					</div><!-- End .wp-quiz-image-upload-preview -->

					<button type="button" class="wp-quiz-image-upload-btn">
						{{ data.question.image ? data.i18n.editImage : data.i18n.uploadImage }}
					</button>

					<?php $this->backend_remove_image_btn( __( 'Remove Image', 'wp-quiz-pro' ) ); ?>

					<input type="text" class="wp-quiz-image-upload-credit" name="{{ baseName }}[imageCredit]" placeholder="<?php esc_attr_e( 'Credit', 'wp-quiz-pro' ); ?>" value="{{ data.question.imageCredit }}">

					<input type="hidden" class="wp-quiz-image-upload-url" name="{{ baseName }}[image]" value="{{ data.question.image }}">
					<input type="hidden" class="wp-quiz-image-upload-id" name="{{ baseName }}[imageId]" value="{{ data.question.imageId }}">
				</div><!-- End .wp-quiz-image-upload -->

			</div><!-- End .wp-quiz-question-image -->

			<p>
				<label for="wp-quiz-question-desc"><strong><?php esc_html_e( 'Description', 'wp-quiz-pro' ); ?></strong></label>
				<textarea name="{{ baseName }}[desc]" id="wp-quiz-question-desc" class="widefat wp-quiz-question-desc" rows="3">{{ data.question.desc }}</textarea>
			</p>

			<div id="wp-quiz-question-profile-notice" {{{ 'friend' !== data.question.profile ? 'style="display: none;"' : '' }}}>
			<?php Template::notice( 'Your Facebook App will be able to access only friends who already authenticated this app. (Played any FB Quiz on your website)', 'warning', true, true ); ?>
		</div>

		<p>
			<label for="wp-quiz-question-profile"><strong><?php esc_html_e( 'Select profile', 'wp-quiz-pro' ); ?></strong></label><br>
			<select id="wp-quiz-question-profile" name="{{ baseName }}[profile]">
				<option value="user" {{ 'user' === data.question.profile ? 'selected' : '' }}><?php esc_html_e( 'User profile image', 'wp-quiz-pro' ); ?></option>
				<option value="friend" {{ 'friend' === data.question.profile ? 'selected' : '' }}><?php esc_html_e( 'Friends profile image', 'wp-quiz-pro' ); ?></option>
			</select>
		</p>

		</div><!-- End .wp-quiz-question-content -->
		<?php
	}

	/**
	 * Prints result js template for backend.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	protected function backend_result_js_template( Quiz $quiz ) {
		$tooltip_text = __( 'Possible name substiution (%%userfirstname%% = user first name, %%userlastname%% = user last name, %%friendfirstname%% = friend first name, %%friendlastname%% = friend last name)', 'wp-quiz-pro' );
		$desc_text    = __( 'Possible name substiution: <code>%%userfirstname%%</code>, <code>%%userlastname%%</code>, <code>%%friendfirstname%%</code>, <code>%%friendlastname%%</code>', 'wp-quiz-pro' );
		?>
		<#
		var elStyle = [
		'--image-radius:' + data.result.imageRadius + 'px',
		'--image-pos-x:' + data.result.pos_x + 'px',
		'--image-pos-y:' + data.result.pos_y + 'px',
		'--title-pos-x:' + data.result.pos_title_x + 'px',
		'--title-pos-y:' + data.result.pos_title_y + 'px',
		];
		if ( parseInt( data.result.proImageWidth ) ) {
		elStyle.push( '--image-width: ' + data.result.proImageWidth + 'px' )
		}
		if ( parseInt( data.result.proImageHeight ) ) {
		elStyle.push( '--image-height: ' + data.result.proImageHeight + 'px' )
		}
		if ( parseInt( data.result.titleImageWidth ) ) {
		elStyle.push( '--title-width: ' + data.result.titleImageWidth + 'px' )
		}
		#>
		<input type="hidden" class="wp-quiz-profile-border-radius-input" name="{{ baseName }}[imageRadius]" value="{{ data.result.imageRadius }}">
		<input type="hidden" class="wp-quiz-profile-image-width-input" name="{{ baseName }}[proImageWidth]" value="{{ data.result.proImageWidth }}">
		<input type="hidden" class="wp-quiz-profile-image-height-input" name="{{ baseName }}[proImageHeight]" value="{{ data.result.proImageHeight }}">
		<input type="hidden" class="wp-quiz-profile-image-pos-x-input" name="{{ baseName }}[pos_x]" value="{{ data.result.pos_x }}">
		<input type="hidden" class="wp-quiz-profile-image-pos-y-input" name="{{ baseName }}[pos_y]" value="{{ data.result.pos_y }}">
		<input type="hidden" class="wp-quiz-profile-title-width-input" name="{{ baseName }}[titleImageWidth]" value="{{ data.result.titleImageWidth }}">
		<input type="hidden" class="wp-quiz-profile-title-pos-x-input" name="{{ baseName }}[pos_title_x]" value="{{ data.result.pos_title_x }}">
		<input type="hidden" class="wp-quiz-profile-title-pos-y-input" name="{{ baseName }}[pos_title_y]" value="{{ data.result.pos_title_y }}">

		<div class="wp-quiz-result-preview">
			<# if ( data.result.image ) { #>
			<img class="wp-quiz-result-preview-image" src="{{ data.result.image }}">
			<# } #>
			<p style="margin-top: 0">
				<strong><?php esc_html_e( 'Title:', 'wp-quiz-pro' ); ?></strong>
				<span class="wp-quiz-result-title-text">{{ data.result.title }}</span>
			</p>
			<p>
				<strong><?php esc_html_e( 'Description:', 'wp-quiz-pro' ); ?></strong>
				<span class="wp-quiz-result-desc-text">{{ data.result.desc }}</span>
			</p>
			<button type="button" class="button wp-quiz-result-edit-button"><?php esc_html_e( 'Edit result', 'wp-quiz-pro' ); ?></button>
			<button type="button" class="button wp-quiz-remove-result-btn"><?php esc_html_e( 'Remove', 'wp-quiz-pro' ); ?></button>
		</div>

		<div class="wp-quiz-result-popup">
			<div class="wp-quiz-result-popup-content">
				<div class="wp-quiz-result-image wp-quiz-image-upload wp-quiz-fb-quiz-canvas {{ data.result.image ? '' : 'no-image' }}" style="{{ elStyle.join(';') }}">
					<div class="wp-quiz-image-upload-preview">
						<# if (data.result.image) { #>
						<img src="{{ data.result.image }}" alt="">
						<# } #>
					</div><!-- End .wp-quiz-image-upload-preview -->

					<button type="button" class="button button-hero wp-quiz-image-upload-btn">{{ data.i18n.uploadImage }}</button>

					<div class="wp-quiz-profile-avatar">
						<img src="<?php echo esc_url( wp_quiz()->admin_assets() . 'images/avatar.jpg' ); ?>" alt="">
					</div>

					<div class="wp-quiz-profile-title"><?php esc_html_e( 'Title Text', 'wp-quiz-pro' ); ?></div>

					<div class="wp-quiz-profile-border-radius">
						<div class="wp-quiz-profile-border-radius-text">{{ data.result.imageRadius }}px</div>
						<div class="wp-quiz-profile-border-radius-control"></div>
					</div>

					<div class="wp-quiz-image-top-left-control">
						<a href="#" class="wp-quiz-image-upload-remove-btn"><?php esc_html_e( 'Remove', 'wp-quiz-pro' ); ?></a>
						|
						<a href="#" class="wp-quiz-image-upload-btn"><?php esc_html_e( 'Change Image', 'wp-quiz-pro' ); ?></a>
					</div>

					<input type="hidden" class="wp-quiz-image-upload-url" name="{{ baseName }}[image]" value="{{ data.result.image }}">
					<input type="hidden" class="wp-quiz-image-upload-id" name="{{ baseName }}[imageId]" value="{{ data.result.imageId }}">

					<# if ( data.result.extra_profiles ) { #>
						<# for ( var i = 0; i < data.result.extra_profiles.length; i++ ) { #>
							<#
							var values = data.result.extra_profiles[ i ].split( ',' );
							var imageStyles = [
							'--image-radius: ' + values[2] + 'px',
							'--image-pos-x: ' + values[3] + 'px',
							'--image-pos-y: ' + values[4] + 'px',
							'--image-width: ' + values[0] + 'px',
							'--image-height: ' + values[1] + 'px'
							];
							var titleStyles = [
							'--title-pos-x: ' + values[6] + 'px',
							'--title-pos-y: ' + values[7] + 'px',
							'--title-width: ' + values[5] + 'px'
							];
							#>

							<div class="wp-quiz-profile-avatar wp-quiz-extra-profile-avatar" style="{{ imageStyles.join( ';' ) }}">
								<img src="<?php echo esc_url( wp_quiz()->admin_assets() . 'images/avatar.jpg' ); ?>" alt="">
								<button type="button" class="wp-quiz-remove-profile-btn">&times;</button>
							</div>
							<div class="wp-quiz-profile-title wp-quiz-extra-profile-title" style="{{ titleStyles.join( ';' ) }}"><?php esc_html_e( 'Title Text', 'wp-quiz-pro' ); ?></div>
							<input type="hidden" class="wp-quiz-extra-profile-value" name="{{ baseName }}[extra_profiles][]" value="{{ data.result.extra_profiles[ i ] }}">

						<# } #>
					<# } #>
				</div><!-- End .wp-quiz-image-upload -->

				<button
					type="button"
					class="button wp-quiz-add-profile-button {{ 'friend' === data.question.profile ? '' : 'hidden' }}"
					data-base-name="{{ baseName }}"
					data-profile-image-url="<?php echo esc_url( wp_quiz()->admin_assets() . 'images/avatar.jpg' ); ?>"
					data-profile-title="<?php esc_attr_e( 'Title Text', 'wp-quiz-pro' ); ?>"
				>
					<?php esc_html_e( 'Add more Profile', 'wp-quiz-pro' ); ?>
				</button>

				<p>
					<label for="wp-quiz-result-title-{{ data.result.id }}">
						<strong><?php esc_html_e( 'Result Title', 'wp-quiz-pro' ); ?></strong>
						<?php AdminHelper::tooltip( '<span class="dashicons dashicons-info">', $tooltip_text ); ?>
					</label>
					<input type="text" id="wp-quiz-result-title-{{ data.result.id }}" class="widefat wp-quiz-result-title" name="{{ baseName }}[title]" placeholder="<?php esc_attr_e( 'Result title', 'wp-quiz-pro' ); ?>" value="{{ data.result.title }}">
					<span class="description"><?php echo wp_kses_post( $desc_text ); ?></span>
				</p>

				<p style="margin-bottom: 0">
					<label for="wp-quiz-result-desc-{{ data.result.id }}">
						<strong><?php esc_html_e( 'Description', 'wp-quiz-pro' ); ?></strong>
						<?php AdminHelper::tooltip( '<span class="dashicons dashicons-info">', $tooltip_text ); ?>
					</label>
					<textarea id="wp-quiz-result-desc-{{ data.result.id }}" class="widefat wp-quiz-result-desc" name="{{ baseName }}[desc]" placeholder="<?php esc_attr_e( 'Description', 'wp-quiz-pro' ); ?>" rows="3">{{ data.result.desc }}</textarea>
					<span class="description"><?php echo wp_kses_post( $desc_text ); ?></span>
				</p>

				<button type="button" class="wp-quiz-result-close-popup-button">&times;</button>
			</div>
		</div><!-- End .wp-quiz-result-popup -->
		<?php
	}

	/**
	 * Shows player tracking detail.
	 *
	 * @param PlayData $play_data Tracking data.
	 * @param bool     $no_result Not show the result.
	 */
	public function show_tracking_data( PlayData $play_data, $no_result = false ) {
		if ( empty( $play_data->answered_data ) ) {
			printf( '<p>%s</p>', esc_html__( 'This feature only works with new entry since version 2.0', 'wp-quiz-pro' ) );
			return;
		}
		?>
		<div class="wp-quiz-tracking <?php echo esc_attr( $this->name ); ?>-tracking">
			<img src="<?php echo esc_url( Helper::get_fb_result_image_url( $play_data->answered_data ) ); ?>">
		</div>
		<?php
	}

	/**
	 * Gets player data to insert into DB.
	 *
	 * @param Quiz  $quiz        Quiz object.
	 * @param array $player_data Player data get from REST request.
	 * @return array|false       Return an array with keys corresponding to players table columns.
	 *                           Return `false` if do not want to track player data on this quiz type.
	 */
	public function get_inserting_player_data( Quiz $quiz, array $player_data ) {
		if ( empty( $player_data['profile'] ) ) {
			return array();
		}

		$fb_profile  = $player_data['profile'];
		$insert_data = array(
			'user_id'    => null,
			'user_ip'    => Helper::get_current_ip(),
			'type'       => 'fb_user',
			'fb_user_id' => ! empty( $fb_profile['id'] ) ? $fb_profile['id'] : '',
			'email'      => ! empty( $fb_profile['email'] ) ? $fb_profile['email'] : '',
			'first_name' => ! empty( $fb_profile['first_name'] ) ? $fb_profile['first_name'] : '',
			'last_name'  => ! empty( $fb_profile['last_name'] ) ? $fb_profile['last_name'] : '',
			'gender'     => ! empty( $fb_profile['gender'] ) ? $fb_profile['gender'] : '',
			'picture'    => ! empty( $fb_profile['picture'] ) ? $fb_profile['picture'] : '',
			'friends'    => ! empty( $fb_profile['friends'] ) ? $fb_profile['friends'] : '',
		);

		/**
		 * Allows changing player insert data.
		 *
		 * @since 2.0.0
		 *
		 * @param array $insert_data Player insert data.
		 * @param array $player_data Unprocessed player data from REST request.
		 * @param Quiz  $quiz        Quiz object.
		 */
		return apply_filters( 'wp_quiz_player_insert_data', $insert_data, $player_data, $quiz );
	}

	/**
	 * Gets play data to insert into DB.
	 *
	 * @param Quiz  $quiz           Quiz object.
	 * @param array $player_data    Player data get from REST request.
	 * @return array|WP_Error|false Return an array with keys corresponding to plays table columns.
	 *                              Return `false` if do not want to track player data on this quiz type.
	 */
	public function get_inserting_play_data( Quiz $quiz, array $player_data ) {
		$quiz_data = isset( $player_data['quiz_data'] ) ? $player_data['quiz_data'] : json_decode( $quiz->get_post()->post_content, true );
		$answered  = isset( $player_data['answered'] ) ? $player_data['answered'] : ''; // Store the image src.
		$result_id = isset( $player_data['result_id'] ) ? $player_data['result_id'] : '';

		// Save the image.
		$image_code = str_replace( 'data:image/jpeg;base64,', '', $answered );
		$file_name  = sprintf( 'image-q%s-%s.jpg', $quiz->get_id(), date( 'Y-m-d-H-i-s' ) );
		$file_path  = Helper::get_fb_result_image_path( $file_name );
		$saved      = Helper::save_base64_image( $image_code, $file_path );
		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		return array(
			'quiz_id'       => $quiz->get_id(),
			'result'        => $result_id,
			'quiz_type'     => $this->name,
			'quiz_data'     => $quiz_data,
			'answered_data' => $file_name,
			'quiz_url'      => $player_data['current_url'],
		);
	}

	/**
	 * Prints content in <head> section.
	 *
	 * @param Quiz $quiz Quiz object.
	 */
	public function wp_head( Quiz $quiz ) {
		if ( empty( $_GET['wqtid'] ) ) { // WPCS: csrf ok.
			parent::wp_head( $quiz );
			return;
		}

		$tracking_id = intval( $_GET['wqtid'] );
		$play_data   = PlayData::get( $tracking_id );
		if ( ! $play_data ) {
			return;
		}

		$quiz_data = $play_data->quiz_data;
		$result_id = $play_data->result;

		if ( empty( $quiz_data['results'][ $result_id ] ) ) {
			return;
		}

		$player = Player::get( $play_data->player_id );
		if ( ! $player ) {
			return;
		}

		$result = $quiz_data['results'][ $result_id ];

		$result['title'] = str_replace( array( '%%userfirstname%%', '%%userlastname%%' ), array( $player->first_name, $player->last_name ), $result['title'] );
		$result['desc']  = str_replace( array( '%%userfirstname%%', '%%userlastname%%' ), array( $player->first_name, $player->last_name ), $result['desc'] );

		/*
		 * Prints tags for displaying meta data on social network.
		 */
		MetaTags::url( $result, $play_data );

		if ( ! empty( $result['title'] ) ) {
			MetaTags::title( $result, $play_data );
		}

		if ( ! empty( $result['desc'] ) ) {
			MetaTags::description( $result, $play_data );
		}

		$image_url = Helper::get_fb_result_image_url( $play_data->answered_data );
		if ( $image_url ) {
			printf( "<meta property=\"og:image\" content=\"%s\" />\n", esc_url( $image_url ) );
			printf( "<meta property=\"twitter:image\" content=\"%s\" />\n", esc_url( $image_url ) );
		}
	}

	/**
	 * Gets chart data.
	 *
	 * @param array $question   Question data.
	 * @param array $stats_data Stats data.
	 * @param Quiz  $quiz       Quiz object.
	 * @return array
	 */
	public function get_chart_data( array $question, array $stats_data, Quiz $quiz ) {
		$chart_data = array(
			array( __( 'Result', 'wp-quiz-pro' ), __( 'count', 'wp-quiz-pro' ), array( 'role' => 'annotation' ) ),
		);

		foreach ( $stats_data as $data ) {
			$chart_data[] = array( $data['title'], $data['count'], $data['count'] );
		}

		return $chart_data;
	}

	/**
	 * Gets chart options.
	 *
	 * @return array
	 */
	public function get_chart_options() {
		$options          = parent::get_chart_options();
		$options['title'] = __( 'How many times result is chosen?', 'wp-quiz-pro' );
		return $options;
	}
}
