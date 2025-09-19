<?php
/**
 * Stats module admin
 *
 * @package WPQuiz
 */

namespace WPQuiz\Modules\Stats\Admin;

use WP_Post;
use WPQuiz\Modules\PlayerTracking\Player;
use WPQuiz\Modules\Stats\DB;
use WPQuiz\PostTypeQuiz;
use WPQuiz\QuizTypeManager;

/**
 * Class Admin
 */
class Admin {

	/**
	 * Initializes class.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'post_row_actions', array( $this, 'row_actions' ), 10, 2 );
	}

	/**
	 * Registers admin menu.
	 */
	public function admin_menu() {
		$hook = add_submenu_page(
			'edit.php?post_type=' . PostTypeQuiz::get_name(),
			__( 'WP Quiz Stats', 'wp-quiz-pro' ),
			__( 'Stats', 'wp-quiz-pro' ),
			'manage_options',
			'wp-quiz-stats',
			array( $this, 'render_page' )
		);

		add_action( "load-{$hook}", array( $this, 'load' ) );
	}

	/**
	 * Loads page.
	 */
	public function load() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueues styles and scripts.
	 */
	public function enqueue() {
		wp_enqueue_style( 'wp-quiz-datepicker', wp_quiz()->plugin_url() . 'includes/Modules/Stats/assets/css/jquery-ui-datepicker.min.css', array(), wp_quiz()->version );
		wp_enqueue_style( 'wp-quiz-stats-admin', wp_quiz()->plugin_url() . 'includes/Modules/Stats/assets/css/stats.css', array(), wp_quiz()->version );
		wp_enqueue_script( 'wp-quiz-stats-admin', wp_quiz()->plugin_url() . 'includes/Modules/Stats/assets/js/admin.js', array( 'jquery-ui-datepicker' ), wp_quiz()->version, true );
	}

	/**
	 * Renders admin page.
	 */
	public function render_page() {
		$view       = ! empty( $_GET['view'] ) ? $_GET['view'] : ''; // phpcs:ignore
		$from       = ! empty( $_GET['from'] ) ? $_GET['from'] : ''; // phpcs:ignore
		$to         = ! empty( $_GET['to'] ) ? $_GET['to'] : ''; // phpcs:ignore
		$quiz_type  = ! empty( $_GET['quiz_type'] ) ? $_GET['quiz_type'] : ''; // phpcs:ignore
		$quiz_types = QuizTypeManager::get_titles();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'WP Quiz Stats', 'wp-quiz-pro' ); ?></h1>

			<form id="wp-quiz-stats-filter-form" action="" method="get">
				<div class="col">
					<label for="wp-quiz-stats-filter-from"><?php esc_html_e( 'From', 'wp-quiz-pro' ); ?></label>
					<input type="text" id="wp-quiz-stats-filter-from" class="wp-quiz-datepicker" name="from" value="<?php echo esc_attr( $from ); ?>">
				</div>

				<div class="col">
					<label for="wp-quiz-stats-filter-to"><?php esc_html_e( 'To', 'wp-quiz-pro' ); ?></label>
					<input type="text" id="wp-quiz-stats-filter-to" class="wp-quiz-datepicker" name="to" value="<?php echo esc_attr( $to ); ?>">
				</div>

				<div class="col">
					<label for="wp-quiz-stats-filter-quiz-type"><?php esc_html_e( 'Quiz type', 'wp-quiz-pro' ); ?></label>
					<select name="quiz_type" id="wp-quiz-stats-filter-quiz-type">
						<option value=""><?php esc_html_e( 'All', 'wp-quiz-pro' ); ?></option>
						<?php foreach ( $quiz_types as $key => $value ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $quiz_type ); ?>><?php echo esc_html( $value ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<input type="hidden" name="post_type" value="<?php echo esc_html( PostTypeQuiz::get_name() ); ?>">
				<input type="hidden" name="page" value="wp-quiz-stats">
				<button type="submit" class="button"><?php esc_html_e( 'Filter', 'wp-quiz-pro' ); ?></button>
			</form>

			<div id="poststuff">
				<?php
				switch ( $view ) {
					case 'detail':
						if ( ! empty( $_GET['quiz_id'] ) ) { // WPCS: csrf ok.
							$this->detail_view();
						}
						break;
					default:
						$this->list_view();
				}
				?>
			</div>
		</div>
		<?php
		if ( 'detail' === $view ) {
			?>
			<!-- Load the AJAX API -->
			<script type="text/javascript" src="https://www.google.com/jsapi"></script>
			<script type="text/javascript">

				// Load the Visualization API and the piechart package.
				google.load( 'visualization', '1.0', {
					'packages': [ 'corechart' ]
				});

				// Set a callback to run when the Google Visualization API is loaded.
				google.setOnLoadCallback( function() {

					// Instantiate and draw the chart.
					document.querySelectorAll( '.wp-quiz-chart' ).forEach( function( el ) {
						var chart, chartType, data, options;

						chartType = el.dataset.chartType || 'BarChart';
						data      = el.dataset.chartData;

						if ( ! data ) {
							return;
						}

						options = el.dataset.chartOptions ? JSON.parse( el.dataset.chartOptions ) : {};
						data    = JSON.parse( data );

						if ( 'BarChart' ===  chartType ) {
							var itemCount = data.length - 1;
							options.chartArea = {
								height: itemCount * 30
							};
						}

						data  = google.visualization.arrayToDataTable( data );
						chart = new google.visualization[ chartType ]( el );
						chart.draw( data, options );
					});
				});

			</script>
			<?php
		}
	}

	/**
	 * Shows list view.
	 */
	protected function list_view() {
		$from      = ! empty( $_GET['from'] ) ? $_GET['from'] : ''; // phpcs:ignore
		$to        = ! empty( $_GET['to'] ) ? $_GET['to'] : ''; // phpcs:ignore
		$quiz_type = ! empty( $_GET['quiz_type'] ) ? $_GET['quiz_type'] : ''; // phpcs:ignore
		$quizzes   = DB::get_plays_count(
			array(
				'from'      => $from,
				'to'        => $to,
				'group_by'  => DB::GROUP_BY_QUIZ_ID,
				'quiz_type' => $quiz_type,
			)
		);

		if ( ! $quizzes ) {
			echo '<p class="notice update-nag">' . esc_html__( 'Sorry, there is nothing to show at this moment.', 'wp-quiz-pro' ) . '</p>';
			return;
		}
		?>
		<div id="wp-quiz-stats-quizzes">
			<?php
			foreach ( $quizzes as $value ) :
				$quiz = PostTypeQuiz::get_quiz( $value['quiz_id'] );
				if ( ! $quiz ) {
					continue;
				}
				$detail_link = add_query_arg(
					array(
						'view'    => 'detail',
						'quiz_id' => $quiz->get_id(),
					)
				);
				?>
				<div class="wp-quiz-stats-quiz postbox">
					<h3 class="hndle" style="cursor: default;">
						<?php echo esc_html( $quiz->get_title() ); ?>

						<?php if ( $quiz->get_quiz_type()->get_icon() ) : ?>
							<span class="hndle-icon <?php echo esc_attr( $quiz->get_quiz_type()->get_icon() ); ?>"></span>
						<?php endif; ?>
					</h3>

					<div class="inside">
						<p>
							<?php esc_html_e( 'Played:', 'wp-quiz-pro' ); ?>
							<?php
							if ( intval( $value['count'] ) ) {
								$url = admin_url( 'edit.php?post_type=wp_quiz&page=wp_quiz_players&quiz_id=' . $quiz->get_id() );

								// translators: played count.
								echo '<a href="' . esc_url( $url ) . '">' . sprintf( esc_html( _n( '%s time', '%s times', $value['count'], 'wp-quiz-pro' ) ), esc_html( number_format_i18n( $value['count'] ) ) ) . '</a>';
							} else {
								// translators: played count.
								printf( esc_html( _n( '%s time', '%s times', $value['count'], 'wp-quiz-pro' ) ), esc_html( number_format_i18n( $value['count'] ) ) );
							}
							?>
						</p>

						<p><a href="<?php echo esc_url( $detail_link ); ?>"><?php esc_html_e( 'View detail', 'wp-quiz-pro' ); ?></a></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Shows detail view.
	 */
	protected function detail_view() {
		$from    = ! empty( $_GET['from'] ) ? $_GET['from'] : ''; // phpcs:ignore
		$to      = ! empty( $_GET['to'] ) ? $_GET['to'] : ''; // phpcs:ignore
		$quiz_id = ! empty( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : ''; // phpcs:ignore
		$quiz    = PostTypeQuiz::get_quiz( $quiz_id );
		if ( ! $quiz ) {
			esc_html_e( 'Quiz does not exist.', 'wp-quiz-pro' );
			return;
		}

		$quiz_type = $quiz->get_quiz_type();
		?>

		<h2 class="wp-quiz-stats-quiz-title"><?php echo esc_html( $quiz->get_title() ); ?></h2>

		<?php
		if ( 'trivia' === $quiz_type->get_name() ) {
			$top_players = DB::get_top_scored_players( $quiz_id );
			?>
			<div class="wp-quiz-stats-question postbox">
				<h3 class="wp-quiz-stats-question-title hndle">
					<span><?php esc_html_e( 'Top scored', 'wp-quiz-pro' ); ?></span>
				</h3>

				<div class="inside">
					<ul>
						<?php
						foreach ( $top_players as $top_player ) {
							$player = Player::get( $top_player['player_id'] );
							$name   = $player ? $player->get_display_name() : __( 'Guest', 'wp-quiz-pro' );
							?>
							<li>
								<strong><?php echo esc_html( $name ); ?>:</strong>
								<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=wp_quiz&page=wp_quiz_players&play_data_id=' . $top_player['play_data_id'] ) ); ?>"><?php echo intval( $top_player['correct_answered'] ); ?></a>
							</li>
							<?php
						}
						?>
					</ul>
				</div>
			</div>
			<?php
		}
		?>

		<?php
		if ( 'fb_quiz' === $quiz_type->get_name() ) {
			$data = DB::get_fb_quiz_result_count_data(
				$quiz_id,
				array(
					'from' => $from,
					'to'   => $to,
				)
			);

			$chart_data    = $quiz_type->get_chart_data( array(), $data, $quiz );
			$chart_type    = $quiz_type->get_chart_type();
			$chart_options = $quiz_type->get_chart_options();

			?>
			<div
				class="wp-quiz-chart"
				data-chart-data="<?php echo esc_attr( wp_json_encode( $chart_data ) ); ?>"
				data-chart-type="<?php echo esc_attr( $chart_type ); ?>"
				data-chart-options="<?php echo esc_attr( wp_json_encode( $chart_options ) ); ?>"
			></div>
			<?php

			return;
		}

		$data = DB::get_answer_count_data(
			$quiz_id,
			array(
				'from' => $from,
				'to'   => $to,
			)
		);

		$questions = $quiz->get_questions();
		foreach ( $questions as $qid => $question ) {
			$question['id'] = $qid;
			$count          = ! empty( $data[ $qid ]['count'] ) ? intval( $data[ $qid ]['count'] ) : 0;
			$chart_data     = $quiz_type->get_chart_data( $question, $data, $quiz );
			$chart_type     = $quiz_type->get_chart_type();
			$chart_options  = $quiz_type->get_chart_options();

			if ( 'swiper' === $quiz_type->get_name() ) {
				$count = intval( $question['votesUp'] ) + intval( $question['votesDown'] );
			}
			?>
			<div class="wp-quiz-stats-question postbox">
				<h3 class="wp-quiz-stats-question-title hndle">
					<span><?php echo esc_html( $question['title'] ); ?></span>
					<small>
						<?php esc_html_e( 'Answered:', 'wp-quiz-pro' ); ?>
						<?php
						// translators: played count.
						printf( esc_html( _n( '%s time', '%s times', $count, 'wp-quiz-pro' ) ), esc_html( number_format_i18n( $count ) ) );
						?>
					</small>
				</h3>

				<div class="inside">
					<div
						class="wp-quiz-chart"
						data-chart-data="<?php echo esc_attr( wp_json_encode( $chart_data ) ); ?>"
						data-chart-type="<?php echo esc_attr( $chart_type ); ?>"
						data-chart-options="<?php echo esc_attr( wp_json_encode( $chart_options ) ); ?>"
					></div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Filters the array of row action links on the Posts list table.
	 *
	 * The filter is evaluated only for non-hierarchical post types.
	 *
	 * @param array   $actions An array of row action links. Defaults are
	 *                         'Edit', 'Quick Edit', 'Restore', 'Trash',
	 *                         'Delete Permanently', 'Preview', and 'View'.
	 * @param WP_Post $post    The post object.
	 * @return array
	 */
	public function row_actions( $actions, $post ) {
		if ( PostTypeQuiz::get_name() !== $post->post_type || 'publish' !== $post->post_status ) {
			return $actions;
		}

		$statistics_url = admin_url(
			sprintf(
				'edit.php?post_type=%1$s&page=wp-quiz-stats&view=detail&quiz_id=%2$s',
				PostTypeQuiz::get_name(),
				$post->ID
			)
		);

		$actions['statistics'] = sprintf(
			'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
			$statistics_url,
			/* translators: %s: quiz title */
			esc_attr( sprintf( __( 'Statistics &#8220;%s&#8221;', 'wp-quiz-pro' ), $post->post_title ) ),
			__( 'Statistics', 'wp-quiz-pro' )
		);

		return $actions;
	}
}
