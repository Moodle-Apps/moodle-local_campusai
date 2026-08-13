# Campus Assistant

AI-powered student assistant for your Moodle campus.

Campus Assistant adds a conversational widget to every Moodle page. Students can ask questions in natural language and receive answers based on real Moodle data (courses, assignments, grades, forums, calendar events) and/or external AI models.

## Features

- Floating chat widget with customizable color, position, title and welcome message.
- Role-aware function calling: student, teacher and manager functions.
- Multiple AI providers: managed proxy, OpenAI, Google Gemini, Anthropic Claude and DeepSeek.
- Conversation history and audit log with configurable retention.
- Rate limiting per user.
- Full Privacy API support for GDPR compliance.
- No external JavaScript dependencies; vanilla JS widget.

## Supported Moodle versions

- Moodle 4.4 or later (build `2024041500`).
- PHP 8.1 or later.

## Installation

1. Download `campusai-v2.2.2.zip` from the release page.
2. Extract the ZIP; it should contain a single `campusai/` folder.
3. Copy `campusai/` into the `/local/` directory of your Moodle installation.
4. Log in as administrator and visit *Site administration → Notifications* to complete the installation.
5. Go to *Site administration → Plugins → Local plugins → Campus Assistant* and configure your provider, API key or license key, and appearance options.
6. Enable the plugin and verify the widget appears for users with the `local/campusai:use` capability.

## Configuration

- **AI provider**: choose between the managed proxy or a direct provider (OpenAI, Gemini, Claude, DeepSeek).
- **API key / license key**: required credentials for the selected provider.
- **Appearance**: customize the widget color, position, title, welcome message and language.
- **Privacy**: configure audit logging and log retention.
- **Rate limiting**: set the maximum number of messages per user within a time window.

## External services

When using the default managed proxy provider, chat messages are sent to `https://campusassistant.app/`. The proxy routes the request to the configured AI model. No student data is stored by the proxy beyond what is necessary to process the request.

If you use OpenAI, Gemini, Claude or DeepSeek directly, data is sent to the corresponding provider according to their terms of service. You are responsible for configuring and complying with each provider's terms.

## Screenshots

- `pix/screenshots/widget-student.png` — floating chat widget on a course page.
- `pix/screenshots/widget-teacher.png` — widget showing a teacher-specific suggestion.
- `pix/screenshots/settings-general.png` — Campus Assistant settings page.
- `pix/screenshots/settings-provider.png` — provider and credentials configuration.

## Cross-database compatibility

The plugin uses Moodle's Data Manipulation API (DML) for all database access. Custom SQL has been reviewed for portability and does not use MySQL/MariaDB-specific functions or syntax. It is intended to work with MySQL, MariaDB and PostgreSQL.

## Privacy

The plugin stores user messages and assistant replies in the `local_campusai_conversation` table. Rate limit counters are stored in `local_campusai_ratelimit`. Both tables are covered by Moodle's Privacy API and can be exported or deleted via *Site administration → Users → Privacy and policies → Data requests*.

## License

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

See `LICENSE` for the full license text.

## Author and support

- **Author**: Moodle-Apps
- **Source code**: https://github.com/Moodle-Apps/Campus-Assistant
- **Issue tracker**: https://github.com/Moodle-Apps/Campus-Assistant/issues
- **Documentation**: https://github.com/Moodle-Apps/Campus-Assistant#readme
