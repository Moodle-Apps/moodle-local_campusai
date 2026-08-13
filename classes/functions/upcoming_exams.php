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
 * upcoming_exams function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class upcoming_exams extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'upcoming_exams';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_upcoming_exams_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What exams are coming up?',
            'Show my upcoming quizzes.',
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
                    'description' => get_string('param_days', 'local_campusai'),
                    'default' => 30,
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

        $days = $args['days'] ?? 30;
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
        $params['quizmodule'] = $DB->get_field('modules', 'id', ['name' => 'quiz']);

        $sql = "SELECT q.id, q.name, q.timeopen, q.timeclose, c.fullname
                  FROM {quiz} q
                  JOIN {course_modules} cm ON cm.instance = q.id AND cm.module = :quizmodule
                  JOIN {course} c ON c.id = cm.course
                 WHERE cm.course $insql AND q.timeopen >= :now AND q.timeopen <= :end
              ORDER BY q.timeopen ASC";

        $records = $DB->get_records_sql($sql, $params, 0, 20);

        if (empty($records)) {
            return get_string('function_upcoming_exams_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $r) {
            $date = userdate($r->timeopen, get_string('strftimedatetime', 'langconfig'));
            $lines[] = '- **' . $r->name . '** (' . $r->fullname . ') — ' . $date;
        }

        return implode("\n", $lines);
    }
}
