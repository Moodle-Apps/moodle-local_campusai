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
 * Teachers without assigned courses function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class teachers_without_courses extends base_admin {
    /**
     * Returns the function name.
     *
     * @return string
     */
    public static function name(): string {
        return 'admin_teachers_without_courses';
    }

    /**
     * Returns the function description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_admin_teachers_without_courses_description', 'local_campusai');
    }

    /**
     * Returns example questions for the widget.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'Which teachers are not assigned to any course?',
            'Show unassigned teachers.',
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
                'limit' => [
                    'type' => 'integer',
                    'description' => get_string('param_limit', 'local_campusai'),
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
            return get_string('function_admin_teachers_without_courses_permission', 'local_campusai');
        }

        $limit = isset($args['limit']) ? (int) $args['limit'] : 50;
        $limit = min(max($limit, 1), 200);

        $teacherroles = get_archetype_roles('editingteacher');
        if (empty($teacherroles)) {
            return get_string('function_admin_teachers_without_courses_no_roles', 'local_campusai');
        }

        $roleids = array_map(function ($role) {
            return (int) $role->id;
        }, $teacherroles);
        [$insql, $inparams] = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED);

        $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email
                  FROM {user} u
                  JOIN {role_assignments} ra ON ra.userid = u.id
                 WHERE ra.roleid {$insql}
                   AND u.deleted = 0
                   AND u.suspended = 0
                   AND NOT EXISTS (
                       SELECT 1
                         FROM {role_assignments} ra2
                         JOIN {context} ctx ON ctx.id = ra2.contextid
                        WHERE ra2.userid = u.id
                          AND ra2.roleid {$insql}
                          AND ctx.contextlevel = :coursecontext
                   )
              ORDER BY u.lastname, u.firstname";

        $params = array_merge($inparams, ['coursecontext' => CONTEXT_COURSE]);
        $records = $DB->get_records_sql($sql, $params, 0, $limit);

        if (empty($records)) {
            return get_string('function_admin_teachers_without_courses_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $record) {
            $name = fullname($record);
            $lines[] = "- {$name} ({$record->email})";
        }

        return implode("\n", $lines);
    }
}
