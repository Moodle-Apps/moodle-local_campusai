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



namespace local_campusai\functions\admin; class campus_stats extends base_admin { public function get_definition(): array { return [ 'name' => 'get_campus_stats', 'description' => 'Get overall campus statistics: total courses, active students, teachers, and enrollments.', 'parameters' => ['type' => 'object', 'properties' => new \stdClass()], ]; } public function execute(array $arguments): array { global $DB; $totalcourses = $DB->count_records('course', ['category' => 0]) + $DB->count_records_select('course', 'category > 0'); $totalcourses--; $activestudents = $DB->count_records_select('role_assignments', "roleid = 5"); $totalteachers = $DB->count_records_select('role_assignments', "roleid IN (2,3,4)"); $totalenrolments = $DB->count_records('user_enrolments', []); $activeusers7 = $DB->count_records_select('user', "lastlogin > ? AND deleted = 0 AND suspended = 0", [time() - (7 * DAYSECS)]); return [ 'total_courses' => (int)$totalcourses, 'active_students' => (int)$activestudents, 'teachers' => (int)$totalteachers, 'total_enrollments' => (int)$totalenrolments, 'users_logged_in_7days' => (int)$activeusers7, ]; } } 
