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
 * Pending submissions function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pending_submissions extends base_admin {
    /**
     * Returns the function name.
     *
     * @return string
     */
    public static function name(): string {
        return 'admin_pending_submissions';
    }

    /**
     * Returns the function description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_admin_pending_submissions_description', 'local_campusai');
    }

    /**
     * Returns example questions for the widget.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'How many submissions are pending grading?',
            'Show ungraded submissions across courses.',
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
                'course_id' => [
                    'type' => 'integer',
                    'description' => get_string('function_admin_pending_submissions_param_course_id', 'local_campusai'),
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => get_string('function_admin_pending_submissions_param_limit', 'local_campusai'),
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
            return get_string('function_admin_pending_submissions_permission', 'local_campusai');
        }

        $limit = isset($args['limit']) ? (int) $args['limit'] : 50;
        $limit = min(max($limit, 1), 200);

        $params = [];
        $where = "s.status = 'submitted' AND g.grade IS NULL";

        if (isset($args['course_id']) && (int) $args['course_id'] > 0) {
            $where .= " AND a.course = :courseid";
            $params['courseid'] = (int) $args['course_id'];
        }

        $sql = "SELECT a.name AS assignment, c.fullname AS course, COUNT(s.id) AS pending
                  FROM {assign_submission} s
                  JOIN {assign} a ON a.id = s.assignment
                  JOIN {course} c ON c.id = a.course
                  JOIN {user} u ON u.id = s.userid
             LEFT JOIN {assign_grades} g ON g.assignment = s.assignment AND g.userid = s.userid
                 WHERE {$where}
              GROUP BY a.id, a.name, c.fullname
              ORDER BY pending DESC";

        $records = $DB->get_records_sql($sql, $params, 0, $limit);

        if (empty($records)) {
            return get_string('function_admin_pending_submissions_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $record) {
            $lines[] = get_string('function_admin_pending_submissions_item', 'local_campusai', (object) [
                'course' => $record->course,
                'assignment' => $record->assignment,
                'pending' => $record->pending,
            ]);
        }

        return implode("\n", $lines);
    }
}
