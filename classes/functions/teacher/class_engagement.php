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



namespace local_campusai\functions\teacher; defined('MOODLE_INTERNAL') || die(); class class_engagement extends base_teacher { public function get_definition(): array { return [ 'name' => 'get_class_engagement', 'description' => 'Get class engagement metrics: logins, views, and forum participation over recent days.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => ['type' => 'integer', 'description' => 'The course ID.'], 'days' => ['type' => 'integer', 'description' => 'Number of days to analyze (default 30).'], ], 'required' => ['course_id'], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = (int)($arguments['course_id'] ?? 0); $days = (int)($arguments['days'] ?? 30); if (!$courseid || !$this->is_teacher_in_course($courseid)) { return ['error' => 'Invalid course or you are not a teacher in this course.']; } $course = get_course($courseid); $cutoff = time() - ($days * DAYSECS); $logins = $DB->count_records_select( 'logstore_standard_log', 'courseid = ? AND action = ? AND timecreated >= ?', [$courseid, 'viewed', $cutoff] ); $activeusers = $DB->count_records_select( 'logstore_standard_log', 'courseid = ? AND timecreated >= ?', [$courseid, $cutoff], 'COUNT(DISTINCT userid)' ); $forumposts = $DB->count_records_select( 'logstore_standard_log', 'courseid = ? AND component = ? AND timecreated >= ?', [$courseid, 'mod_forum', $cutoff] ); $submissions = $DB->count_records_select( 'logstore_standard_log', 'courseid = ? AND component = ? AND action = ? AND timecreated >= ?', [$courseid, 'mod_assign', 'submitted', $cutoff] ); $sql = "SELECT FROM_UNIXTIME(timecreated, '%Y-%m-%d') AS day,
                       COUNT(DISTINCT userid) AS users,
                       COUNT(*) AS events
                  FROM {logstore_standard_log}
                 WHERE courseid = ? AND timecreated >= ?
              GROUP BY day ORDER BY day ASC"; $daily = $DB->get_records_sql($sql, [$courseid, $cutoff]); $trend = []; foreach ($daily as $day) { $trend[] = ['date' => $day->day, 'active_users' => (int)$day->users, 'events' => (int)$day->events]; } return [ 'course' => $course->fullname, 'period_days' => $days, 'total_events' => (int)$logins, 'active_users' => (int)$activeusers, 'forum_interactions' => (int)$forumposts, 'submissions' => (int)$submissions, 'daily_trend' => $trend, ]; } } 
