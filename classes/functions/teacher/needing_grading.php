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



namespace local_campusai\functions\teacher; defined('MOODLE_INTERNAL') || die(); class needing_grading extends base_teacher { public function get_definition(): array { return [ 'name' => 'get_students_needing_grading', 'description' => 'Get student submissions that are pending your grading. Optionally filter by course.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => ['type' => 'integer', 'description' => 'Optional course ID to filter.'], ], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = $arguments['course_id'] ?? null; $courseids = $courseid ? (in_array($courseid, $this->get_teaching_course_ids()) ? [$courseid] : []) : $this->get_teaching_course_ids(); if (empty($courseids)) { return ['pending' => [], 'message' => 'No teaching courses found.']; } $result = []; foreach ($courseids as $cid) { $modinfo = get_fast_modinfo($cid, $this->userid); $assigns = $modinfo->get_instances_of('assign'); foreach ($assigns as $cm) { if (!$cm->visible) continue; $submissions = $DB->get_records_select( 'assign_submission', 'assignment = ? AND status = ?', [$cm->instance, 'submitted'], 'timemodified DESC', 'id, userid, timemodified' ); foreach ($submissions as $sub) { $grade = $DB->get_record('assign_grades', ['assignment' => $cm->instance, 'userid' => $sub->userid], 'id', IGNORE_MISSING); if (!$grade) { $user = \core_user::get_user($sub->userid, 'firstname, lastname'); $course = get_course($cid); $result[] = [ 'course' => $course->fullname, 'assignment' => $cm->name, 'student' => $user ? trim($user->firstname . ' ' . $user->lastname) : 'Unknown', 'submitted' => $this->format_date($sub->timemodified), ]; } } } } return ['pending_grading' => $result, 'count' => count($result)]; } } 
