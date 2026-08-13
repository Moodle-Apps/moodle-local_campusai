# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
