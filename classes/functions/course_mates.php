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
 * course_mates function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_mates extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'course_mates';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_course_mates_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'Who else is in my course?',
            'List my classmates.',
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
        $users = get_enrolled_users($context, '', 0, 'u.id, u.firstname, u.lastname, u.email', 'u.lastname, u.firstname');

        $lines = [];
        foreach ($users as $user) {
            if ($user->id == $userid) {
                continue;
            }
            $lines[] = '- ' . fullname($user) . ' (' . $user->email . ')';
        }

        if (empty($lines)) {
            return get_string('function_course_mates_empty', 'local_campusai');
        }

        return implode("\n", $lines);
    }
}
