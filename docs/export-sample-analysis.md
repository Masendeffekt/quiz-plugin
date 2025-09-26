# "A nagy általános tudás kvíz" export breakdown

This document explains the structure of the exported quiz payload that was retrieved from the plugin's admin export tool. The data is produced by `\WPQuiz\Quiz::to_array()` / `to_json()`, which collects the questions, results, settings, and featured image information for a quiz post.

## Top-level structure

The export contains a list of quizzes. Each quiz object mirrors what the importer expects when creating new quizzes programmatically.

| Key | Purpose | Notes |
| --- | --- | --- |
| `id` | Unique quiz identifier. | Populated automatically when exporting an existing quiz. |
| `title` | Human-readable quiz title. | Stored on the `wp_quiz` custom post type. |
| `type` | Quiz type slug. | Must be one of the registered types managed by `QuizTypeManager`. |
| `questions` | Associative array of questions keyed by generated IDs. | Each entry contains the question content plus answers. |
| `results` | Associative array of results keyed by generated IDs. | Controls score ranges and result descriptions. |
| `settings` | Quiz-level settings merged from meta defaults. | Reflects the fields returned by the active quiz type. |
| `featured_image` | URL or `false` when no featured image is attached. | The importer can sideload this when `download_images` is enabled. |

The plugin loads questions/results from `post_content` JSON and merges quiz-type defaults during export, which is why switches such as `rand_questions` or `show_next_button` appear with `on`/`off` values.【F:includes/Quiz.php†L58-L86】【F:includes/QuizType.php†L64-L107】

## Questions block

Questions are stored as associative arrays indexed by generated IDs like `apc3d`. Every question carries metadata required by the trivia type:

- `mediaType`, `answerType`, media URLs, and credits govern how the front end renders the prompt.
- `title`, `desc`, and `hint` contain the text shown to the player.
- `answers` is itself an associative array keyed by generated IDs. Each answer defines display text and optional media. The correct answer has `isCorrect: "1"`, matching the trivia logic that checks this flag when calculating scores.【F:includes/QuizType/Trivia.php†L43-L120】

Because the export already contains IDs and answer order, you can submit the entire `questions` object back through the importer, or regenerate fresh IDs—`Importer::parse_import_data()` will rebuild them if omitted.【F:includes/Importer.php†L120-L191】

## Results block

The `results` array assigns a single result entry (`rbwbr`) with a score range of 0–10. Trivia quizzes map total correct answers into these ranges to pick which result summary to display at the end. When importing, the plugin verifies these ranges and fills in missing fields with defaults, so you can provide multiple result tiers for more nuanced feedback.【F:includes/Importer.php†L191-L248】

## Settings block

Settings mirror the per-quiz options saved under the `wp_quiz_` meta prefix. During export, the plugin merges stored meta with default settings from the quiz type. Notable flags in this sample include:

- `question_layout: "multiple"` – the standard trivia layout.
- `rand_questions`, `rand_answers`, `restart_questions`: `on` values toggle optional behaviors that the importer normalizes from `on/off` strings.【F:includes/Importer.php†L249-L314】
- `show_next_button: "on"` – ensures the Next button is displayed between questions.

These values can be supplied in your AI-generated payload. Missing keys are filled with defaults during import by calling `QuizType::get_default_settings()` and related helpers.【F:includes/Importer.php†L314-L383】

## Featured image

The exported quiz has `featured_image: false`, meaning no thumbnail was attached. When you pass a remote image URL here and call the REST importer with `download_images: true`, the plugin downloads and sets the featured image automatically.【F:includes/Importer.php†L90-L118】

## Using the export as an AI template

1. Use the exported structure as the target schema in your AI prompts so the model produces the same nested keys.
2. Validate that each question includes at least one answer marked with `isCorrect: "1"` and that result ranges cover all possible scores. The importer guards against missing data but valid ranges make the experience smoother.【F:includes/Importer.php†L120-L248】
3. Submit the final payload to `POST /wp-json/wp-quiz/v2/admin/import-quizzes` or feed it into a PHP automation that instantiates `\WPQuiz\Importer` and calls `import_quiz()`.

This approach lets you round-trip a quiz definition between export, AI modification, and automated re-import without touching the WordPress admin UI.
