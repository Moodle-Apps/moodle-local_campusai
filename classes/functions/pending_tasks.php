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
 * pending_tasks function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pending_tasks extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'pending_tasks';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_pending_tasks_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What assignments do I still need to submit?',
            'Show my pending tasks.',
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

        $courseid = $args['courseid'] ?? 0;

        if ($courseid && !is_enrolled(\context_course::instance($courseid), $userid)) {
            return get_string('error_no_course_access', 'local_campusai');
        }

        if ($courseid) {
            $courses = [$courseid => get_course($courseid)];
        } else {
            $courses = enrol_get_users_courses($userid, true, 'id');
        }

        if (empty($courses)) {
            return get_string('error_not_enrolled', 'local_campusai');
        }

        $courseids = array_keys($courses);
        [$insql, $params] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $params['userid'] = $userid;
        $params['now'] = time();
        $params['assignmodule'] = $DB->get_field('modules', 'id', ['name' => 'assign']);

        $sql = "SELECT a.id, a.name, a.duedate, c.fullname
                  FROM {assign} a
                  JOIN {course_modules} cm ON cm.instance = a.id AND cm.module = :assignmodule
                  JOIN {course} c ON c.id = cm.course
             LEFT JOIN {assign_submission} s ON s.assignment = a.id AND s.userid = :userid AND s.status = 'submitted'
                 WHERE cm.course $insql AND (a.duedate = 0 OR a.duedate > :now) AND s.id IS NULL
              ORDER BY a.duedate ASC";

        $records = $DB->get_records_sql($sql, $params, 0, 20);

        if (empty($records)) {
            return get_string('function_pending_tasks_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $r) {
            if ($r->duedate) {
                $due = userdate($r->duedate, get_string('strftimedatetime', 'langconfig'));
            } else {
                $due = get_string('status_no_due_date', 'local_campusai');
            }
            $lines[] = '- **' . $r->name . '** (' . $r->fullname . ') — ' . $due;
        }

        return implode("\n", $lines);
    }
}
