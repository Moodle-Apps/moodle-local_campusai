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
 * Recent campus activity feed function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recent_activity extends base_admin {
    /**
     * Returns the function name.
     *
     * @return string
     */
    public static function name(): string {
        return 'admin_recent_activity';
    }

    /**
     * Returns the function description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_admin_recent_activity_description', 'local_campusai');
    }

    /**
     * Returns example questions for the widget.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What happened on the platform recently?',
            'Show recent campus activity.',
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
                    'description' => get_string('param_days_back', 'local_campusai'),
                    'default' => 7,
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => get_string('param_limit', 'local_campusai'),
                    'default' => 20,
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
            return get_string('function_admin_recent_activity_permission', 'local_campusai');
        }

        // Keep the time window small so the query on the log table stays cheap: at most 30 days
        // back, newest records first, with a small row limit.
        $days = isset($args['days']) ? (int) $args['days'] : 7;
        $days = min(max($days, 1), 30);
        $limit = isset($args['limit']) ? (int) $args['limit'] : 20;
        $limit = min(max($limit, 1), 100);

        $threshold = time() - ($days * DAYSECS);

        $sql = "SELECT l.id, l.timecreated, l.action, u.firstname, u.lastname, c.fullname AS course
                  FROM {logstore_standard_log} l
                  JOIN {user} u ON u.id = l.userid
                  JOIN {course} c ON c.id = l.courseid
                 WHERE l.timecreated > :threshold
              ORDER BY l.timecreated DESC";

        $records = $DB->get_records_sql($sql, ['threshold' => $threshold], 0, $limit);

        if (empty($records)) {
            return get_string('function_admin_recent_activity_empty', 'local_campusai', (object) ['days' => $days]);
        }

        $lines = [];
        foreach ($records as $record) {
            $name = fullname($record);
            $time = userdate($record->timecreated, '%d/%m/%Y %H:%M');
            $lines[] = get_string('function_admin_recent_activity_item', 'local_campusai', (object) [
                'time' => $time,
                'name' => $name,
                'course' => $record->course,
                'action' => $record->action,
            ]);
        }

        return implode("\n", $lines);
    }
}
