<?php
/**
 * Template for export page
 *
 * @package WPQuiz
 * @var \WPQuiz\Admin\AdminPages\ImportExport $page
 */

use WPQuiz\PostTypeQuiz;

$quizzes = get_posts(
	array(
		'post_type'   => PostTypeQuiz::get_name(),
		'post_status' => 'any',
		'nopaging'    => true, // phpcs:ignore
	)
);

if ( ! empty( $quizzes ) ) : ?>
	<form action="<?php echo esc_url( $page->get_tab_url( 'export' ) ); ?>" method="post">
		<h2 class="hndle"><?php esc_html_e( 'Select Quizzes to Export', 'wp-quiz-pro' ); ?></h2>

		<div class="inside postbox">
			<table id="export_wp_quiz" width="100%" frame="border">
				<tr>
					<td style="width:5%;"><input id="selectall" type="checkbox" /></td>
					<td><label for="selectall"><?php esc_html_e( 'Select All', 'wp-quiz-pro' ); ?></label></td>
				</tr>
				<?php foreach ( $quizzes as $quiz ) : ?>
					<tr>
						<td><input class="wp_quizId" type="checkbox" value="<?php echo intval( $quiz->ID ); ?>" id="wp-quiz-<?php echo intval( $quiz->ID ); ?>" name="wp_quizzes[]" /></td>
						<td><label for="wp-quiz-<?php echo intval( $quiz->ID ); ?>"><?php echo esc_html( $quiz->post_title ); ?></label></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>

		<p>
			<input type="submit" name="export_quizzes" id="submit" class="button-primary" value="<?php esc_attr_e( 'Export Quizzes', 'wp-quiz-pro' ); ?>">
			<input type="submit" name="export_settings" id="submit" class="button-primary" value="<?php esc_attr_e( 'Export Settings', 'wp-quiz-pro' ); ?>">&nbsp;
		</p>
	</form>

<?php else : ?>
	<div><?php esc_html_e( 'No Quiz to Export', 'wp-quiz-pro' ); ?></div>
<?php endif; ?>
