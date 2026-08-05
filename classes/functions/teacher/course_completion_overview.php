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



namespace local_campusai\functions\teacher; defined('MOODLE_INTERNAL') || die(); class course_completion_overview extends base_teacher { public function get_definition(): array { return [ 'name' => 'get_course_completion_overview', 'description' => 'Get the completion percentage for each student in a course.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => ['type' => 'integer', 'description' => 'The course ID.'], ], 'required' => ['course_id'], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = (int)($arguments['course_id'] ?? 0); if (!$courseid || !$this->is_teacher_in_course($courseid)) { return ['error' => 'Invalid course or you are not a teacher in this course.']; } $course = get_course($courseid); $completion = new \completion_info($course); if (!$completion->is_enabled()) { return ['course' => $course->fullname, 'message' => 'Completion tracking not enabled.']; } $context = \context_course::instance($courseid); $students = get_role_users(5, $context, false, 'u.id, u.firstname, u.lastname', 'u.lastname ASC', '', '', 500); $modinfo = get_fast_modinfo($courseid); $cms = $modinfo->get_cms(); $trackable = array_filter($cms, fn($cm) => $cm->completion); if (empty($trackable)) { return ['course' => $course->fullname, 'message' => 'No trackable activities.']; } $result = []; foreach ($students as $student) { $completed = 0; foreach ($trackable as $cm) { $data = $completion->get_data($cm, false, $student->id); if ($data->completionstate == COMPLETION_COMPLETE || $data->completionstate == COMPLETION_COMPLETE_PASS) { $completed++; } } $pct = round(($completed / count($trackable)) * 100); $result[] = [ 'name' => trim($student->firstname . ' ' . $student->lastname), 'completion' => $pct . '%', 'completed_activities' => $completed, 'total_activities' => count($trackable), ]; } return ['course' => $course->fullname, 'students' => $result]; } } 
