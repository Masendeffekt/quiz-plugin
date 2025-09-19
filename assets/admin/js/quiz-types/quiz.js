'use strict';

(function (wpQuizAdmin, $) {
	"use strict";

	wpQuizAdmin.Quiz = function () {
		function Quiz($wrapper) {
			babelHelpers.classCallCheck(this, Quiz);

			this.$wrapper = $wrapper;
			this.init();
		}

		babelHelpers.createClass(Quiz, [{
			key: 'init',
			value: function init() {
				this.questionsList = this.$wrapper.find('.wp-quiz-questions-list');
				this.resultsList = this.$wrapper.find('.wp-quiz-results-list');

				this.templates = {
					question: wp.template('wp-quiz-' + this.name + '-question-tpl'),
					answer: wp.template('wp-quiz-' + this.name + '-answer-tpl'),
					result: wp.template('wp-quiz-' + this.name + '-result-tpl')
				};

				var questions = this.$wrapper.find('.wp-quiz-' + this.name + '-questions-value').val();
				this.questions = questions.length ? JSON.parse(questions) : [];

				var results = this.$wrapper.find('.wp-quiz-' + this.name + '-results-value').val();
				this.results = results.length ? JSON.parse(results) : [];

				var defaultQuestion = this.$wrapper.find('.wp-quiz-' + this.name + '-default-question-value').val();
				this.defaultQuestion = defaultQuestion.length ? JSON.parse(defaultQuestion) : [];

				var defaultAnswer = this.$wrapper.find('.wp-quiz-' + this.name + '-default-answer-value').val();
				this.defaultAnswer = defaultAnswer.length ? JSON.parse(defaultAnswer) : [];

				var defaultResult = this.$wrapper.find('.wp-quiz-' + this.name + '-default-result-value').val();
				this.defaultResult = defaultResult.length ? JSON.parse(defaultResult) : [];

				Object.values(this.questions).forEach(this.loadQuestionOutput);

				Object.values(this.results).forEach(this.loadResultOutput);

				this.lastAddedQuestion = null;
				this.lastRemovedQuestion = null;
				this.lastAddedAnswer = null;
				this.lastRemovedAnswer = null;
				this.lastAddedResult = null;
				this.lastRemovedResult = null;

				this.loadEvents();
			}
		}, {
			key: 'parseQuestion',
			value: function parseQuestion(question) {
				return question;
			}
		}, {
			key: 'parseAnswer',
			value: function parseAnswer(answer) {
				return answer;
			}
		}, {
			key: 'parseResult',
			value: function parseResult(result) {
				return result;
			}
		}, {
			key: 'getQuestionTplData',
			value: function getQuestionTplData(question) {
				return {
					i18n: wpQuizAdmin.i18n,
					question: question,
					index: question.index
				};
			}
		}, {
			key: 'getAnswerTplData',
			value: function getAnswerTplData(answer) {
				return {
					i18n: wpQuizAdmin.i18n,
					answer: answer,
					index: answer.index
				};
			}
		}, {
			key: 'getResultTplData',
			value: function getResultTplData(result) {
				return {
					i18n: wpQuizAdmin.i18n,
					result: result,
					index: result.index
				};
			}
		}, {
			key: 'loadQuestionOutput',
			value: function loadQuestionOutput(question, index) {
				var _this = this;

				question = this.parseQuestion(question);
				question.index = index;

				var $question = this.templates.question(this.getQuestionTplData(question, index));
				$question = $($question);

				var $answersList = $question.find('.wp-quiz-answers-list');
				Object.values(question.answers).forEach(function (answer, index) {
					return _this.loadAnswerOutput(answer, index, $answersList);
				});

				this.questionsList.append($question);
			}
		}, {
			key: 'loadAnswerOutput',
			value: function loadAnswerOutput(answer, index, $listEl) {
				var baseName = $listEl.attr('data-base-name');
				answer = this.parseAnswer(answer);
				answer.question = question;
				answer.index = index;
				answer.baseName = baseName;
				$answersList.append(this.templates.answer(this.getAnswerTplData(answer)));
			}
		}, {
			key: 'loadResultOutput',
			value: function loadResultOutput(result, index) {
				result = this.parseResult(result);
				result.index = index;
				var output = this.templates.result(this.getResultTplData(result));
				this.resultsList.append(output);
			}
		}, {
			key: 'loadEvents',
			value: function loadEvents() {
				var _this2 = this;

				this.$wrapper.on('click', '.wp-quiz-add-question-btn', function (ev) {
					return _this2.onAddQuestion(ev);
				});

				this.$wrapper.on('click', '.wp-quiz-remove-question-btn', function (ev) {
					return _this2.onRemoveQuestion(ev);
				});

				this.$wrapper.on('click', '.wp-quiz-add-answer-btn', function (ev) {
					return _this2.onAddAnswer(ev);
				});

				this.$wrapper.on('click', '.wp-quiz-remove-answer-btn', function (ev) {
					return _this2.onRemoveAnswer(ev);
				});

				this.$wrapper.on('click', '.wp-quiz-add-result-btn', function (ev) {
					return _this2.onAddResult(ev);
				});

				this.$wrapper.on('click', '.wp-quiz-remove-result-btn', function (ev) {
					return _this2.onRemoveResult(ev);
				});

				this.$wrapper.find('.wp-quiz-image-upload').each(function () {
					wpQuizAdmin.helpers.initImageUpload($(this));
				});

				if (this.videoUpload) {
					this.$wrapper.find('.wp-quiz-video-insert').each(function () {
						wpQuizAdmin.helpers.initVideoInsert($(this));
					});
				}

				if (this.questionSortable) {
					this.sortQuestions();
				}

				if (this.answerSortable) {
					this.sortAnswers();
				}

				if (this.resultSortable) {
					this.sortResults();
				}
			}
		}, {
			key: 'reIndexQuestions',
			value: function reIndexQuestions() {
				this.$wrapper.find('.wq-question').each(function (index) {
					$(this).find('.wq-question-number').text(index + 1);
				});
			}
		}, {
			key: 'onAddQuestion',
			value: function onAddQuestion(ev) {
				ev.preventDefault();

				var defaultQuestion = this.defaultQuestion;
				defaultQuestion.id = wpQuizAdmin.helpers.getRandomString();
				defaultQuestion.index = this.questionsList.find('.wp-quiz-question').length;

				var $question = this.templates.question(this.getQuestionTplData(defaultQuestion));
				$question = $($question);
				this.questionsList.append($question);
				this.lastAddedQuestion = defaultQuestion;

				$question.find('.wp-quiz-image-upload').each(function () {
					wpQuizAdmin.helpers.initImageUpload($(this));
				});

				if (this.videoUpload) {
					$question.find('.wp-quiz-video-insert').each(function () {
						wpQuizAdmin.helpers.initVideoInsert($(this));
					});
				}
			}
		}, {
			key: 'onRemoveQuestion',
			value: function onRemoveQuestion(ev) {
				ev.preventDefault();
				var $question = $(ev.currentTarget).closest('.wp-quiz-question');
				this.lastRemovedQuestion = $question.attr('data-id');
				$question.remove();
				this.reIndexQuestions();
			}
		}, {
			key: 'onAddAnswer',
			value: function onAddAnswer(ev) {
				var $answers = $(ev.currentTarget).closest('.wp-quiz-answers');
				var answerType = $answers.attr('data-type');
				var defaultAnswer = this.defaultAnswer;
				defaultAnswer.id = wpQuizAdmin.helpers.getRandomString();
				defaultAnswer.baseName = $answers.data('base-name');
				defaultAnswer.index = $answers.find('.wp-quiz-answer').length;
				defaultAnswer.type = answerType;

				$answers.append(this.templates.answer(this.getAnswerTplData(defaultAnswer)));
				this.lastAddedAnswer = defaultAnswer;
			}
		}, {
			key: 'onRemoveAnswer',
			value: function onRemoveAnswer(ev) {
				ev.preventDefault();
				var $answer = $(ev.currentTarget).closest('.wp-quiz-answer');
				this.lastRemovedAnswer = $answer.attr('data-id');
				$answer.remove();
			}
		}, {
			key: 'onAddResult',
			value: function onAddResult(ev) {
				var defaultResult = this.defaultResult;
				defaultResult.id = wpQuizAdmin.helpers.getRandomString();
				defaultResult.index = this.resultsList.find('.wp-quiz-result').length;
				this.resultsList.append(this.templates.result(this.getResultTplData(defaultResult)));
				this.lastAddedResult = defaultResult;
			}
		}, {
			key: 'onRemoveResult',
			value: function onRemoveResult(ev) {
				ev.preventDefault();
				var $result = $(ev.currentTarget).closest('.wp-quiz-result');
				this.lastRemovedResult = $result.attr('data-id');
				$result.remove();
			}
		}, {
			key: 'sortAnswers',
			value: function sortAnswers() {
				this.$wrapper.find('.wp-quiz-answers-list').sortable({
					items: '> .wp-quiz-answer',
					placeholder: 'wp-quiz-sortable-placeholder',
					start: function start(ev, ui) {
						ui.placeholder.height(ui.item.height());
					}
				});
			}
		}, {
			key: 'sortQuestions',
			value: function sortQuestions() {
				var _this3 = this;

				this.$wrapper.find('.wp-quiz-questions-list').sortable({
					handle: '.wp-quiz-question-number',
					items: '> .wp-quiz-question',
					placeholder: 'wp-quiz-sortable-placeholder',
					start: function start(ev, ui) {
						ui.placeholder.height(ui.item.height());
					},
					update: function update(ev, ui) {
						return _this3.onSortQuestion(ev, ui);
					}
				});
			}
		}, {
			key: 'sortResults',
			value: function sortResults() {
				this.$wrapper.find('.wp-quiz-results-list').sortable({
					items: '> .wp-quiz-result',
					placeholder: 'wp-quiz-sortable-placeholder',
					start: function start(ev, ui) {
						ui.placeholder.height(ui.item.height());
					}
				});
			}
		}, {
			key: 'onSortQuestions',
			value: function onSortQuestions(ev, ui) {
				if (this.answerSortable) {
					this.sortAnswers();
				}
				this.reIndexQuestions();
			}
		}, {
			key: 'name',
			get: function get() {
				return '';
			}
		}, {
			key: 'questionSortable',
			get: function get() {
				return true;
			}
		}, {
			key: 'answerSortable',
			get: function get() {
				return true;
			}
		}, {
			key: 'resultSortable',
			get: function get() {
				return true;
			}
		}, {
			key: 'videoUpload',
			get: function get() {
				return false;
			}
		}]);
		return Quiz;
	}();
})(wpQuizAdmin, jQuery);
//# sourceMappingURL=../../sourcemaps/quiz-types/quiz.js.map
