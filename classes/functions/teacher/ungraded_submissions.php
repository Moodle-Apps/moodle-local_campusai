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
 * Ungraded submissions ordered by oldest first.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ungraded_submissions extends base_teacher {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'teacher_ungraded_submissions';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_teacher_ungraded_submissions_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'Which submissions need grading?',
            'Show ungraded work in my courses.',
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
                    'description' => get_string('function_teacher_ungraded_submissions_param_courseid', 'local_campusai'),
                ],
                'limit' => [
                    'type'        => 'integer',
                    'description' => get_string('function_teacher_ungraded_submissions_param_limit', 'local_campusai'),
                    'default'     => 10,
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

        if ($courseid) {
            $course = $DB->get_record('course', ['id' => $courseid]);
            if (!$course || !has_capability('moodle/course:update', \context_course::instance($courseid), $userid)) {
                return get_string('function_teacher_ungraded_submissions_not_teacher', 'local_campusai');
            }
            $courseids = [$courseid];
        } else {
            $courses = get_user_capability_course('moodle/course:update', $userid);
            if (!$courses) {
                return get_string('function_teacher_ungraded_submissions_no_teaching', 'local_campusai');
            }
            $courseids = array_map(function ($c) {
                return (int) $c->id;
            }, $courses);
        }

        [$insql, $inparams] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'c');

        $sql = "SELECT s.id, s.timemodified,
                       u.firstname, u.lastname,
                       a.name AS assignmentname, c.shortname
                  FROM {assign_submission} s
                  JOIN {assign} a ON a.id = s.assignment
                  JOIN {course} c ON c.id = a.course
                  JOIN {user} u ON u.id = s.userid
                  LEFT JOIN {assign_grades} ag ON ag.assignment = s.assignment
                       AND ag.userid = s.userid
                       AND ag.attemptnumber = s.attemptnumber
                 WHERE s.status = 'submitted'
                   AND s.latest = 1
                   AND a.course $insql
                   AND (ag.id IS NULL OR ag.timemodified < s.timemodified)
                 ORDER BY s.timemodified ASC";

        $submissions = $DB->get_records_sql($sql, $inparams, 0, $limit);

        if (!$submissions) {
            return get_string('function_teacher_ungraded_submissions_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($submissions as $s) {
            $date = \userdate($s->timemodified, '%d/%m/%Y');
            $lines[] = "- {$s->firstname} {$s->lastname}: {$s->assignmentname} ({$s->shortname}, {$date})";
        }

        return implode("\n", $lines);
    }
}
