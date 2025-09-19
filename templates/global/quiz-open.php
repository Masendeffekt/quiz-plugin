<?php
/**
 * Template for quiz open tag
 *
 * @package WPQuiz
 * @version 2.0.8
 *
 * @var \WPQuiz\Quiz $quiz Quiz object.
 */

$quiz_type = $quiz->get_quiz_type();
?>
<!-- wp quiz -->
<div id="wp-quiz-<?php echo esc_attr( $quiz->get_id() ); ?>" class="<?php echo esc_attr( $quiz_type->get_quiz_class_attr( $quiz ) ); ?>" <?php echo $quiz->get_quiz_type()->get_data_attrs( $quiz ); ?>>
