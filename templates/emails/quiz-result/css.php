<?php
/**
 * CSS for quiz result email
 *
 * @package WPQuiz
 */

$css = <<<CSS
body {
    font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol;
    font-size: 16px;
}

.result {
    padding: 30px;
    margin: 20px auto;
    background: #F9F9F9;
    border: 1px solid #ECECEC;
    max-width: 500px;
}

.result__quiz-title {
    font-size: 2em;
    font-weight: 700;
    margin-bottom: 20px;
}

.result__title {
    font-weight: 700;
}

.result__image {
    width: 100%;
    height: auto;
    margin-top: 20px;
}

.play-button {
    font-size: 1.5em;
    font-weight: 700;
    color: #fff;
    background-color: #00cdff;
    display: inline-block;
    padding: 10px 20px;
    text-decoration: none;
}

.wp-quiz-tracking .answers:after {
	content: " ";
	display: block;
	height: 0;
	visibility: hidden;
	clear: both;
}

.wp-quiz-tracking .answers.image-answers .answer span {
	display: block;
	padding: 0 10px 4px 10px;
}

.wp-quiz-tracking .answers.image-answers .answer {
	width: 180px;
	float: left;
	margin-right: 15px;
	border-radius: 3px;
	display: block;
}

.wp-quiz-tracking .answers.text-answers .answer {
	display: inline-block;
	padding: 7px 10px;
	border-radius: 3px;
}

.wp-quiz-tracking .answers .answer.correct {
	background: #C8E6C9;
}

.wp-quiz-tracking .answers .answer.incorrect {
	background: #ffcdd2;
}
CSS;

echo $css; // WPCS: xss ok.
