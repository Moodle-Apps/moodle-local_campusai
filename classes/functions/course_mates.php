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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class course_mates extends base_function { public function get_definition(): array { return [ 'name' => 'get_course_mates', 'description' => 'Get the list of classmates in a course (names only, no contact info).', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => [ 'type' => 'integer', 'description' => 'The course ID.', ], ], 'required' => ['course_id'], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = (int)($arguments['course_id'] ?? 0); if (!$courseid || !$this->is_enrolled($courseid)) { return ['mates' => [], 'message' => 'Invalid course or not enrolled.']; } $context = \context_course::instance($courseid); $students = get_role_users(5, $context, false, 'u.id, u.firstname, u.lastname', 'u.lastname ASC', '', '', 100); $mates = []; foreach ($students as $student) { if ($student->id == $this->userid) { continue; } $mates[] = [ 'name' => trim($student->firstname . ' ' . $student->lastname), ]; } return ['mates' => $mates, 'count' => count($mates)]; } } 
