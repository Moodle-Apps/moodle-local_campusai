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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class course_grades extends base_function { public function get_definition(): array { return [ 'name' => 'get_course_grades', 'description' => 'Get published grades and marks for the student across all enrolled courses.', 'parameters' => [ 'type' => 'object', 'properties' => new \stdClass(), ], ]; } public function execute(array $arguments): array { global $DB, $CFG; $courses = enrol_get_users_courses($this->userid); $grades = []; require_once($CFG->dirroot . '/lib/gradelib.php'); require_once($CFG->dirroot . '/lib/grade/grade_item.php'); require_once($CFG->dirroot . '/lib/grade/grade_grade.php'); foreach ($courses as $course) { $gradeitems = \grade_item::fetch_all(['courseid' => $course->id]); if (empty($gradeitems)) { continue; } $coursegrades = []; foreach ($gradeitems as $item) { if ($item->hidden != 0 || $item->gradetype == GRADE_TYPE_NONE) { continue; } $grade = \grade_grade::fetch([ 'itemid' => $item->id, 'userid' => $this->userid, ]); if ($grade && !$grade->is_hidden() && !is_null($grade->finalgrade)) { $coursename = $item->itemname ?: $course->fullname; $formatted = grade_format_gradevalue($grade->finalgrade, $item, true); $coursegrades[] = [ 'item' => $item->itemname ?: 'Course total', 'grade' => $formatted, ]; } } if (!empty($coursegrades)) { $grades[] = [ 'course' => $course->fullname, 'items' => $coursegrades, ]; } } return ['grades' => $grades]; } } 
