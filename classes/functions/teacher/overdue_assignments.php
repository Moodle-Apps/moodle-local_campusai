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



namespace local_campusai\functions\teacher; defined('MOODLE_INTERNAL') || die(); class overdue_assignments extends base_teacher { public function get_definition(): array { return [ 'name' => 'get_overdue_assignments', 'description' => 'Get students who submitted assignments after the due date in a course.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => ['type' => 'integer', 'description' => 'The course ID.'], ], 'required' => ['course_id'], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = (int)($arguments['course_id'] ?? 0); if (!$courseid || !$this->is_teacher_in_course($courseid)) { return ['error' => 'Invalid course or you are not a teacher in this course.']; } $course = get_course($courseid); $modinfo = get_fast_modinfo($courseid, $this->userid); $assigns = $modinfo->get_instances_of('assign'); $result = []; foreach ($assigns as $cm) { if (!$cm->visible) continue; $assign = $DB->get_record('assign', ['id' => $cm->instance], 'name, duedate', IGNORE_MISSING); if (!$assign || $assign->duedate <= 0) continue; $submissions = $DB->get_records_select( 'assign_submission', 'assignment = ? AND status = ? AND timemodified > ?', [$cm->instance, 'submitted', $assign->duedate], 'timemodified DESC', 'userid, timemodified', 0, 50 ); foreach ($submissions as $sub) { $user = \core_user::get_user($sub->userid, 'firstname, lastname'); $lateness = $sub->timemodified - $assign->duedate; $result[] = [ 'assignment' => $assign->name, 'student' => $user ? trim($user->firstname . ' ' . $user->lastname) : 'Unknown', 'due_date' => $this->format_date($assign->duedate), 'submitted' => $this->format_date($sub->timemodified), 'late_by_days' => round($lateness / DAYSECS, 1), ]; } } return ['course' => $course->fullname, 'overdue_submissions' => $result, 'count' => count($result)]; } } 
