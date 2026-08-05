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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class teacher_info extends base_function { public function get_definition(): array { return [ 'name' => 'get_teacher_info', 'description' => 'Get the names and contact information of teachers/tutors for a specific course.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => [ 'type' => 'integer', 'description' => 'The course ID.', ], ], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = (int)($arguments['course_id'] ?? 0); if ($courseid <= 0) { return ['error' => 'A valid course ID is required.']; } if (!$this->is_enrolled($courseid)) { return ['error' => 'You are not enrolled in this course.']; } $context = \context_course::instance($courseid); $teachingroles = [ get_config('moodle', 'teacherrole') ?: 3, get_config('moodle', 'editingteacherrole') ?: 2, ]; $teachers = []; foreach ($teachingroles as $roleid) { $roleusers = get_role_users($roleid, $context, false, 'u.id, u.firstname, u.lastname, u.email', 'lastname ASC'); foreach ($roleusers as $user) { $teachers[] = [ 'name' => fullname($user), 'email' => $user->email, 'role' => $roleid == 2 ? 'Teacher' : 'Tutor', ]; } } $seen = []; $unique = []; foreach ($teachers as $t) { if (!isset($seen[$t['name']])) { $seen[$t['name']] = true; $unique[] = $t; } } return ['teachers' => $unique]; } } 
