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
 * next_activity function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class next_activity extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'next_activity';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_next_activity_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What should I do next?',
            'Show my next incomplete activity.',
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
                    'description' => get_string('param_courseid_optional', 'local_campusai'),
                ],
            ],
        ];
    }

    /**
     * Executes the function and returns a plain text result.
     * @param int $userid
     * @param array $args
     * @return string
     */
    public function execute(int $userid, array $args): string {
        $courseid = $args['courseid'] ?? 0;

        if ($courseid && !is_enrolled(\context_course::instance($courseid), $userid)) {
            return get_string('error_no_course_access', 'local_campusai');
        }

        if ($courseid) {
            $courses = [$courseid => get_course($courseid)];
        } else {
            $courses = enrol_get_users_courses($userid, true, 'id, fullname');
        }

        foreach ($courses as $course) {
            $modinfo = get_fast_modinfo($course->id, $userid);
            foreach ($modinfo->get_cms() as $cm) {
                if ($cm->uservisible && !$cm->completion) {
                    continue;
                }
                if ($cm->uservisible) {
                    $completioninfo = new \completion_info($course);
                    $state = $completioninfo->get_data($cm, false, $userid)->completionstate;
                    if ($state == COMPLETION_INCOMPLETE) {
                        return get_string('function_next_activity_result', 'local_campusai', (object) [
                            'activity' => $cm->get_formatted_name(),
                            'course' => $course->fullname,
                        ]);
                    }
                }
            }
        }

        return get_string('function_next_activity_empty', 'local_campusai');
    }
}
