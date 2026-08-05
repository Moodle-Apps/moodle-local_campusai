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



namespace local_campusai\functions\admin; class inactive_users extends base_admin { public function get_definition(): array { return [ 'name' => 'get_inactive_students', 'description' => 'Get students who have not logged in recently. Default: 30 days.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'days' => [ 'type' => 'integer', 'description' => 'Number of days of inactivity (default 30).', ], ], ], ]; } public function execute(array $arguments): array { global $DB; $days = (int)($arguments['days'] ?? 30); $days = max(1, min(365, $days)); $cutoff = time() - ($days * DAYSECS); $sql = "SELECT u.id, u.firstname, u.lastname, u.lastlogin, u.email
                FROM {user} u
                JOIN {role_assignments} ra ON ra.userid = u.id
                WHERE ra.roleid = 5
                AND u.deleted = 0 AND u.suspended = 0
                AND (u.lastlogin < ? OR u.lastlogin IS NULL OR u.lastlogin = 0)
                ORDER BY u.lastlogin ASC
                LIMIT 50"; $users = $DB->get_records_sql($sql, [$cutoff]); $result = []; foreach ($users as $u) { $lastlogin = $u->lastlogin > 0 ? $this->format_date($u->lastlogin) : 'Never'; $result[] = [ 'name' => fullname($u), 'email' => $u->email, 'last_login' => $lastlogin, ]; } return [ 'inactive_students' => $result, 'total_inactive' => count($result), 'days_threshold' => $days, ]; } } 
