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



namespace local_campusai\functions\teacher; defined('MOODLE_INTERNAL') || die(); class class_progress extends base_teacher { public function get_definition(): array { return [ 'name' => 'get_class_progress', 'description' => 'Get the average completion progress of all students in a course.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => ['type' => 'integer', 'description' => 'The course ID.'], ], 'required' => ['course_id'], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = (int)($arguments['course_id'] ?? 0); if (!$courseid || !$this->is_teacher_in_course($courseid)) { return ['error' => 'Invalid course or you are not a teacher in this course.']; } $course = get_course($courseid); $completion = new \completion_info($course); if (!$completion->is_enabled()) { return ['course' => $course->fullname, 'message' => 'Completion tracking is not enabled for this course.']; } $context = \context_course::instance($courseid); $students = get_role_users(5, $context, false, 'u.id', '', '', '', 500); if (empty($students)) { return ['course' => $course->fullname, 'average_progress' => 0, 'message' => 'No students enrolled.']; } $modinfo = get_fast_modinfo($courseid); $cms = $modinfo->get_cms(); $trackable = []; foreach ($cms as $cm) { if ($cm->completion) { $trackable[] = $cm; } } if (empty($trackable)) { return ['course' => $course->fullname, 'average_progress' => 0, 'message' => 'No trackable activities.']; } $totalcompletion = 0; $perstudent = []; foreach ($students as $student) { $completed = 0; foreach ($trackable as $cm) { $data = $completion->get_data($cm, false, $student->id); if ($data->completionstate == COMPLETION_COMPLETE || $data->completionstate == COMPLETION_COMPLETE_PASS) { $completed++; } } $pct = round(($completed / count($trackable)) * 100); $perstudent[] = $pct; $totalcompletion += $pct; } $avg = round($totalcompletion / count($students)); return [ 'course' => $course->fullname, 'student_count' => count($students), 'trackable_activities' => count($trackable), 'average_progress' => $avg, 'distribution' => [ 'above_75' => count(array_filter($perstudent, fn($p) => $p >= 75)), '50_to_75' => count(array_filter($perstudent, fn($p) => $p >= 50 && $p < 75)), '25_to_50' => count(array_filter($perstudent, fn($p) => $p >= 25 && $p < 50)), 'below_25' => count(array_filter($perstudent, fn($p) => $p < 25)), ], ]; } } 
