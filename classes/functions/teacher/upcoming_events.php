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

namespace local_campusai\functions\teacher;
/**
 * Upcoming events in teacher courses.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class upcoming_events extends base_teacher {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'teacher_upcoming_events';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_teacher_upcoming_events_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What events are coming up in my courses?',
            'Show upcoming course events.',
        ];
    }

    /**
     * Returns the JSON schema parameters.
     *
     * @return array
     */
    public static function parameters(): array {
        return [
            'type'       => 'object',
            'properties' => [
                'courseid' => [
                    'type'        => 'integer',
                    'description' => get_string('function_teacher_upcoming_events_param_courseid', 'local_campusai'),
                ],
                'limit' => [
                    'type'        => 'integer',
                    'description' => get_string('function_teacher_upcoming_events_param_limit', 'local_campusai'),
                    'default'     => 10,
                ],
                'days' => [
                    'type'        => 'integer',
                    'description' => get_string('param_days', 'local_campusai'),
                    'default'     => 30,
                ],
            ],
            'required'   => [],
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

        $courseid = !empty($args['courseid']) ? (int) $args['courseid'] : 0;
        $limit = !empty($args['limit']) ? (int) $args['limit'] : 10;
        $days = !empty($args['days']) ? (int) $args['days'] : 30;

        if ($courseid) {
            $course = $DB->get_record('course', ['id' => $courseid]);
            if (!$course || !has_capability('moodle/course:update', \context_course::instance($courseid), $userid)) {
                return get_string('function_teacher_upcoming_events_not_teacher', 'local_campusai');
            }
            $courseids = [$courseid];
        } else {
            $courses = get_user_capability_course('moodle/course:update', $userid);
            if (!$courses) {
                return get_string('function_teacher_upcoming_events_no_teaching', 'local_campusai');
            }
            $courseids = array_map(function ($c) {
                return (int) $c->id;
            }, $courses);
        }

        [$insql, $inparams] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'c');

        $now = time();
        $end = $now + ($days * DAYSECS);

        $sql = "SELECT e.id, e.name, e.timestart, e.timeduration, c.shortname
                  FROM {event} e
                  JOIN {course} c ON c.id = e.courseid
                 WHERE e.courseid $insql
                   AND e.timestart >= :now
                   AND e.timestart <= :end
                 ORDER BY e.timestart ASC";

        $params = array_merge($inparams, ['now' => $now, 'end' => $end]);
        $events = $DB->get_records_sql($sql, $params, 0, $limit);

        if (!$events) {
            return get_string('function_teacher_upcoming_events_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($events as $event) {
            $date = \userdate($event->timestart, '%d/%m/%Y %H:%M');
            $lines[] = "- {$event->name} ({$event->shortname}, {$date})";
        }

        return implode("\n", $lines);
    }
}
