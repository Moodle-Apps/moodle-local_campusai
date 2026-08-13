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

namespace local_campusai\functions\admin;

/**
 * Global campus statistics function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class campus_stats extends base_admin {
    /**
     * Returns the function name.
     *
     * @return string
     */
    public static function name(): string {
        return 'admin_campus_stats';
    }

    /**
     * Returns the function description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_admin_campus_stats_description', 'local_campusai');
    }

    /**
     * Returns example questions for the widget.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'How many users are on the platform?',
            'Show me campus-wide statistics.',
        ];
    }

    /**
     * Returns the function parameters schema.
     *
     * @return array
     */
    public static function parameters(): array {
        return [
            'type' => 'object',
            'properties' => (object) [],
            'required' => [],
        ];
    }

    /**
     * Executes the function.
     *
     * @param int $userid User ID.
     * @param array $args Arguments from the LLM.
     * @return string
     */
    public function execute(int $userid, array $args): string {
        global $DB;

        if (!has_capability('local/campusai:manage', \context_system::instance(), $userid)) {
            return get_string('function_admin_campus_stats_permission', 'local_campusai');
        }

        $users = $DB->count_records('user', ['deleted' => 0]);
        $courses = $DB->count_records('course', ['visible' => 1]);
        $categories = $DB->count_records('course_categories', ['visible' => 1]);
        $enrolments = $DB->count_records('user_enrolments', []);

        return get_string('function_admin_campus_stats_result', 'local_campusai', (object) [
            'users' => $users,
            'courses' => $courses,
            'categories' => $categories,
            'enrolments' => $enrolments,
        ]);
    }
}
