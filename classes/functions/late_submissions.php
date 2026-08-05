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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class late_submissions extends base_function { public function get_definition(): array { return [ 'name' => 'get_late_submissions', 'description' => 'Get your assignments that were submitted after the due date.', 'parameters' => [ 'type' => 'object', 'properties' => new \stdClass(), ], ]; } public function execute(array $arguments): array { global $DB; $courses = enrol_get_users_courses($this->userid); $courseids = array_keys($courses); if (empty($courseids)) { return ['late_submissions' => [], 'message' => 'No courses enrolled.']; } $result = []; foreach ($courses as $course) { $modinfo = get_fast_modinfo($course->id, $this->userid); $assigns = $modinfo->get_instances_of('assign'); foreach ($assigns as $cm) { if (!$cm->visible) { continue; } $assign = $DB->get_record('assign', ['id' => $cm->instance], 'id, name, duedate', IGNORE_MISSING); if (!$assign || $assign->duedate <= 0) { continue; } $submission = $DB->get_record('assign_submission', [ 'assignment' => $assign->id, 'userid' => $this->userid, ], 'timemodified, status', IGNORE_MISSING); if (!$submission || $submission->status !== 'submitted') { continue; } if ($submission->timemodified > $assign->duedate) { $lateness = $submission->timemodified - $assign->duedate; $result[] = [ 'course' => $course->fullname, 'assignment' => $assign->name, 'due_date' => $this->format_date($assign->duedate), 'submitted' => $this->format_date($submission->timemodified), 'late_by_days' => round($lateness / DAYSECS, 1), ]; } } } return ['late_submissions' => $result]; } } 
