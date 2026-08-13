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
 * my_overview function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class my_overview extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'my_overview';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_my_overview_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'Give me an overview of my studies.',
            'How many courses and tasks do I have?',
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
            'properties' => (object) [],
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

        $courses = enrol_get_users_courses($userid, true, 'id, fullname');
        $coursescount = count($courses);

        $courseids = array_keys($courses);
        $pendingtasks = 0;
        $deadlines = 0;
        if (!empty($courseids)) {
            [$insql, $params] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
            $params['now'] = time();
            $params['end'] = time() + (14 * DAYSECS);
            $params['assignmodule'] = $DB->get_field('modules', 'id', ['name' => 'assign']);

            $sql = "SELECT COUNT(a.id)
                      FROM {assign} a
                      JOIN {course_modules} cm ON cm.instance = a.id AND cm.module = :assignmodule
                     WHERE cm.course $insql AND a.duedate > :now AND a.duedate <= :end";
            $deadlines = $DB->count_records_sql($sql, $params);

            $sql = "SELECT COUNT(a.id)
                      FROM {assign} a
                      JOIN {course_modules} cm ON cm.instance = a.id AND cm.module = :assignmodule2
                 LEFT JOIN {assign_submission} s ON s.assignment = a.id AND s.userid = :userid AND s.status = 'submitted'
                     WHERE cm.course $insql AND a.duedate > :now2 AND s.id IS NULL";
            $params['assignmodule2'] = $params['assignmodule'];
            $params['now2'] = $params['now'];
            $params['userid'] = $userid;
            $pendingtasks = $DB->count_records_sql($sql, $params);
        }

        return get_string('function_my_overview_result', 'local_campusai', (object) [
            'coursescount' => $coursescount,
            'pendingtasks' => $pendingtasks,
            'deadlines' => $deadlines,
        ]);
    }
}
