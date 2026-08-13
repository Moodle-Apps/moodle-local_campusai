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
 * course_resources function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_resources extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'course_resources';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_course_resources_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What resources are available in this course?',
            'List course materials.',
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

        $modinfo = get_fast_modinfo($courseid, $userid);
        $lines = [];
        $hasresources = false;

        foreach ($modinfo->get_cms() as $cm) {
            $modname = $cm->modname;
            if (in_array($modname, ['resource', 'url', 'folder', 'book', 'page'])) {
                if ($cm->uservisible) {
                    $lines[] = '- **' . $cm->get_formatted_name() . '** (' . get_string('modulename', $modname) . ')';
                    $hasresources = true;
                }
            }
        }

        if (!$hasresources) {
            return get_string('function_course_resources_empty', 'local_campusai');
        }

        return implode("\n", $lines);
    }
}
