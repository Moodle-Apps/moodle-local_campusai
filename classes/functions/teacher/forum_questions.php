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



namespace local_campusai\functions\teacher; defined('MOODLE_INTERNAL') || die(); class forum_questions extends base_teacher { public function get_definition(): array { return [ 'name' => 'get_forum_replies', 'description' => 'Get forum discussion threads that have no reply from you (the teacher) in a course.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => ['type' => 'integer', 'description' => 'The course ID.'], ], 'required' => ['course_id'], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = (int)($arguments['course_id'] ?? 0); if (!$courseid || !$this->is_teacher_in_course($courseid)) { return ['error' => 'Invalid course or you are not a teacher in this course.']; } $course = get_course($courseid); $modinfo = get_fast_modinfo($courseid, $this->userid); $forums = $modinfo->get_instances_of('forum'); $unanswered = []; foreach ($forums as $cm) { if (!$cm->visible) continue; $discussions = $DB->get_records('forum_discussions', ['forum' => $cm->instance], 'timemodified DESC', 'id, name, userid, timemodified', 0, 50); foreach ($discussions as $disc) { $teacherreply = $DB->record_exists('forum_posts', [ 'discussion' => $disc->id, 'userid' => $this->userid, ]); if (!$teacherreply) { $author = \core_user::get_user($disc->userid, 'firstname, lastname'); $unanswered[] = [ 'forum' => $cm->name, 'discussion' => $disc->name, 'author' => $author ? trim($author->firstname . ' ' . $author->lastname) : 'Unknown', 'date' => $this->format_date($disc->timemodified), 'url' => (string)(new \moodle_url('/mod/forum/discuss.php', ['d' => $disc->id])), ]; } } } return ['course' => $course->fullname, 'unanswered_threads' => $unanswered, 'count' => count($unanswered)]; } } 
