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



 namespace local_campusai\functions\teacher; defined('MOODLE_INTERNAL') || die(); abstract class base_teacher extends \local_campusai\functions\base_function { protected function get_teaching_course_ids(): array { global $DB; $teachershortnames = ['teacher', 'editingteacher', 'coursecreator', 'teachers', 'noneditingteacher', 'profesor', 'docente']; $courseids = []; $courses = enrol_get_users_courses($this->userid); foreach ($courses as $course) { $context = \context_course::instance($course->id); $roles = get_user_roles($context, $this->userid, false); foreach ($roles as $role) { if (in_array($role->shortname, $teachershortnames)) { $courseids[] = $course->id; break; } } } return $courseids; } protected function is_teacher_in_course(int $courseid): bool { $teachershortnames = ['teacher', 'editingteacher', 'coursecreator', 'teachers', 'noneditingteacher', 'profesor', 'docente']; $context = \context_course::instance($courseid); $roles = get_user_roles($context, $this->userid, false); foreach ($roles as $role) { if (in_array($role->shortname, $teachershortnames)) { return true; } } return false; } } 
