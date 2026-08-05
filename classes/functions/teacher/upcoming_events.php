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



namespace local_campusai\functions\teacher; defined('MOODLE_INTERNAL') || die(); class upcoming_events extends base_teacher { public function get_definition(): array { return [ 'name' => 'get_upcoming_course_events', 'description' => 'Get upcoming calendar events in your teaching courses for the next 14 days.', 'parameters' => [ 'type' => 'object', 'properties' => new \stdClass(), ], ]; } public function execute(array $arguments): array { global $DB; $courseids = $this->get_teaching_course_ids(); if (empty($courseids)) { return ['events' => [], 'message' => 'You are not a teacher in any course.']; } $now = time(); $future = $now + (14 * DAYSECS); list($insql, $inparams) = $DB->get_in_or_equal($courseids); $params = array_merge([$now, $future], $inparams); $events = $DB->get_records_select( 'event', "timestart >= ? AND timestart <= ? AND courseid $insql", $params, 'timestart ASC', 'id, name, timestart, courseid, eventtype', 0, 30 ); $courses = $DB->get_records_list('course', 'id', $courseids, '', 'id, fullname'); $result = []; foreach ($events as $event) { $result[] = [ 'name' => $event->name, 'course' => $courses[$event->courseid]->fullname ?? 'Unknown', 'date' => $this->format_date($event->timestart), 'type' => $event->eventtype, ]; } return ['events' => $result]; } } 
