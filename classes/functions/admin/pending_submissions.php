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



namespace local_campusai\functions\admin; class pending_submissions extends base_admin { public function get_definition(): array { return [ 'name' => 'get_pending_submissions_overview', 'description' => 'Get an overview of assignments with the most unsubmitted work across all courses.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'limit' => [ 'type' => 'integer', 'description' => 'Max assignments to return (default 10).', ], ], ], ]; } public function execute(array $arguments): array { global $DB; $limit = (int)($arguments['limit'] ?? 10); $limit = max(1, min(50, $limit)); $sql = "SELECT a.id, a.name, c.fullname AS course,
                       COUNT(DISTINCT ra.userid) AS total_students,
                       COUNT(DISTINCT sub.userid) AS submitted
                FROM {assign} a
                JOIN {course} c ON c.id = a.course
                JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = 50
                JOIN {role_assignments} ra ON ra.contextid = ctx.id AND ra.roleid = 5
                LEFT JOIN {assign_submission} sub ON sub.assignment = a.id AND sub.userid = ra.userid AND sub.status = 'submitted'
                WHERE c.id > 1 AND a.duedate > 0
                GROUP BY a.id, a.name, c.fullname
                ORDER BY (COUNT(DISTINCT ra.userid) - COUNT(DISTINCT sub.userid)) DESC"; $results = $DB->get_records_sql($sql, [], 0, $limit); $assignments = []; foreach ($results as $r) { if ($r->submitted >= $r->total_students) { continue; } $assignments[] = [ 'course' => $r->course, 'assignment' => $r->name, 'total_students' => (int)$r->total_students, 'submitted' => (int)$r->submitted, 'not_submitted' => (int)($r->total_students - $r->submitted), ]; } return ['assignments' => $assignments]; } } 
