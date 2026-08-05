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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class course_progress extends base_function { public function get_definition(): array { return [ 'name' => 'get_course_progress', 'description' => 'Get the completion percentage and progress details for a specific course.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => [ 'type' => 'integer', 'description' => 'The course ID.', ], ], 'required' => ['course_id'], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = (int)($arguments['course_id'] ?? 0); if ($courseid <= 0) { return ['error' => 'A valid course ID is required.']; } if (!$this->is_enrolled($courseid)) { return ['error' => 'You are not enrolled in this course.']; } $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST); $completion = new \completion_info($course); if (!$completion->is_enabled()) { return [ 'course' => $course->fullname, 'enabled' => false, 'message' => 'Completion tracking is not enabled for this course.', ]; } $modinfo = get_fast_modinfo($courseid, $this->userid); $activities = $completion->get_activities(); if (empty($activities)) { return [ 'course' => $course->fullname, 'enabled' => true, 'total' => 0, 'completed' => 0, 'percentage' => 0, ]; } $completed = 0; $details = []; foreach ($activities as $activity) { $iscomplete = $completion->is_activity_complete($this->userid, $activity); if ($iscomplete) { $completed++; } $details[] = [ 'activity' => $activity->name, 'type' => $activity->modname, 'completed' => $iscomplete, ]; } $percentage = round(($completed / count($activities)) * 100); return [ 'course' => $course->fullname, 'enabled' => true, 'total' => count($activities), 'completed' => $completed, 'percentage' => $percentage, 'details' => $details, ]; } } 
