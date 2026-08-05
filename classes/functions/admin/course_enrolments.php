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



namespace local_campusai\functions\admin; class course_enrolments extends base_admin { public function get_definition(): array { return [ 'name' => 'get_course_enrolments', 'description' => 'Get enrolment count per course, sorted by most enrolled.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'limit' => [ 'type' => 'integer', 'description' => 'Max number of courses to return (default 10).', ], ], ], ]; } public function execute(array $arguments): array { global $DB; $limit = (int)($arguments['limit'] ?? 10); $limit = max(1, min(50, $limit)); $sql = "SELECT c.id, c.fullname, c.shortname, COUNT(ue.id) AS enrolled
                FROM {course} c
                JOIN {enrol} e ON e.courseid = c.id
                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                WHERE c.id > 1 AND c.visible = 1
                GROUP BY c.id, c.fullname, c.shortname
                ORDER BY enrolled DESC
                LIMIT " . $limit; $results = $DB->get_records_sql($sql); $courses = []; foreach ($results as $r) { $courses[] = [ 'course' => $r->fullname, 'shortname' => $r->shortname, 'enrolled' => (int)$r->enrolled, ]; } return ['courses' => $courses]; } } 
