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

        $now = time();

        // Fetch the students of each course once, then resolve grades and
        // overdue assignments for every course and user with two grouped
        // queries instead of two queries per course.
        $studentsbycourse = [];
        $alluserids = [];
        foreach ($courses as $course) {
            $context = \context_course::instance($course->id);
            $students = get_enrolled_users($context, 'mod/assign:submit');
            if (!$students) {
                continue;
            }
            $studentsbycourse[$course->id] = $students;
            foreach ($students as $uid => $student) {
                $alluserids[$uid] = $uid;
            }
        }

        $avggrades = [];
        $overdues = [];
        if ($alluserids) {
            $courseids = array_keys($studentsbycourse);
            [$insql, $inparams] = $DB->get_in_or_equal($alluserids, SQL_PARAMS_NAMED, 'u');
            [$coursesql, $courseparams] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'c');

            $rs = $DB->get_recordset_sql(
                "SELECT gi.courseid, gg.userid,
                        AVG(CASE WHEN gg.finalgrade IS NOT NULL AND gi.grademax > 0
                                 THEN gg.finalgrade / gi.grademax * 100 END) AS avggrade
                   FROM {grade_grades} gg
                   JOIN {grade_items} gi ON gi.id = gg.itemid
                        AND gi.itemtype != 'course'
                  WHERE gg.userid $insql
                        AND gi.courseid $coursesql
                  GROUP BY gi.courseid, gg.userid",
                array_merge($inparams, $courseparams)
            );
            foreach ($rs as $row) {
                $avggrades[$row->courseid][$row->userid] = $row->avggrade;
            }
            $rs->close();

            $rs = $DB->get_recordset_sql(
                "SELECT a.course AS courseid, u.id AS userid, COUNT(a.id) AS overdue
                   FROM {assign} a
                   JOIN {user} u ON u.id $insql
                   LEFT JOIN {assign_submission} sub ON sub.assignment = a.id
                        AND sub.userid = u.id
                        AND sub.status = 'submitted'
                  WHERE a.course $coursesql
                        AND a.duedate > 0
                        AND a.duedate < :now
                        AND sub.id IS NULL
                  GROUP BY a.course, u.id",
                array_merge($inparams, $courseparams, ['now' => $now])
            );
            foreach ($rs as $row) {
                $overdues[$row->courseid][$row->userid] = $row->overdue;
            }
            $rs->close();
        }

        $lines = [];
        foreach ($courses as $course) {
            if (empty($studentsbycourse[$course->id])) {
                continue;
            }

            foreach ($studentsbycourse[$course->id] as $uid => $student) {
                $avg = isset($avggrades[$course->id][$uid]) && $avggrades[$course->id][$uid] !== null
                    ? (float) $avggrades[$course->id][$uid]
                    : null;
                $late = isset($overdues[$course->id][$uid]) ? (int) $overdues[$course->id][$uid] : 0;

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
