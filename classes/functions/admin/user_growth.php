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



namespace local_campusai\functions\admin; defined('MOODLE_INTERNAL') || die(); class user_growth extends base_admin { public function get_definition(): array { return [ 'name' => 'get_user_growth', 'description' => 'Get new user registrations and enrolments over a recent period.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'days' => ['type' => 'integer', 'description' => 'Number of days to analyze (default 30).'], ], ], ]; } public function execute(array $arguments): array { global $DB; $days = (int)($arguments['days'] ?? 30); $cutoff = time() - ($days * DAYSECS); $newusers = $DB->count_records_select('user', "timecreated >= ? AND deleted = 0 AND suspended = 0", [$cutoff]); $newenrolments = $DB->count_records_select('user_enrolments', "timemodified >= ?", [$cutoff]); $sql = "SELECT FROM_UNIXTIME(timecreated, '%Y-%m-%d') AS day,
                       COUNT(*) AS count
                  FROM {user}
                 WHERE timecreated >= ? AND deleted = 0 AND suspended = 0
              GROUP BY day ORDER BY day ASC"; $daily = $DB->get_records_sql($sql, [$cutoff]); $trend = []; foreach ($daily as $day) { $trend[] = ['date' => $day->day, 'new_users' => (int)$day->count]; } return [ 'period_days' => $days, 'new_users' => (int)$newusers, 'new_enrolments' => (int)$newenrolments, 'daily_trend' => $trend, ]; } } 
