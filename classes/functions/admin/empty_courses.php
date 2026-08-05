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



namespace local_campusai\functions\admin; defined('MOODLE_INTERNAL') || die(); class empty_courses extends base_admin { public function get_definition(): array { return [ 'name' => 'get_empty_courses', 'description' => 'Get courses with no students, no activities, or both.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'type' => [ 'type' => 'string', 'description' => 'Filter: no_students, no_content, or both (default both).', ], ], ], ]; } public function execute(array $arguments): array { global $DB; $filter = $arguments['type'] ?? 'both'; $courses = $DB->get_records_select('course', 'id > 1', [], 'fullname ASC', 'id, fullname, shortname'); $nostudents = []; $nocontent = []; foreach ($courses as $course) { $context = \context_course::instance($course->id); $studentcount = count_enrolled_users($context, 5); $modinfo = get_fast_modinfo($course->id); $cms = $modinfo->get_cms(); $activitycount = count($cms); if ($studentcount === 0 && ($filter === 'no_students' || $filter === 'both')) { $nostudents[] = [ 'id' => (int)$course->id, 'name' => $course->fullname, 'activities' => $activitycount, ]; } if ($activitycount === 0 && ($filter === 'no_content' || $filter === 'both')) { $nocontent[] = [ 'id' => (int)$course->id, 'name' => $course->fullname, 'students' => $studentcount, ]; } } $result = []; if ($filter === 'no_students' || $filter === 'both') { $result['no_students'] = $nostudents; } if ($filter === 'no_content' || $filter === 'both') { $result['no_content'] = $nocontent; } return $result; } } 
