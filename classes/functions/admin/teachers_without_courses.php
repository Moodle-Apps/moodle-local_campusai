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



namespace local_campusai\functions\admin; defined('MOODLE_INTERNAL') || die(); class teachers_without_courses extends base_admin { public function get_definition(): array { return [ 'name' => 'get_teachers_without_courses', 'description' => 'Get users with teacher or editingteacher role who have no course assigned.', 'parameters' => ['type' => 'object', 'properties' => new \stdClass()], ]; } public function execute(array $arguments): array { global $DB; $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname
                  FROM {user} u
                  JOIN {role_assignments} ra ON ra.userid = u.id
                  JOIN {role} r ON ra.roleid = r.id
                 WHERE r.shortname IN ('teacher', 'editingteacher')
                   AND u.deleted = 0 AND u.suspended = 0"; $teachers = $DB->get_records_sql($sql); $result = []; foreach ($teachers as $teacher) { $hascourse = $DB->record_exists_sql( "SELECT 1
                   FROM {role_assignments} ra
                   JOIN {context} ctx ON ra.contextid = ctx.id
                   JOIN {role} r ON ra.roleid = r.id
                  WHERE ra.userid = ? AND r.shortname IN ('teacher', 'editingteacher')
                    AND ctx.contextlevel = ?", [$teacher->id, CONTEXT_COURSE] ); if (!$hascourse) { $courses = enrol_get_users_courses($teacher->id); $isteacheranywhere = false; foreach ($courses as $course) { $context = \context_course::instance($course->id); $roles = get_user_roles($context, $teacher->id, false); foreach ($roles as $role) { if (in_array($role->shortname, ['teacher', 'editingteacher'])) { $isteacheranywhere = true; break 2; } } } if (!$isteacheranywhere) { $result[] = [ 'name' => trim($teacher->firstname . ' ' . $teacher->lastname), ]; } } } return ['teachers_without_courses' => $result, 'count' => count($result)]; } } 
