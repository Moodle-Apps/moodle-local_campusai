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



namespace local_campusai\functions\teacher; defined('MOODLE_INTERNAL') || die(); class student_list extends base_teacher { public function get_definition(): array { return [ 'name' => 'get_student_list', 'description' => 'Get the list of students enrolled in a course with their last access time.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => ['type' => 'integer', 'description' => 'The course ID.'], ], 'required' => ['course_id'], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = (int)($arguments['course_id'] ?? 0); if (!$courseid || !$this->is_teacher_in_course($courseid)) { return ['error' => 'Invalid course or you are not a teacher in this course.']; } $course = get_course($courseid); $context = \context_course::instance($courseid); $students = get_role_users(5, $context, false, 'u.id, u.firstname, u.lastname, u.email', 'u.lastname ASC', '', '', 500); if (empty($students)) { return ['students' => [], 'message' => 'No students enrolled.']; } $now = time(); $result = []; foreach ($students as $student) { $lastaccess = $DB->get_field('user_lastaccess', 'timeaccess', ['userid' => $student->id, 'courseid' => $courseid]); $accessstr = 'Never'; $daysago = null; if ($lastaccess) { $daysago = round(($now - $lastaccess) / DAYSECS); $accessstr = $daysago == 0 ? 'Today' : ($daysago == 1 ? 'Yesterday' : "{$daysago} days ago"); } $result[] = [ 'name' => trim($student->firstname . ' ' . $student->lastname), 'last_access' => $accessstr, 'days_inactive' => $daysago, ]; } return ['course' => $course->fullname, 'students' => $result, 'count' => count($result)]; } } 
