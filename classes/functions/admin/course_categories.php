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



namespace local_campusai\functions\admin; defined('MOODLE_INTERNAL') || die(); class course_categories extends base_admin { public function get_definition(): array { return [ 'name' => 'get_course_categories', 'description' => 'Get the course category tree with total courses and enrolments per category.', 'parameters' => ['type' => 'object', 'properties' => new \stdClass()], ]; } public function execute(array $arguments): array { global $DB; $categories = $DB->get_records('course_categories', [], 'sortorder ASC', 'id, name, parent, description'); $coursecounts = $DB->get_records_sql( "SELECT category, COUNT(*) AS cnt FROM {course} WHERE id > 1 GROUP BY category" ); $enrolcounts = $DB->get_records_sql( "SELECT c.category, COUNT(ue.id) AS cnt
               FROM {course} c
               JOIN {enrol} e ON e.courseid = c.id
               JOIN {user_enrolments} ue ON ue.enrolid = e.id
              WHERE c.id > 1
           GROUP BY c.category" ); $result = []; foreach ($categories as $cat) { $result[] = [ 'id' => (int)$cat->id, 'name' => $cat->name, 'parent' => (int)$cat->parent, 'courses' => (int)($coursecounts[$cat->id]->cnt ?? 0), 'enrolments' => (int)($enrolcounts[$cat->id]->cnt ?? 0), ]; } return ['categories' => $result]; } } 
