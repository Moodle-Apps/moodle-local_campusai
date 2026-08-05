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



namespace local_campusai\functions\admin; class recent_activity extends base_admin { public function get_definition(): array { return [ 'name' => 'get_recent_activity', 'description' => 'Get recent campus activity: new users, new enrollments, recent logins.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'days' => [ 'type' => 'integer', 'description' => 'Activity from the last N days (default 7).', ], ], ], ]; } public function execute(array $arguments): array { global $DB; $days = (int)($arguments['days'] ?? 7); $days = max(1, min(90, $days)); $cutoff = time() - ($days * DAYSECS); $newusers = $DB->count_records_select('user', "timecreated > ? AND deleted = 0", [$cutoff]); $newenrolments = $DB->count_records_select('user_enrolments', "timestart > ?", [$cutoff]); $recentlogins = $DB->count_records_select('user', "lastlogin > ? AND deleted = 0", [$cutoff]); $sql = "SELECT c.fullname, COUNT(l.id) AS log_count
                FROM {logstore_standard_log} l
                JOIN {course} c ON c.id = l.courseid
                WHERE l.timecreated > ? AND l.courseid > 1
                GROUP BY c.fullname
                ORDER BY log_count DESC
                LIMIT 5"; $activecourses = []; $rs = $DB->get_recordset_sql($sql, [$cutoff]); foreach ($rs as $r) { $activecourses[] = ['course' => $r->fullname, 'activity_entries' => (int)$r->log_count]; } $rs->close(); return [ 'days' => $days, 'new_users' => (int)$newusers, 'new_enrollments' => (int)$newenrolments, 'recent_logins' => (int)$recentlogins, 'most_active_courses' => $activecourses, ]; } } 
