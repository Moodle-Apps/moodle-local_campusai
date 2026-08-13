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
 * Course categories listing function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_categories extends base_admin {
    /**
     * Returns the function name.
     *
     * @return string
     */
    public static function name(): string {
        return 'admin_course_categories';
    }

    /**
     * Returns the function description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_admin_course_categories_description', 'local_campusai');
    }

    /**
     * Returns example questions for the widget.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What course categories exist?',
            'Show the category structure.',
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
                'parent_id' => [
                    'type' => 'integer',
                    'description' => get_string('function_admin_course_categories_param_parent_id', 'local_campusai'),
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
            return get_string('function_admin_course_categories_permission', 'local_campusai');
        }

        $parentid = isset($args['parent_id']) ? (int) $args['parent_id'] : 0;

        $params = ['parent' => $parentid];
        $categories = $DB->get_records('course_categories', $params, 'name ASC', 'id, name, coursecount');

        if (empty($categories)) {
            return get_string('function_admin_course_categories_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($categories as $category) {
            $name = $category->name;
            $count = (int) $category->coursecount;
            $lines[] = get_string('function_admin_course_categories_item', 'local_campusai', (object) [
                'name' => $name,
                'count' => $count,
            ]);
        }

        return implode("\n", $lines);
    }
}
