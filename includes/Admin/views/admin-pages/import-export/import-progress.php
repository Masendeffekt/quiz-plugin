<?php
/**
 * Template for import progress
 *
 * @package WPQuiz
 * @var ImportExport $page
 * @var array        $quizzes
 */

use WPQuiz\Admin\AdminPages\ImportExport;

$total = count( $quizzes );
?>
<form id="wq-import-options">
	<p>
		<label>
			<input type="checkbox" id="wq-download-images" checked>
			<?php esc_html_e( 'Download images', 'wp-quiz-pro' ); ?>
		</label>
	</p>

	<p>
		<label>
			<input type="checkbox" id="wq-force-new-quizzes">
			<?php esc_html_e( 'Create a new quiz if quiz with the same title is present.', 'wp-quiz-pro' ); ?>
		</label>
	</p>

	<button class="button button-primary"><?php esc_html_e( 'Continue', 'wp-quiz-pro' ); ?></button>
	<span class="spinner" style="float: none;"></span>
</form>

<div id="wq-import-progress" class="hidden">
	<?php // translators: %1$s: import done, %2$s: import total. ?>
	<p><?php printf( __( 'Importing %1$s of %2$s quizzes', 'wp-quiz-pro' ), '<span id="import-done">0</span>', "<span id=\"import-total\">{$total}</span>" ); // WPCS: xss ok. ?></p>
</div>

<div id="wq-import-done" class="hidden">
	<p><?php esc_html_e( 'Import done', 'wp-quiz-pro' ); ?></p>
	<p><a href="<?php echo esc_url( $page->get_tab_url( 'import' ) ); ?>"><?php esc_html_e( 'Back to import page', 'wp-quiz-pro' ); ?></a></p>
</div>
