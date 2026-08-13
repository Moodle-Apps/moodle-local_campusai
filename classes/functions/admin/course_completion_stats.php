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
 * Course completion statistics function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_completion_stats extends base_admin {
    /**
     * Returns the function name.
     *
     * @return string
     */
    public static function name(): string {
        return 'admin_course_completion_stats';
    }

    /**
     * Returns the function description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_admin_course_completion_stats_description', 'local_campusai');
    }

    /**
     * Returns example questions for the widget.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What are the course completion rates?',
            'Show how many users completed courses.',
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
                    'description' => get_string('function_admin_course_completion_stats_param_course_id', 'local_campusai'),
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
            return get_string('function_admin_course_completion_stats_permission', 'local_campusai');
        }

        $courseid = isset($args['course_id']) ? (int) $args['course_id'] : 0;

        $sql = "SELECT COUNT(*) AS total, SUM(CASE WHEN timecompleted > 0 THEN 1 ELSE 0 END) AS completed
                  FROM {course_completions}";
        $params = [];

        if ($courseid > 0) {
            $sql .= " WHERE course = :courseid";
            $params['courseid'] = $courseid;
        }

        $record = $DB->get_record_sql($sql, $params);

        if (!$record || (int) $record->total === 0) {
            return get_string('function_admin_course_completion_stats_empty', 'local_campusai');
        }

        $total = (int) $record->total;
        $completed = (int) $record->completed;
        $rate = round(($completed / $total) * 100, 1);

        return get_string('function_admin_course_completion_stats_result', 'local_campusai', (object) [
            'total' => $total,
            'completed' => $completed,
            'rate' => $rate,
        ]);
    }
}
