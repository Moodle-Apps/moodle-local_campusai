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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class next_activity extends base_function { public function get_definition(): array { return [ 'name' => 'get_next_activity', 'description' => 'Get the next recommended pending activity based on completion tracking. Optionally specify a course.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => [ 'type' => 'integer', 'description' => 'Optional course ID. If omitted, checks all enrolled courses.', ], ], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = $arguments['course_id'] ?? null; $courses = enrol_get_users_courses($this->userid); if ($courseid) { $courses = isset($courses[$courseid]) ? [$courseid => $courses[$courseid]] : []; } if (empty($courses)) { return ['next_activity' => null, 'message' => 'No courses found.']; } $recommended = []; foreach ($courses as $course) { $completion = new \completion_info($course); if (!$completion->is_enabled()) { continue; } $modinfo = get_fast_modinfo($course->id, $this->userid); $cms = $modinfo->get_cms(); foreach ($cms as $cm) { if (!$cm->visible || !$cm->completion) { continue; } $data = $completion->get_data($cm, false, $this->userid); if ($data->completionstate == COMPLETION_COMPLETE || $data->completionstate == COMPLETION_COMPLETE_PASS) { continue; } $recommended[] = [ 'course' => $course->fullname, 'activity' => $cm->name, 'type' => $cm->modname, 'url' => (string)(new \moodle_url('/mod/' . $cm->modname . '/view.php', ['id' => $cm->id])), ]; break; } } return ['next_activities' => $recommended]; } } 
