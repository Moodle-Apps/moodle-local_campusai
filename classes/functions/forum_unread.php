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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class forum_unread extends base_function { public function get_definition(): array { return [ 'name' => 'get_forum_unread', 'description' => 'Get the number of unread forum posts across all enrolled courses.', 'parameters' => [ 'type' => 'object', 'properties' => new \stdClass(), ], ]; } public function execute(array $arguments): array { global $DB, $CFG; $courses = enrol_get_users_courses($this->userid); $forums = []; foreach ($courses as $course) { $modinfo = get_fast_modinfo($course->id, $this->userid); foreach ($modinfo->get_instances_of('forum') as $cm) { if (!$cm->visible || !$cm->uservisible) { continue; } $forum = $DB->get_record('forum', ['id' => $cm->instance], '*', IGNORE_MISSING); if (!$forum) { continue; } $sql = "SELECT COUNT(1)
                          FROM {forum_posts} fp
                          JOIN {forum_discussions} fd ON fp.discussion = fd.id
                     LEFT JOIN {forum_read} fr ON fr.postid = fp.id AND fr.userid = ?
                         WHERE fd.forum = ? AND fp.modified > ? AND fr.id IS NULL"; $unread = (int) $DB->count_records_sql($sql, [$this->userid, $forum->id, $forum->timemarked ?? 0]); if ($unread === 0) { $sql2 = "SELECT COUNT(1)
                               FROM {forum_posts} fp
                               JOIN {forum_discussions} fd ON fp.discussion = fd.id
                          LEFT JOIN {forum_read} fr ON fr.postid = fp.id AND fr.userid = ?
                              WHERE fd.forum = ? AND fp.userid != ? AND fr.id IS NULL"; $unread = (int) $DB->count_records_sql($sql2, [$this->userid, $forum->id, $this->userid]); } if ($unread > 0) { $forums[] = [ 'course' => $course->fullname, 'forum' => $forum->name, 'unread_count' => $unread, ]; } } } return ['forums_with_unread' => $forums]; } } 
