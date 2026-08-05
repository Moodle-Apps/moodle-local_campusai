<?php
// This file is part of Campus Assistant - a Moodle local plugin.
//
// Campus Assistant is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * English language strings for Campus Assistant.
 *
 * @package   local_campusai
 * @copyright 2026 Campus Assistant <hola@campusassistant.app>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Campus Assistant';
$string['pluginname_desc'] = 'AI-powered student assistant for your Moodle campus.';

// Settings.
$string['settings'] = 'Campus Assistant Settings';
$string['settings_provider'] = 'AI Provider';
$string['settings_provider_desc'] = 'Select the AI provider for the assistant.';
$string['settings_apikey'] = 'API Key';
$string['settings_apikey_desc'] = 'Your API key for the selected provider.';
$string['settings_model'] = 'Model';
$string['settings_model_desc'] = 'The specific model to use (e.g. gpt-4o-mini, gemini-1.5-flash, claude-3-5-haiku-20241022, deepseek-chat).';
$string['settings_systemprompt'] = 'System Prompt';
$string['settings_systemprompt_desc'] = 'Instructions that define the assistant behaviour. Leave default unless you know what you are doing.';
$string['settings_position'] = 'Button Position';
$string['settings_position_desc'] = 'Where the assistant button appears on the page.';
$string['settings_color'] = 'Theme Colour';
$string['settings_color_desc'] = 'Primary colour for the assistant button and modal.';
$string['settings_title'] = 'Assistant Title';
$string['settings_title_desc'] = 'Title shown at the top of the assistant modal.';
$string['settings_welcome'] = 'Welcome Message';
$string['settings_welcome_desc'] = 'First message the student sees when opening the assistant.';
$string['settings_ratelimit'] = 'Rate Limit (messages per hour)';
$string['settings_ratelimit_desc'] = 'Maximum messages a student can send per hour.';
$string['settings_maxtokens'] = 'Max Tokens per Response';
$string['settings_maxtokens_desc'] = 'Maximum tokens in the AI response. Higher = more detailed but more expensive.';
$string['settings_auditlog'] = 'Enable Audit Log';
$string['settings_auditlog_desc'] = 'Log all student interactions for admin review.';
$string['settings_logretention'] = 'Log Retention (days)';
$string['settings_logretention_desc'] = 'How long to keep audit logs before auto-deleting. 0 = keep forever.';
$string['settings_language'] = 'Default language';
$string['settings_language_desc'] = 'Default language for assistant responses.';

$string['settings_enabled'] = 'Enable Campus Assistant';
$string['settings_enabled_desc'] = 'Master switch for the assistant.';
$string['settings_hideroles'] = 'Hide for Roles';
$string['settings_hideroles_desc'] = 'Select roles that should NOT see the assistant (e.g. managers, administrators).';

// Positions.
$string['position_bottom_right'] = 'Bottom Right';
$string['position_bottom_left'] = 'Bottom Left';

// Quick prompts.
$string['quickprompt_exams'] = '📅 What exams do I have?';
$string['quickprompt_tasks'] = '📝 What am I missing?';
$string['quickprompt_courses'] = '📚 My courses';

// Privacy.
$string['privacy:metadata:conversation'] = 'Stores student questions and AI responses for audit purposes.';
$string['privacy:metadata:conversation:userid'] = 'The ID of the user who sent the message.';
$string['privacy:metadata:conversation:usermessage'] = 'The question asked by the student.';
$string['privacy:metadata:conversation:assistantmessage'] = 'The response given by the assistant.';
$string['privacy:metadata:conversation:timecreated'] = 'When the interaction occurred.';

// Capabilities.
$string['campusai:use'] = 'Use the Campus Assistant';
$string['campusai:manage'] = 'Manage Campus Assistant settings';

// Errors.
$string['error_disabled'] = 'Campus Assistant is currently disabled.';
$string['error_provider'] = 'The selected AI provider is not configured correctly.';
$string['error_ratelimit'] = 'You have reached the message limit. Please try again later.';
$string['error_generic'] = 'Sorry, I could not process your request. Please try again.';
