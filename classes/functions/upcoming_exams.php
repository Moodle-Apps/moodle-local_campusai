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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class upcoming_exams extends base_function { public function get_definition(): array { return [ 'name' => 'get_upcoming_exams', 'description' => 'Get upcoming exams, tests, and quizzes for the student in the next 30 days.', 'parameters' => [ 'type' => 'object', 'properties' => new \stdClass(), ], ]; } public function execute(array $arguments): array { global $DB; $now = time(); $future = $now + (30 * DAYSECS); $courses = enrol_get_users_courses($this->userid); $courseids = array_keys($courses); if (empty($courseids)) { return ['exams' => [], 'message' => 'You are not enrolled in any courses.']; } $exams = []; foreach ($courses as $course) { $modinfo = get_fast_modinfo($course->id, $this->userid); $cms = $modinfo->get_cms(); foreach ($cms as $cm) { if ($cm->modname !== 'quiz') { continue; } if (!$cm->visible) { continue; } $quiz = $DB->get_record('quiz', ['id' => $cm->instance], 'timeopen, timeclose, name', IGNORE_MISSING); if (!$quiz) { continue; } $examtime = $quiz->timeopen > 0 ? $quiz->timeopen : $quiz->timeclose; if ($examtime > 0 && $examtime >= $now && $examtime <= $future) { $exams[] = [ 'course' => $course->fullname, 'exam' => $quiz->name, 'date' => $this->format_date($examtime), 'timestamp' => $examtime, ]; } } } list($insql, $inparams) = $DB->get_in_or_equal($courseids); $params = array_merge([$now, $future], $inparams); $events = $DB->get_records_select( 'event', "timestart >= ? AND timestart <= ? AND courseid $insql AND (eventtype = 'exam' OR eventtype = 'site')", $params, 'timestart ASC', 'name, timestart, courseid, description', 0, 20 ); foreach ($events as $event) { $exams[] = [ 'course' => $courses[$event->courseid]->fullname ?? 'General', 'exam' => $event->name, 'date' => $this->format_date($event->timestart), 'timestamp' => $event->timestart, ]; } $seen = []; $unique = []; foreach ($exams as $exam) { $key = $exam['exam'] . '|' . $exam['timestamp']; if (!isset($seen[$key])) { $seen[$key] = true; $unique[] = $exam; } } usort($unique, function($a, $b) { return $a['timestamp'] <=> $b['timestamp']; }); return ['exams' => $unique]; } } 
