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
 * Class engagement level.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class class_engagement extends base_teacher {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'teacher_class_engagement';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_teacher_class_engagement_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'How engaged is my class?',
            'Show active students and forum posts.',
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
                    'description' => get_string('function_teacher_class_engagement_param_courseid', 'local_campusai'),
                ],
            ],
            'required'   => ['courseid'],
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
        if (!$courseid) {
            return get_string('function_teacher_class_engagement_missing_courseid', 'local_campusai');
        }

        $course = $DB->get_record('course', ['id' => $courseid]);
        if (!$course || !has_capability('moodle/course:update', \context_course::instance($courseid), $userid)) {
            return get_string('function_teacher_class_engagement_not_teacher', 'local_campusai');
        }

        $context = \context_course::instance($courseid);
        $students = get_enrolled_users($context, 'mod/assign:submit');
        $total = count($students);

        if (!$total) {
            return get_string('function_teacher_class_engagement_no_students', 'local_campusai');
        }

        $since = time() - (7 * DAYSECS);
        $userids = array_keys($students);
        [$insql, $inparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'u');

        $active = $DB->count_records_sql(
            "SELECT COUNT(DISTINCT ul.userid)
               FROM {user_lastaccess} ul
              WHERE ul.courseid = :courseid
                AND ul.userid $insql
                AND ul.timeaccess > :since",
            array_merge(['courseid' => $courseid, 'since' => $since], $inparams)
        );

        $forumposts = $DB->count_records_sql(
            "SELECT COUNT(p.id)
               FROM {forum_posts} p
               JOIN {forum_discussions} d ON d.id = p.discussion
               JOIN {forum} f ON f.id = d.forum
              WHERE f.course = :courseid
                AND p.created > :since",
            ['courseid' => $courseid, 'since' => $since]
        );

        $submissions = $DB->count_records_sql(
            "SELECT COUNT(s.id)
               FROM {assign_submission} s
               JOIN {assign} a ON a.id = s.assignment
              WHERE a.course = :courseid
                AND s.status = 'submitted'
                AND s.timemodified > :since",
            ['courseid' => $courseid, 'since' => $since]
        );

        $percentage = round($active / $total * 100);

        return get_string('function_teacher_class_engagement_result', 'local_campusai', (object) [
            'percentage' => $percentage,
            'active' => $active,
            'total' => $total,
            'forumposts' => $forumposts,
            'submissions' => $submissions,
        ]);
    }
}
