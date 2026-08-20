# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.2.4] - 2026-08-18

### Changed

- Chat message submission now uses the registered external service
  `local_campusai_chat_send_message` via `core/ajax` instead of the plugin's own AJAX endpoint.
- Regenerated `amd/build/campusai.min.js` with the official Moodle grunt build (minified output
  plus source map).

### Removed

- Custom `ajax.php` endpoint; the external service covers message submission.
- Unused `error_method_not_allowed` language string.

### Fixed

- Eliminated N+1 queries in `teacher_at_risk_students`: average grades and overdue assignments
  for all courses and students are now resolved with two grouped queries.

## [2.2.3] - 2026-08-18

### Added

- Privacy provider now declares every external location data is sent to (managed proxy, OpenAI,
  Anthropic Claude, Google Gemini and DeepSeek) via `add_external_location_link()`.
- PHPUnit coverage for the external location declarations.
- GitHub Actions CI workflow based on the official moodle-plugin-ci template.

### Changed

- Migrated the chat widget from legacy JavaScript to an AMD module (`local_campusai/campusai`).
- Removed redundant `$PAGE->requires->css()` call; Moodle loads the plugin `styles.css` automatically.
- Replaced all `PARAM_RAW`/`PARAM_RAW_TRIMMED` usage with stricter param types (`PARAM_TEXT`).
- Hardened queries on `logstore_standard_log`: `study_time` now scans a fixed 90-day window,
  `admin_login_stats` reads at most 1000 rows and `admin_recent_activity` caps the window at 30 days.
- Fixed N+1 queries in announcements and teacher overview functions.

### Removed

- Redundant `db/uninstall.php`; Moodle already removes plugin configuration on uninstall.

## [2.2.2] - 2026-08-12

### Changed

- Bumped plugin version number to `2026090122` to satisfy Moodle Marketplace release uniqueness requirement.

## [2.2.1] - 2026-08-12

### Added

- Initial public release of the Campus Assistant plugin.
- Floating chat widget with customizable appearance and quick-action chips.
- Role-aware function calling: 25 student functions, 15 manager functions and 12 teacher functions.
- Support for five AI providers: managed proxy, OpenAI, Google Gemini, Anthropic Claude and DeepSeek.
- Conversation audit log with configurable retention.
- Per-user rate limiting with configurable window.
- Full Moodle Privacy API implementation.
- PHPUnit tests for license manager, privacy provider and external chat service.

### Security

- All user input is sanitized before being sent to AI providers.
- Assistant output is escaped before display.
- Capabilities enforce role-based access to functions and audit logs.
- API keys, JWT secrets and license keys are stored via Moodle config and never hardcoded.
- No use of `additionalhtmlhead`; all assets are loaded through `$PAGE->requires` for CSP compatibility.

## [2.2.0] - 2026-07-15

- Internal preview, not published.

## [2.1.0] - 2026-06-01

- Internal preview, not published.

## [2.0.0] - 2026-04-20

- Internal preview, not published.
