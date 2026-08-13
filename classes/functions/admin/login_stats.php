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

namespace local_campusai\functions\admin;

/**
 * Login statistics function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class login_stats extends base_admin {
    /**
     * Returns the function name.
     *
     * @return string
     */
    public static function name(): string {
        return 'admin_login_stats';
    }

    /**
     * Returns the function description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_admin_login_stats_description', 'local_campusai');
    }

    /**
     * Returns example questions for the widget.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What are the login statistics?',
            'Show daily login counts.',
        ];
    }

    /**
     * Returns the function parameters schema.
     *
     * @return array
     */
    public static function parameters(): array {
        return [
            'type' => 'object',
            'properties' => [
                'period' => [
                    'type' => 'string',
                    'enum' => ['daily', 'weekly', 'monthly'],
                    'description' => get_string('function_admin_login_stats_param_period', 'local_campusai'),
                    'default' => 'daily',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => get_string('function_admin_login_stats_param_limit', 'local_campusai'),
                    'default' => 7,
                ],
            ],
            'required' => [],
        ];
    }

    /**
     * Executes the function.
     *
     * @param int $userid User ID.
     * @param array $args Arguments from the LLM.
     * @return string
     */
    public function execute(int $userid, array $args): string {
        global $DB;

        if (!has_capability('local/campusai:manage', \context_system::instance(), $userid)) {
            return get_string('function_admin_login_stats_permission', 'local_campusai');
        }

        $period = isset($args['period']) ? strtolower($args['period']) : 'daily';
        if (!in_array($period, ['daily', 'weekly', 'monthly'])) {
            $period = 'daily';
        }
        $limit = isset($args['limit']) ? (int) $args['limit'] : 7;
        $limit = min(max($limit, 1), 90);

        switch ($period) {
            case 'weekly':
                $label = get_string('function_admin_login_stats_label_week', 'local_campusai');
                $threshold = strtotime("-{$limit} weeks");
                break;
            case 'monthly':
                $label = get_string('function_admin_login_stats_label_month', 'local_campusai');
                $threshold = strtotime("-{$limit} months");
                break;
            case 'daily':
            default:
                $label = get_string('function_admin_login_stats_label_day', 'local_campusai');
                $threshold = strtotime("-{$limit} days");
                break;
        }

        $sql = "SELECT id, timecreated, userid
                  FROM {logstore_standard_log}
                 WHERE action = 'loggedin'
                   AND timecreated > :threshold
              ORDER BY timecreated DESC";

        $records = $DB->get_records_sql($sql, ['threshold' => $threshold]);

        if (empty($records)) {
            return get_string('function_admin_login_stats_empty', 'local_campusai');
        }

        $grouped = [];
        foreach ($records as $record) {
            $timestamp = (int) $record->timecreated;
            switch ($period) {
                case 'weekly':
                    $key = date('Y-W', $timestamp);
                    break;
                case 'monthly':
                    $key = date('Y-m', $timestamp);
                    break;
                case 'daily':
                default:
                    $key = date('Y-m-d', $timestamp);
                    break;
            }

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'total' => 0,
                    'users' => [],
                ];
            }
            $grouped[$key]['total']++;
            $grouped[$key]['users'][(int) $record->userid] = true;
        }

        $lines = [];
        $count = 0;
        foreach ($grouped as $key => $data) {
            if ($count >= $limit) {
                break;
            }
            $unique = count($data['users']);
            $total = $data['total'];
            $lines[] = get_string('function_admin_login_stats_item', 'local_campusai', (object) [
                'label' => $label,
                'key' => $key,
                'total' => $total,
                'unique' => $unique,
            ]);
            $count++;
        }

        return implode("\n", $lines);
    }
}
