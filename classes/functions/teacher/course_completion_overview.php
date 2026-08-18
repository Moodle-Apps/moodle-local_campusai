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
 * Course completion overview.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_completion_overview extends base_teacher {
    /** @var int Maximum number of courses analysed per request. */
    private const MAX_COURSES = 25;

    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'teacher_course_completion_overview';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_teacher_course_completion_overview_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'How many students have completed my course?',
            'Show course completion overview.',
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
                    'description' => get_string('function_teacher_course_completion_overview_param_courseid', 'local_campusai'),
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
                return get_string('function_teacher_course_completion_overview_not_teacher', 'local_campusai');
            }
            $courses = [$course];
        } else {
            $courses = get_user_capability_course('moodle/course:update', $userid);
            if (!$courses) {
                return get_string('function_teacher_course_completion_overview_no_teaching', 'local_campusai');
            }
            // Bound the per-course work when the user teaches many courses.
            $courses = array_slice($courses, 0, self::MAX_COURSES);
        }

        // Preload the completion counts for all courses in a single query instead of one
        // count per course inside the loop.
        $courseids = array_map(fn($course) => $course->id, $courses);
        [$insql, $inparams] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $completions = $DB->get_records_sql(
            "SELECT course, COUNT(*) AS completed
               FROM {course_completions}
              WHERE course $insql
                AND timecompleted > 0
              GROUP BY course",
            $inparams
        );

        $lines = [];
        foreach ($courses as $course) {
            $context = \context_course::instance($course->id);
            $students = get_enrolled_users($context, 'mod/assign:submit');
            $total = count($students);

            $completed = isset($completions[$course->id]) ? (int) $completions[$course->id]->completed : 0;

            $lines[] = get_string('function_teacher_course_completion_overview_item', 'local_campusai', (object) [
                'shortname' => $course->shortname,
                'completed' => $completed,
                'total' => $total,
            ]);
        }

        if (empty($lines)) {
            return get_string('function_teacher_course_completion_overview_empty', 'local_campusai');
        }

        return implode("\n", $lines);
    }
}
