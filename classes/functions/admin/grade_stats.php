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
 * Global grade distribution function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_stats extends base_admin {
    /**
     * Returns the function name.
     *
     * @return string
     */
    public static function name(): string {
        return 'admin_grade_stats';
    }

    /**
     * Returns the function description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_admin_grade_stats_description', 'local_campusai');
    }

    /**
     * Returns example questions for the widget.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What are the grade statistics?',
            'Show average grades across courses.',
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
                    'description' => get_string('function_admin_grade_stats_param_course_id', 'local_campusai'),
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
            return get_string('function_admin_grade_stats_permission', 'local_campusai');
        }

        $params = [];
        $where = "g.finalgrade IS NOT NULL";

        if (isset($args['course_id']) && (int) $args['course_id'] > 0) {
            $where .= " AND c.id = :courseid";
            $params['courseid'] = (int) $args['course_id'];
        }

        $sql = "SELECT COUNT(g.id) AS total,
                       AVG(g.finalgrade) AS average,
                       MIN(g.finalgrade) AS minimum,
                       MAX(g.finalgrade) AS maximum
                  FROM {grade_grades} g
                  JOIN {grade_items} gi ON gi.id = g.itemid
                  JOIN {course} c ON c.id = gi.courseid
                 WHERE {$where}";

        $record = $DB->get_record_sql($sql, $params);

        if (!$record || (int) $record->total === 0) {
            return get_string('function_admin_grade_stats_empty', 'local_campusai');
        }

        $total = (int) $record->total;
        $average = round((float) $record->average, 2);
        $min = round((float) $record->minimum, 2);
        $max = round((float) $record->maximum, 2);

        return get_string('function_admin_grade_stats_result', 'local_campusai', (object) [
            'total' => $total,
            'average' => $average,
            'min' => $min,
            'max' => $max,
        ]);
    }
}
