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
 * Course list with filters function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_list_admin extends base_admin {
    /**
     * Returns the function name.
     *
     * @return string
     */
    public static function name(): string {
        return 'admin_course_list_admin';
    }

    /**
     * Returns the function description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_admin_course_list_admin_description', 'local_campusai');
    }

    /**
     * Returns example questions for the widget.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'List all courses on the platform.',
            'Show visible courses.',
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
                'category_id' => [
                    'type' => 'integer',
                    'description' => get_string('function_admin_course_list_admin_param_category_id', 'local_campusai'),
                ],
                'search' => [
                    'type' => 'string',
                    'description' => get_string('function_admin_course_list_admin_param_search', 'local_campusai'),
                ],
                'visible_only' => [
                    'type' => 'boolean',
                    'description' => get_string('function_admin_course_list_admin_param_visible_only', 'local_campusai'),
                    'default' => true,
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => get_string('function_admin_course_list_admin_param_limit', 'local_campusai'),
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
            return get_string('function_admin_course_list_admin_permission', 'local_campusai');
        }

        $limit = isset($args['limit']) ? (int) $args['limit'] : 50;
        $limit = min(max($limit, 1), 200);

        $conditions = ['c.id <> :siteid'];
        $params = ['siteid' => SITEID];

        if (isset($args['category_id']) && (int) $args['category_id'] >= 0) {
            $conditions[] = 'c.category = :category';
            $params['category'] = (int) $args['category_id'];
        }

        if (isset($args['search']) && trim($args['search']) !== '') {
            $conditions[] = $DB->sql_like('c.fullname', ':search', false);
            $params['search'] = '%' . $DB->sql_like_escape(trim($args['search'])) . '%';
        }

        if (!isset($args['visible_only']) || $args['visible_only'] === true) {
            $conditions[] = 'c.visible = 1';
        }

        $where = implode(' AND ', $conditions);
        $sql = "SELECT c.id, c.fullname, c.shortname, c.visible
                  FROM {course} c
                 WHERE {$where}
              ORDER BY c.fullname ASC";

        $records = $DB->get_records_sql($sql, $params, 0, $limit);

        if (empty($records)) {
            return get_string('function_admin_course_list_admin_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $record) {
            $status = $record->visible
                ? get_string('status_visible', 'local_campusai')
                : get_string('status_hidden', 'local_campusai');
            $lines[] = get_string('function_admin_course_list_admin_item', 'local_campusai', (object) [
                'fullname' => $record->fullname,
                'shortname' => $record->shortname,
                'status' => $status,
            ]);
        }

        return implode("\n", $lines);
    }
}
