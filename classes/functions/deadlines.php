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
 * deadlines function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deadlines extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'deadlines';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_deadlines_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What deadlines do I have coming up?',
            'Show upcoming due dates.',
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
                'days' => [
                    'type' => 'integer',
                    'description' => get_string('function_deadlines_param_days', 'local_campusai'),
                    'default' => 14,
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

        $days = $args['days'] ?? 14;
        $now = time();
        $end = $now + ($days * DAYSECS);

        $courses = enrol_get_users_courses($userid, true, 'id');
        if (empty($courses)) {
            return get_string('error_not_enrolled', 'local_campusai');
        }

        $courseids = array_keys($courses);
        [$insql, $params] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $params['now'] = $now;
        $params['end'] = $end;

        $sql = "SELECT a.id, a.name, a.duedate, cm.course
                  FROM {assign} a
                  JOIN {course_modules} cm ON cm.instance = a.id AND cm.module = :assignmodule
                 WHERE cm.course $insql AND a.duedate >= :now AND a.duedate <= :end
              ORDER BY a.duedate ASC";
        $params['assignmodule'] = $DB->get_field('modules', 'id', ['name' => 'assign']);

        $records = $DB->get_records_sql($sql, $params, 0, 20);

        if (empty($records)) {
            return get_string('function_deadlines_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $r) {
            $date = userdate($r->duedate, get_string('strftimedatetime', 'langconfig'));
            $lines[] = '- **' . $r->name . '** — ' . $date;
        }

        return implode("\n", $lines);
    }
}
