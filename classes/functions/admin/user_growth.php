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
 * User growth over time function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_growth extends base_admin {
    /**
     * Returns the function name.
     *
     * @return string
     */
    public static function name(): string {
        return 'admin_user_growth';
    }

    /**
     * Returns the function description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_admin_user_growth_description', 'local_campusai');
    }

    /**
     * Returns example questions for the widget.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'How has user registration grown over the last year?',
            'Show monthly user growth.',
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
                'months' => [
                    'type' => 'integer',
                    'description' => get_string('function_admin_user_growth_param_months', 'local_campusai'),
                    'default' => 12,
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
            return get_string('function_admin_user_growth_permission', 'local_campusai');
        }

        $months = isset($args['months']) ? (int) $args['months'] : 12;
        $months = min(max($months, 1), 60);

        $threshold = strtotime("-{$months} months");
        $records = $DB->get_records_sql(
            "SELECT id, timecreated FROM {user} WHERE deleted = 0 AND timecreated > :threshold",
            ['threshold' => $threshold]
        );

        if (empty($records)) {
            return get_string('function_admin_user_growth_empty', 'local_campusai');
        }

        $grouped = [];
        foreach ($records as $record) {
            $key = date('Y-m', (int) $record->timecreated);
            if (!isset($grouped[$key])) {
                $grouped[$key] = 0;
            }
            $grouped[$key]++;
        }

        krsort($grouped);

        $lines = [];
        foreach ($grouped as $month => $count) {
            $lines[] = get_string('function_admin_user_growth_item', 'local_campusai', (object) [
                'month' => $month,
                'count' => $count,
            ]);
        }

        return implode("\n", $lines);
    }
}
