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
 * Storage usage function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class storage_usage extends base_admin {
    /**
     * Returns the function name.
     *
     * @return string
     */
    public static function name(): string {
        return 'admin_storage_usage';
    }

    /**
     * Returns the function description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_admin_storage_usage_description', 'local_campusai');
    }

    /**
     * Returns example questions for the widget.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'How much storage is being used?',
            'Which courses use the most storage?',
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
                    'description' => get_string('function_admin_storage_usage_param_course_id', 'local_campusai'),
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => get_string('function_admin_storage_usage_param_limit', 'local_campusai'),
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
            return get_string('function_admin_storage_usage_permission', 'local_campusai');
        }

        if (isset($args['course_id']) && (int) $args['course_id'] > 0) {
            $courseid = (int) $args['course_id'];
            $course = $DB->get_record('course', ['id' => $courseid], 'id, fullname');
            if (!$course) {
                return get_string('function_admin_storage_usage_course_not_found', 'local_campusai');
            }
            $bytes = (int) $DB->get_field_sql(
                "SELECT SUM(f.filesize) FROM {files} f WHERE f.contextid IN (
                    SELECT id FROM {context} WHERE instanceid = :courseid AND contextlevel = :courselevel
                )",
                ['courseid' => $courseid, 'courselevel' => CONTEXT_COURSE]
            );
            $size = display_size($bytes);
            return get_string('function_admin_storage_usage_course_result', 'local_campusai', (object) [
                'fullname' => $course->fullname,
                'size' => $size,
            ]);
        }

        $limit = isset($args['limit']) ? (int) $args['limit'] : 20;
        $limit = min(max($limit, 1), 100);

        $totalbytes = (int) $DB->get_field_sql("SELECT SUM(filesize) FROM {files}");
        $total = display_size($totalbytes);

        $sql = "SELECT c.id, c.fullname, SUM(f.filesize) AS bytes
                  FROM {course} c
                  JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = :courselevel
                  JOIN {files} f ON f.contextid = ctx.id
                 WHERE c.id <> :siteid
              GROUP BY c.id, c.fullname
              ORDER BY bytes DESC";

        $records = $DB->get_records_sql($sql, ['courselevel' => CONTEXT_COURSE, 'siteid' => SITEID], 0, $limit);

        $lines = [get_string('function_admin_storage_usage_total', 'local_campusai', (object) ['total' => $total])];
        if (!empty($records)) {
            foreach ($records as $record) {
                $size = display_size((int) $record->bytes);
                $lines[] = get_string('function_admin_storage_usage_item', 'local_campusai', (object) [
                    'fullname' => $record->fullname,
                    'size' => $size,
                ]);
            }
        }

        return implode("\n", $lines);
    }
}
