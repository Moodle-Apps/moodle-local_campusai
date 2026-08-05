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



namespace local_campusai\functions\admin; defined('MOODLE_INTERNAL') || die(); class course_completion_stats extends base_admin { public function get_definition(): array { return [ 'name' => 'get_course_completion_stats', 'description' => 'Get course completion rates across the campus (top N courses).', 'parameters' => [ 'type' => 'object', 'properties' => [ 'limit' => ['type' => 'integer', 'description' => 'Max courses to return (default 20).'], ], ], ]; } public function execute(array $arguments): array { global $DB; $limit = (int)($arguments['limit'] ?? 20); $limit = max(1, min($limit, 100)); $sql = "SELECT c.id, c.fullname,
                       (SELECT COUNT(DISTINCT ue.userid)
                          FROM {enrol} e
                          JOIN {user_enrolments} ue ON ue.enrolid = e.id
                         WHERE e.courseid = c.id) AS enrolled,
                       (SELECT COUNT(DISTINCT cc.userid)
                          FROM {course_completions} cc
                         WHERE cc.course = c.id AND cc.timecompleted IS NOT NULL) AS completed
                  FROM {course} c
                 WHERE c.id > 1
                   AND EXISTS (SELECT 1 FROM {enrol} e2
                          JOIN {user_enrolments} ue2 ON ue2.enrolid = e2.id
                         WHERE e2.courseid = c.id)
              ORDER BY enrolled DESC"; $courses = $DB->get_records_sql($sql, [], 0, $limit); $result = []; foreach ($courses as $course) { $rate = $course->enrolled > 0 ? round(($course->completed / $course->enrolled) * 100) : 0; $result[] = [ 'course' => $course->fullname, 'enrolled' => (int)$course->enrolled, 'completed' => (int)$course->completed, 'completion_rate' => $rate . '%', ]; } return ['courses' => $result]; } } 
