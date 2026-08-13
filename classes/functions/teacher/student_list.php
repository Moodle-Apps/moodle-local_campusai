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
 * List of students in a teacher course.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class student_list extends base_teacher {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'teacher_student_list';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_teacher_student_list_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'Who are the students in my course?',
            'Show enrolled students.',
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
                    'description' => get_string('function_teacher_student_list_param_courseid', 'local_campusai'),
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
            return get_string('function_teacher_student_list_missing_courseid', 'local_campusai');
        }

        $course = $DB->get_record('course', ['id' => $courseid]);
        if (!$course || !has_capability('moodle/course:update', \context_course::instance($courseid), $userid)) {
            return get_string('function_teacher_student_list_not_teacher', 'local_campusai');
        }

        $context = \context_course::instance($courseid);
        $students = get_enrolled_users($context, 'mod/assign:submit');

        if (!$students) {
            return get_string('function_teacher_student_list_no_students', 'local_campusai');
        }

        $lines = [];
        foreach ($students as $student) {
            $lines[] = "- {$student->firstname} {$student->lastname} ({$student->email})";
        }

        if (count($lines) > 30) {
            $total = count($lines);
            $lines = array_slice($lines, 0, 30);
            $remaining = $total - 30;
            $lines[] = get_string('function_teacher_student_list_more', 'local_campusai', (object) ['remaining' => $remaining]);
        }

        return implode("\n", $lines);
    }
}
