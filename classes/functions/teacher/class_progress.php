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
 * Average class progress.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class class_progress extends base_teacher {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'teacher_class_progress';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_teacher_class_progress_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What is the average progress of my class?',
            'Show class completion percentage.',
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
                    'description' => get_string('function_teacher_class_progress_param_courseid', 'local_campusai'),
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
            return get_string('function_teacher_class_progress_missing_courseid', 'local_campusai');
        }

        $course = $DB->get_record('course', ['id' => $courseid]);
        if (!$course || !has_capability('moodle/course:update', \context_course::instance($courseid), $userid)) {
            return get_string('function_teacher_class_progress_not_teacher', 'local_campusai');
        }

        $totalmodules = $DB->count_records('course_modules', [
            'course'            => $courseid,
            'visible'           => 1,
            'deletioninprogress' => 0,
        ]);

        if (!$totalmodules) {
            return get_string('function_teacher_class_progress_no_activities', 'local_campusai');
        }

        $context = \context_course::instance($courseid);
        $students = get_enrolled_users($context, 'mod/assign:submit');
        if (!$students) {
            return get_string('function_teacher_class_progress_no_students', 'local_campusai');
        }

        $userids = array_keys($students);
        [$insql, $inparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'u');

        $completed = $DB->get_records_sql(
            "SELECT cmc.userid, COUNT(cmc.id) AS completed
               FROM {course_modules_completion} cmc
               JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid
              WHERE cm.course = :courseid
                AND cm.visible = 1
                AND cmc.completionstate IN (1, 2)
                AND cmc.userid $insql
              GROUP BY cmc.userid",
            array_merge(['courseid' => $courseid], $inparams)
        );

        $sum = 0.0;
        foreach ($students as $uid => $student) {
            $done = isset($completed[$uid]) ? (int) $completed[$uid]->completed : 0;
            $sum += $done / $totalmodules;
        }

        $average = round($sum / count($students) * 100);

        return get_string('function_teacher_class_progress_result', 'local_campusai', (object) [
            'average' => $average,
            'totalmodules' => $totalmodules,
        ]);
    }
}
