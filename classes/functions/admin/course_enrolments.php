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
 * Course enrolments function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_enrolments extends base_admin {
    /**
     * Returns the function name.
     *
     * @return string
     */
    public static function name(): string {
        return 'admin_course_enrolments';
    }

    /**
     * Returns the function description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_admin_course_enrolments_description', 'local_campusai');
    }

    /**
     * Returns example questions for the widget.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'How many students are enrolled in each course?',
            'Show course enrolment numbers.',
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
                    'description' => get_string('function_admin_course_enrolments_param_course_id', 'local_campusai'),
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => get_string('function_admin_course_enrolments_param_limit', 'local_campusai'),
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
            return get_string('function_admin_course_enrolments_permission', 'local_campusai');
        }

        $limit = isset($args['limit']) ? (int) $args['limit'] : 50;
        $limit = min(max($limit, 1), 200);

        if (isset($args['course_id']) && (int) $args['course_id'] > 0) {
            $courseid = (int) $args['course_id'];
            $course = $DB->get_record('course', ['id' => $courseid], 'id, fullname');
            if (!$course) {
                return get_string('function_admin_course_enrolments_course_not_found', 'local_campusai');
            }
            $count = (int) $DB->get_field_sql(
                "SELECT COUNT(ue.id) FROM {enrol} e JOIN {user_enrolments} ue ON ue.enrolid = e.id WHERE e.courseid = :courseid",
                ['courseid' => $courseid]
            );
            return get_string('function_admin_course_enrolments_course_result', 'local_campusai', (object) [
                'fullname' => $course->fullname,
                'count' => $count,
            ]);
        }

        $sql = "SELECT c.id, c.fullname, COUNT(ue.id) AS enrolments
                  FROM {course} c
             LEFT JOIN {enrol} e ON e.courseid = c.id
             LEFT JOIN {user_enrolments} ue ON ue.enrolid = e.id
                 WHERE c.id <> :siteid
              GROUP BY c.id, c.fullname
              ORDER BY enrolments DESC";

        $records = $DB->get_records_sql($sql, ['siteid' => SITEID], 0, $limit);

        if (empty($records)) {
            return get_string('function_admin_course_enrolments_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $record) {
            $name = $record->fullname;
            $count = (int) $record->enrolments;
            $lines[] = get_string('function_admin_course_enrolments_item', 'local_campusai', (object) [
                'name' => $name,
                'count' => $count,
            ]);
        }

        return implode("\n", $lines);
    }
}
