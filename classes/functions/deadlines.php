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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class deadlines extends base_function { public function get_definition(): array { return [ 'name' => 'get_deadlines', 'description' => 'Get all academic deadlines (assignments, quizzes, workshops) due in the next 7 days.', 'parameters' => [ 'type' => 'object', 'properties' => new \stdClass(), ], ]; } public function execute(array $arguments): array { global $DB; $now = time(); $future = $now + (7 * DAYSECS); $courses = enrol_get_users_courses($this->userid); $deadlines = []; $modtypes = ['assign', 'quiz', 'workshop', 'forum', 'choice', 'lesson']; foreach ($courses as $course) { $modinfo = get_fast_modinfo($course->id, $this->userid); foreach ($modtypes as $modtype) { $instances = $modinfo->get_instances_of($modtype); foreach ($instances as $cm) { if (!$cm->visible || !$cm->uservisible) { continue; } $deadline = 0; switch ($modtype) { case 'assign': $record = $DB->get_record('assign', ['id' => $cm->instance], 'duedate', IGNORE_MISSING); $deadline = $record->duedate ?? 0; break; case 'quiz': $record = $DB->get_record('quiz', ['id' => $cm->instance], 'timeclose', IGNORE_MISSING); $deadline = $record->timeclose ?? 0; break; case 'workshop': $record = $DB->get_record('workshop', ['id' => $cm->instance], 'submissionend, assessmentend', IGNORE_MISSING); $deadline = max($record->submissionend ?? 0, $record->assessmentend ?? 0); break; case 'forum': $record = $DB->get_record('forum', ['id' => $cm->instance], 'duedate', IGNORE_MISSING); $deadline = $record->duedate ?? 0; break; case 'choice': $record = $DB->get_record('choice', ['id' => $cm->instance], 'timeclose', IGNORE_MISSING); $deadline = $record->timeclose ?? 0; break; case 'lesson': $record = $DB->get_record('lesson', ['id' => $cm->instance], 'deadline', IGNORE_MISSING); $deadline = $record->deadline ?? 0; break; } if ($deadline > 0 && $deadline >= $now && $deadline <= $future) { $deadlines[] = [ 'course' => $course->fullname, 'activity' => $cm->name, 'type' => $modtype, 'deadline' => $this->format_date($deadline), 'timestamp' => $deadline, ]; } } } } usort($deadlines, function($a, $b) { return $a['timestamp'] <=> $b['timestamp']; }); return ['deadlines' => $deadlines]; } } 
