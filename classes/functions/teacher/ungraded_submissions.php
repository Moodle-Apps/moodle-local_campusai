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



namespace local_campusai\functions\teacher; defined('MOODLE_INTERNAL') || die(); class ungraded_submissions extends base_teacher { public function get_definition(): array { return [ 'name' => 'get_ungraded_submissions', 'description' => 'Get all ungraded student submissions grouped by course.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => ['type' => 'integer', 'description' => 'Optional course ID to filter.'], ], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = $arguments['course_id'] ?? null; $courseids = $courseid ? (in_array($courseid, $this->get_teaching_course_ids()) ? [$courseid] : []) : $this->get_teaching_course_ids(); if (empty($courseids)) { return ['ungraded' => [], 'message' => 'No teaching courses found.']; } $result = []; foreach ($courseids as $cid) { $course = get_course($cid); $modinfo = get_fast_modinfo($cid, $this->userid); $assigns = $modinfo->get_instances_of('assign'); $courseungraded = []; foreach ($assigns as $cm) { if (!$cm->visible) continue; $sql = "SELECT s.userid, s.timemodified
                          FROM {assign_submission} s
                     LEFT JOIN {assign_grades} g ON s.assignment = g.assignment AND s.userid = g.userid
                          WHERE s.assignment = ? AND s.status = 'submitted' AND g.id IS NULL"; $pending = $DB->get_records_sql($sql, [$cm->instance]); foreach ($pending as $p) { $user = \core_user::get_user($p->userid, 'firstname, lastname'); $courseungraded[] = [ 'assignment' => $cm->name, 'student' => $user ? trim($user->firstname . ' ' . $user->lastname) : 'Unknown', 'submitted' => $this->format_date($p->timemodified), ]; } } if (!empty($courseungraded)) { $result[] = [ 'course' => $course->fullname, 'count' => count($courseungraded), 'submissions' => $courseungraded, ]; } } return ['courses' => $result, 'total_ungraded' => array_sum(array_map(fn($c) => $c['count'], $result))]; } } 
