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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class my_overview extends base_function { public function get_definition(): array { return [ 'name' => 'get_my_overview', 'description' => 'Get a 360-degree overview: enrolled courses, pending tasks, upcoming deadlines, and current grades — all in one call.', 'parameters' => [ 'type' => 'object', 'properties' => new \stdClass(), ], ]; } public function execute(array $arguments): array { $courses_result = (new course_list($this->userid))->execute([]); $tasks_result = (new pending_tasks($this->userid))->execute([]); $deadlines_result = (new deadlines($this->userid))->execute([]); global $DB; $gradeitems = $DB->get_records_sql( 'SELECT gi.id, gi.itemname, gi.courseid, gg.finalgrade, gi.grademax, c.fullname AS coursename
               FROM {grade_items} gi
               JOIN {grade_grades} gg ON gi.id = gg.itemid
               JOIN {course} c ON gi.courseid = c.id
              WHERE gg.userid = ? AND gg.finalgrade IS NOT NULL AND gi.hidden = 0
           ORDER BY c.fullname, gi.sortorder', [$this->userid] ); $gradesclean = []; foreach ($gradeitems as $g) { $gradesclean[] = [ 'course' => $g->coursename, 'item' => $g->itemname ?: 'Course total', 'grade' => round((float)$g->finalgrade, 1) . '/' . round((float)$g->grademax, 0), ]; } return [ 'courses' => $courses_result['courses'] ?? [], 'pending_tasks' => $tasks_result['tasks'] ?? [], 'upcoming_deadlines' => $deadlines_result['deadlines'] ?? [], 'grades' => $gradesclean, 'summary' => [ 'total_courses' => count($courses_result['courses'] ?? []), 'total_pending' => count($tasks_result['tasks'] ?? []), 'total_deadlines' => count($deadlines_result['deadlines'] ?? []), ], ]; } } 
