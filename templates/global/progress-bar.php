<?php
/**
 * Template for quiz progress bar
 *
 * @package WPQuiz
 *
 * @var float $percent
 */

$percent = ! empty( $percent ) ? floatval( $percent ) : 0;
?>
<!-- Progress bar -->
<div class="wq-progress-bar-container wq_quizProgressBarCtr">
	<div class="wq_quizProgressBar">
		<span class="wq_quizProgressValue" style="width: <?php echo $percent; ?>%;"><?php echo $percent; ?>%</span>
	</div>
</div>
<!-- // Progress bar-->
