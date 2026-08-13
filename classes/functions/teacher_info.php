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
 * teacher_info function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class teacher_info extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'teacher_info';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_teacher_info_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'Who are the teachers in my course?',
            'Show teacher contact details.',
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
            ],
            'required' => ['courseid'],
        ];
    }

    /**
     * Executes the function and returns a plain text result.
     * @param int $userid
     * @param array $args
     * @return string
     */
    public function execute(int $userid, array $args): string {
        $courseid = $args['courseid'];

        if (!is_enrolled(\context_course::instance($courseid), $userid)) {
            return get_string('error_no_course_access', 'local_campusai');
        }

        $context = \context_course::instance($courseid);
        $teachers = get_enrolled_users($context, 'mod/assign:grade', 0, 'u.id, u.firstname, u.lastname, u.email');

        if (empty($teachers)) {
            return get_string('function_teacher_info_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($teachers as $teacher) {
            $lines[] = '- **' . fullname($teacher) . '** — ' . $teacher->email;
        }

        return implode("\n", $lines);
    }
}
