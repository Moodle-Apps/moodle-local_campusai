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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class pending_tasks extends base_function { public function get_definition(): array { return [ 'name' => 'get_pending_tasks', 'description' => 'Get assignments that the student has not yet submitted, with their deadlines.', 'parameters' => [ 'type' => 'object', 'properties' => new \stdClass(), ], ]; } public function execute(array $arguments): array { global $DB; $courses = enrol_get_users_courses($this->userid); $pending = []; foreach ($courses as $course) { $modinfo = get_fast_modinfo($course->id, $this->userid); foreach ($modinfo->get_instances_of('assign') as $cmid => $cm) { if (!$cm->visible) { continue; } $assign = $DB->get_record('assign', ['id' => $cm->instance], '*', MUST_EXIST); $submission = $DB->get_record('assign_submission', [ 'assignment' => $assign->id, 'userid' => $this->userid, ]); $issubmitted = $submission && $submission->status === 'submitted'; if (!$issubmitted && $assign->duedate > 0) { $overdue = $assign->duedate < time(); $pending[] = [ 'course' => $course->fullname, 'task' => $assign->name, 'deadline' => $this->format_date($assign->duedate), 'overdue' => $overdue, 'timestamp' => $assign->duedate, ]; } } } usort($pending, function($a, $b) { return $a['timestamp'] <=> $b['timestamp']; }); return ['pending_tasks' => $pending]; } } 
