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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class submission_status extends base_function { public function get_definition(): array { return [ 'name' => 'get_submission_status', 'description' => 'Check if the student has submitted a specific assignment and its status.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'assignment_name' => [ 'type' => 'string', 'description' => 'The name of the assignment to check (partial match).', ], ], 'required' => ['assignment_name'], ], ]; } public function execute(array $arguments): array { global $DB; $search = trim($arguments['assignment_name'] ?? ''); if (empty($search)) { return ['error' => 'Assignment name is required.']; } $courses = enrol_get_users_courses($this->userid); $results = []; foreach ($courses as $course) { $modinfo = get_fast_modinfo($course->id, $this->userid); foreach ($modinfo->get_instances_of('assign') as $cm) { if (!$cm->uservisible) { continue; } if (stripos($cm->name, $search) === false) { continue; } $assign = $DB->get_record('assign', ['id' => $cm->instance], '*', MUST_EXIST); $submission = $DB->get_record('assign_submission', [ 'assignment' => $assign->id, 'userid' => $this->userid, ]); $status = 'not_started'; if ($submission) { $status = $submission->status; } $results[] = [ 'course' => $course->fullname, 'assignment' => $assign->name, 'status' => $status, 'deadline' => $assign->duedate > 0 ? $this->format_date($assign->duedate) : 'No deadline', 'submitted' => $status === 'submitted', ]; } } return ['submissions' => $results]; } } 
