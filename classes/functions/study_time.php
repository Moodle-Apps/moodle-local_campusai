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

namespace local_campusai\functions;

/**
 * study_time function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class study_time extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'study_time';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_study_time_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'How much time have I spent on this course?',
            'Show my study time.',
        ];
    }

    /**
     * Returns the JSON schema parameters.
     *
     * @return array
     */
    public static function parameters(): array {
        return [
            'type' => 'object',
            'properties' => [
                'courseid' => [
                    'type' => 'integer',
                    'description' => get_string('param_courseid', 'local_campusai'),
                ],
            ],
            'required' => ['courseid'],
        ];
    }

    /**
     * Executes the function and returns a plain text result.
     * @param int $userid
     * @param array $args
     * @return string
     */
    public function execute(int $userid, array $args): string {
        global $DB;

        $courseid = $args['courseid'];

        if (!is_enrolled(\context_course::instance($courseid), $userid)) {
            return get_string('error_no_course_access', 'local_campusai');
        }

        if (!$DB->get_manager()->table_exists('logstore_standard_log')) {
            return get_string('function_study_time_no_data', 'local_campusai');
        }

        // Restrict the scan of the (potentially huge) log table to a fixed time window so the
        // query stays index-friendly on large sites. Study time is estimated from the last
        // 90 days of activity only.
        $threshold = time() - (90 * DAYSECS);

        $sql = "SELECT COUNT(id) AS actions
                  FROM {logstore_standard_log}
                 WHERE userid = :userid
                   AND courseid = :courseid
                   AND action = 'viewed'
                   AND timecreated > :threshold";
        $actions = $DB->count_records_sql($sql, [
            'userid' => $userid,
            'courseid' => $courseid,
            'threshold' => $threshold,
        ]);

        if ($actions == 0) {
            return get_string('function_study_time_empty', 'local_campusai');
        }

        $minutes = $actions * 2;
        $hours = round($minutes / 60, 1);

        return get_string('function_study_time_result', 'local_campusai', (object) [
            'hours' => $hours,
            'minutes' => $minutes,
        ]);
    }
}
