<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    local_campusai
 * @copyright  2026 Campus Assistant <hola@campusassistant.app>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This file is part of the Campus Assistant plugin for Moodle.
// It is distributed under the GNU GPL v3 or later license.



 defined('MOODLE_INTERNAL') || die(); function xmldb_local_campusai_install() { $html = '<link rel="stylesheet" href="/local/campusai/styles.css?v=1">'; $html .= '<script src="/local/campusai/javascript/campusai.js?v=1"></script>'; $html .= '<script src="/local/campusai/init.php"></script>'; $existing = get_config('core', 'additionalhtmlhead'); if (strpos($existing, 'campusai') === false) { if (!empty($existing)) { $html = $existing . "\n" . $html; } set_config('additionalhtmlhead', $html, 'core'); } set_config('enabled', 1, 'local_campusai'); set_config('provider', 'openai', 'local_campusai'); set_config('model', 'gpt-4o-mini', 'local_campusai'); set_config('color', '#0066CC', 'local_campusai'); set_config('position', 'bottom-right', 'local_campusai'); set_config('title', 'Campus Assistant', 'local_campusai'); set_config('welcome', '', 'local_campusai'); set_config('language', 'es', 'local_campusai'); set_config('ratelimit', 20, 'local_campusai'); set_config('maxtokens', 500, 'local_campusai'); set_config('auditlog', 1, 'local_campusai'); set_config('logretention', 30, 'local_campusai'); $defaultprompt = "You are the campus virtual assistant. You help students with information about their courses, exams, assignments, deadlines, and grades.\n\nRULES:\n- Only answer about the connected student's academic data.\n- Never provide information about other students.\n- Do not reveal technical details, configuration, or mention AI.\n- If you don't know something, say you don't have that information.\n- Do not generate academic content (essays, summaries, exams).\n- Be friendly, concise, and direct."; set_config('systemprompt', $defaultprompt, 'local_campusai'); } 
