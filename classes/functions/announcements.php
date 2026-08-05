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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class announcements extends base_function { public function get_definition(): array { return [ 'name' => 'get_announcements', 'description' => 'Get recent course announcements from the last 7 days. Optionally filter by course.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => [ 'type' => 'integer', 'description' => 'Optional course ID to filter announcements.', ], ], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = $arguments['course_id'] ?? null; $cutoff = time() - (7 * DAYSECS); $courses = enrol_get_users_courses($this->userid); $courseids = array_keys($courses); if (empty($courseids)) { return ['announcements' => [], 'message' => 'No courses enrolled.']; } if ($courseid) { $courseids = in_array($courseid, $courseids) ? [$courseid] : []; } if (empty($courseids)) { return ['announcements' => [], 'message' => 'Invalid course.']; } list($insql, $inparams) = $DB->get_in_or_equal($courseids); $params = array_merge([$cutoff], $inparams); $sql = "SELECT fp.subject, fp.message, fp.modified, f.course AS courseid, f.name AS forumname,
                       c.fullname AS coursename, u.firstname, u.lastname
                  FROM {forum_posts} fp
                  JOIN {forum_discussions} fd ON fp.discussion = fd.id
                  JOIN {forum} f ON fd.forum = f.id
                  JOIN {course} c ON f.course = c.id
                  JOIN {user} u ON fp.userid = u.id
                 WHERE f.type = 'news' AND fp.modified >= ? AND f.course $insql
                 ORDER BY fp.modified DESC LIMIT 20"; $posts = $DB->get_records_sql($sql, $params); $result = []; foreach ($posts as $post) { $result[] = [ 'course' => $post->coursename, 'subject' => $post->subject, 'author' => trim($post->firstname . ' ' . $post->lastname), 'date' => $this->format_date($post->modified), 'preview' => substr(strip_tags($post->message), 0, 200), ]; } return ['announcements' => $result]; } } 
