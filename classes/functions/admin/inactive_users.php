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
 * Inactive users function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class inactive_users extends base_admin {
    /**
     * Returns the function name.
     *
     * @return string
     */
    public static function name(): string {
        return 'admin_inactive_users';
    }

    /**
     * Returns the function description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_admin_inactive_users_description', 'local_campusai');
    }

    /**
     * Returns example questions for the widget.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'Which users have been inactive?',
            'Show users who have not logged in recently.',
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
                'days' => [
                    'type' => 'integer',
                    'description' => get_string('function_admin_inactive_users_param_days', 'local_campusai'),
                    'default' => 30,
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => get_string('function_admin_inactive_users_param_limit', 'local_campusai'),
                    'default' => 50,
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
            return get_string('function_admin_inactive_users_permission', 'local_campusai');
        }

        $days = isset($args['days']) ? (int) $args['days'] : 30;
        $days = max($days, 1);
        $limit = isset($args['limit']) ? (int) $args['limit'] : 50;
        $limit = min(max($limit, 1), 200);

        $threshold = time() - ($days * DAYSECS);

        $sql = "SELECT id, firstname, lastname, lastaccess
                  FROM {user}
                 WHERE deleted = 0
                   AND suspended = 0
                   AND (lastaccess < :threshold OR lastaccess = 0)
              ORDER BY lastaccess ASC";

        $records = $DB->get_records_sql($sql, ['threshold' => $threshold], 0, $limit);

        if (empty($records)) {
            return get_string('function_admin_inactive_users_empty', 'local_campusai', (object) ['days' => $days]);
        }

        $lines = [];
        foreach ($records as $record) {
            $name = fullname($record);
            $last = $record->lastaccess > 0
                ? userdate($record->lastaccess, '%d/%m/%Y')
                : get_string('status_never', 'local_campusai');
            $lines[] = get_string(
                'function_admin_inactive_users_item',
                'local_campusai',
                (object) ['name' => $name, 'last' => $last]
            );
        }

        return implode("\n", $lines);
    }
}
