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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class course_calendar extends base_function { public function get_definition(): array { return [ 'name' => 'get_course_calendar', 'description' => 'Get upcoming calendar events (exams, workshops, activities) for the next 14 days across all enrolled courses.', 'parameters' => [ 'type' => 'object', 'properties' => new \stdClass(), ], ]; } public function execute(array $arguments): array { global $DB; $now = time(); $future = $now + (14 * DAYSECS); $courses = enrol_get_users_courses($this->userid); $courseids = array_keys($courses); if (empty($courseids)) { return ['events' => [], 'message' => 'You are not enrolled in any courses.']; } list($insql, $inparams) = $DB->get_in_or_equal($courseids); $params = array_merge([$now, $future], $inparams); $events = $DB->get_records_select( 'event', "timestart >= ? AND timestart <= ? AND (courseid $insql OR (courseid = 1 AND userid = 0))", $params, 'timestart ASC', 'id, name, timestart, timeduration, courseid, eventtype, description', 0, 30 ); $result = []; foreach ($events as $event) { $coursename = $courses[$event->courseid]->fullname ?? 'General'; $result[] = [ 'name' => $event->name, 'course' => $coursename, 'date' => $this->format_date($event->timestart), 'type' => $event->eventtype, 'timestamp' => $event->timestart, ]; } return ['events' => $result]; } } 
