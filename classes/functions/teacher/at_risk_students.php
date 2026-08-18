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
 * Students at risk in teacher courses.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class at_risk_students extends base_teacher {
    /** @var int Maximum number of courses analysed per request. */
    private const MAX_COURSES = 25;

    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'teacher_at_risk_students';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_teacher_at_risk_students_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'Which students are at risk?',
            'Show struggling students in my courses.',
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
                    'description' => get_string('function_teacher_at_risk_students_param_courseid', 'local_campusai'),
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

        if ($courseid) {
            $course = $DB->get_record('course', ['id' => $courseid]);
            if (!$course || !has_capability('moodle/course:update', \context_course::instance($courseid), $userid)) {
                return get_string('function_teacher_at_risk_students_not_teacher', 'local_campusai');
            }
            $courses = [$course];
        } else {
            $courses = get_user_capability_course('moodle/course:update', $userid);
            if (!$courses) {
                return get_string('function_teacher_at_risk_students_no_teaching', 'local_campusai');
            }
            // Bound the per-course work when the user teaches many courses.
            $courses = array_slice($courses, 0, self::MAX_COURSES);
        }

        $now   = time();
        $lines = [];

        foreach ($courses as $course) {
            $context = \context_course::instance($course->id);
            $students = get_enrolled_users($context, 'mod/assign:submit');
            if (!$students) {
                continue;
            }

            $userids = array_keys($students);
            [$insql, $inparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'u');

            $avggrade = $DB->get_records_sql(
                "SELECT u.id,
                        AVG(CASE WHEN gg.finalgrade IS NOT NULL AND gi.grademax > 0
                                 THEN gg.finalgrade / gi.grademax * 100 END) AS avggrade
                   FROM {user} u
                   LEFT JOIN {grade_grades} gg ON gg.userid = u.id
                   LEFT JOIN {grade_items} gi ON gi.id = gg.itemid
                        AND gi.courseid = :courseid
                        AND gi.itemtype != 'course'
                  WHERE u.id $insql
                  GROUP BY u.id",
                array_merge(['courseid' => $course->id], $inparams)
            );

            $overdue = $DB->get_records_sql(
                "SELECT u.id, COUNT(a.id) AS overdue
                   FROM {user} u
                   LEFT JOIN {assign} a ON a.course = :courseid
                        AND a.duedate > 0
                        AND a.duedate < :now
                   LEFT JOIN {assign_submission} sub ON sub.assignment = a.id
                        AND sub.userid = u.id
                        AND sub.status = 'submitted'
                  WHERE u.id $insql
                        AND a.id IS NOT NULL
                        AND sub.id IS NULL
                  GROUP BY u.id",
                array_merge(['courseid' => $course->id, 'now' => $now], $inparams)
            );

            foreach ($students as $uid => $student) {
                $avg = isset($avggrade[$uid]) && $avggrade[$uid]->avggrade !== null
                    ? (float) $avggrade[$uid]->avggrade
                    : null;
                $late = isset($overdue[$uid]) ? (int) $overdue[$uid]->overdue : 0;

                $risk = false;
                if ($avg !== null && $avg < 50.0) {
                    $risk = true;
                }
                if ($late >= 2) {
                    $risk = true;
                }

                if (!$risk) {
                    continue;
                }

                $gradetext = $avg !== null ? round($avg, 1) . '%' : get_string('status_no_grades', 'local_campusai');
                $lines[] = get_string('function_teacher_at_risk_students_item', 'local_campusai', (object) [
                    'name' => $student->firstname . ' ' . $student->lastname,
                    'shortname' => $course->shortname,
                    'grade' => $gradetext,
                    'late' => $late,
                ]);
            }
        }

        if (empty($lines)) {
            return get_string('function_teacher_at_risk_students_empty', 'local_campusai');
        }

        return implode("\n", $lines);
    }
}
