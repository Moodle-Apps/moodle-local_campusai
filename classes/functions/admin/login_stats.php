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



namespace local_campusai\functions\admin; defined('MOODLE_INTERNAL') || die(); class login_stats extends base_admin { public function get_definition(): array { return [ 'name' => 'get_login_stats', 'description' => 'Get daily login statistics and peak concurrency for recent days.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'days' => ['type' => 'integer', 'description' => 'Number of days to analyze (default 7).'], ], ], ]; } public function execute(array $arguments): array { global $DB; $days = (int)($arguments['days'] ?? 7); $cutoff = time() - ($days * DAYSECS); $sql = "SELECT FROM_UNIXTIME(timecreated, '%Y-%m-%d') AS day,
                       COUNT(*) AS total_logins,
                       COUNT(DISTINCT userid) AS unique_users
                  FROM {logstore_standard_log}
                 WHERE eventname LIKE '%login%' AND timecreated >= ?
              GROUP BY day ORDER BY day ASC"; $daily = $DB->get_records_sql($sql, [$cutoff]); $trend = []; $totallogins = 0; $totalunique = 0; foreach ($daily as $day) { $trend[] = [ 'date' => $day->day, 'logins' => (int)$day->total_logins, 'unique_users' => (int)$day->unique_users, ]; $totallogins += $day->total_logins; $totalunique = max($totalunique, (int)$day->unique_users); } $sql = "SELECT FROM_UNIXTIME(timecreated, '%H') AS hour,
                       COUNT(DISTINCT userid) AS users
                  FROM {logstore_standard_log}
                 WHERE eventname LIKE '%login%' AND timecreated >= ?
              GROUP BY hour ORDER BY users DESC LIMIT 5"; $peaks = $DB->get_records_sql($sql, [$cutoff]); $peakhours = []; foreach ($peaks as $peak) { $peakhours[] = ['hour' => $peak->hour . ':00', 'unique_users' => (int)$peak->users]; } return [ 'period_days' => $days, 'total_logins' => (int)$totallogins, 'peak_unique_users_day' => $totalunique, 'daily_trend' => $trend, 'peak_hours' => $peakhours, ]; } } 
