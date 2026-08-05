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



namespace local_campusai; defined('MOODLE_INTERNAL') || die(); class hook_callbacks { public static function before_standard_head_html_generation(\core\hook\output\before_standard_head_html_generation $hook): void { global $PAGE, $USER; file_put_contents('/tmp/campusai_debug.log', date('H:i:s') . ' CALLED user=' . ($USER->id ?? 'null') . ' layout=' . ($PAGE->pagelayout ?? 'null') . ' url=' . ($PAGE->url ?? 'null') . "\n", FILE_APPEND); if (!isloggedin()) { file_put_contents('/tmp/campusai_debug.log', '  -> EXIT: not logged in' . "\n", FILE_APPEND); return; } if (isguestuser()) { file_put_contents('/tmp/campusai_debug.log', '  -> EXIT: guest' . "\n", FILE_APPEND); return; } if (!get_config('local_campusai', 'enabled')) { file_put_contents('/tmp/campusai_debug.log', '  -> EXIT: disabled' . "\n", FILE_APPEND); return; } $licensekey = trim((string) get_config('local_campusai', 'licensekey')); if (empty($licensekey)) { return; } try { $licenseStatus = \local_campusai\license_manager::get_status(); if (!$licenseStatus['valid']) { return; } } catch (\Throwable $e) { return; } $context = \context_system::instance(); if (!has_capability('local/campusai:use', $context)) { file_put_contents('/tmp/campusai_debug.log', '  -> EXIT: no capability' . "\n", FILE_APPEND); return; } if ($PAGE->pagelayout === 'admin' || $PAGE->pagelayout === 'maintenance') { file_put_contents('/tmp/campusai_debug.log', '  -> EXIT: admin/maintenance layout' . "\n", FILE_APPEND); return; } file_put_contents('/tmp/campusai_debug.log', '  -> INJECTING JS' . "\n", FILE_APPEND); $config = [ 'ajaxUrl' => (string) new \moodle_url('/local/campusai/ajax.php'), 'sesskey' => sesskey(), 'color' => get_config('local_campusai', 'color') ?: '#0066CC', 'position' => get_config('local_campusai', 'position') ?: 'bottom-right', 'title' => get_config('local_campusai', 'title') ?: 'Campus Assistant', 'welcome' => get_config('local_campusai', 'welcome') ?: 'Hi! How can I help you today?', 'placeholder' => 'Ask me anything...', 'quickExams' => '📅 Exams', 'quickTasks' => '📝 Missing?', 'quickCourses' => '📚 Courses', 'quickExamsText' => 'What exams do I have coming up?', 'quickTasksText' => 'What assignments have I not submitted yet?', 'quickCoursesText' => 'What courses am I enrolled in?', 'errorText' => 'Sorry, I could not process your request.', 'ratelimit' => (int) get_config('local_campusai', 'ratelimit'), ]; $PAGE->requires->css('/local/campusai/styles.css'); $PAGE->requires->js_call_amd('local_campusai/campusai', 'init', [$config]); } } 
