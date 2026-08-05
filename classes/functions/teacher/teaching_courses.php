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



 namespace local_campusai\functions\teacher; defined('MOODLE_INTERNAL') || die(); class teaching_courses extends base_teacher { public function get_definition(): array { return [ 'name' => 'get_my_teaching_courses', 'description' => 'Get the list of courses where you have a teaching role.', 'parameters' => ['type' => 'object', 'properties' => new \stdClass()], ]; } public function execute(array $arguments): array { $courseids = $this->get_teaching_course_ids(); if (empty($courseids)) { return ['courses' => [], 'message' => 'You are not assigned as teacher in any course.']; } global $DB; $courses = $DB->get_records_list('course', 'id', $courseids, 'fullname ASC', 'id, fullname, shortname, visible'); $result = []; foreach ($courses as $course) { $studentcount = count_enrolled_users(\context_course::instance($course->id), 5); $result[] = [ 'id' => (int)$course->id, 'name' => $course->fullname, 'shortname' => $course->shortname, 'students' => $studentcount, 'visible' => (bool)$course->visible, ]; } return ['courses' => $result]; } } 
