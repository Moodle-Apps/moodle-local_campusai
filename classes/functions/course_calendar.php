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
 * course_calendar function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_calendar extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'course_calendar';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_course_calendar_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What events are coming up in my course?',
            'Show my course calendar.',
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
                'days' => [
                    'type' => 'integer',
                    'description' => get_string('function_course_calendar_param_days', 'local_campusai'),
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

        $courseid = $args['courseid'] ?? 0;
        $days = $args['days'] ?? 30;

        if ($courseid && !is_enrolled(\context_course::instance($courseid), $userid)) {
            return get_string('error_no_course_access', 'local_campusai');
        }

        $now = time();
        $end = $now + ($days * DAYSECS);

        $params = ['now' => $now, 'end' => $end];
        $coursewhere = '';
        if ($courseid) {
            $coursewhere = 'AND e.courseid = :courseid';
            $params['courseid'] = $courseid;
        }

        $sql = "SELECT e.id, e.name, e.timestart, e.timeduration, e.courseid, e.description
                  FROM {event} e
                 WHERE e.timestart >= :now AND e.timestart <= :end
                   AND e.userid = 0 AND e.groupid = 0 AND e.courseid <> 0
                   $coursewhere
              ORDER BY e.timestart ASC";

        $records = $DB->get_records_sql($sql, $params, 0, 20);

        if (empty($records)) {
            return get_string('function_course_calendar_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $r) {
            $date = userdate($r->timestart, get_string('strftimedate', 'langconfig'));
            $lines[] = '- **' . strip_tags($r->name) . '** — ' . $date;
        }

        return implode("\n", $lines);
    }
}
