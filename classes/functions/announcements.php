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
 * announcements function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class announcements extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'announcements';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_announcements_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What are the latest announcements?',
            'Show course news.',
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
                    'description' => get_string('function_announcements_param_courseid', 'local_campusai'),
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => get_string('function_announcements_param_limit', 'local_campusai'),
                    'default' => 10,
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
        global $DB;

        $courseid = $args['courseid'] ?? 0;
        $limit = $args['limit'] ?? 10;

        if ($courseid) {
            if (!is_enrolled(\context_course::instance($courseid), $userid)) {
                return get_string('error_no_course_access', 'local_campusai');
            }
            $courseids = [$courseid];
        } else {
            $courses = enrol_get_users_courses($userid, true, 'id');
            $courseids = array_keys($courses);
        }

        if (empty($courseids)) {
            return get_string('error_not_enrolled', 'local_campusai');
        }

        [$insql, $params] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $params['userid'] = $userid;

        $sql = "SELECT p.id, p.subject, p.message, d.course, d.timemodified, c.shortname
                  FROM {forum_discussions} d
                  JOIN {forum_posts} p ON p.discussion = d.id
                  JOIN {forum} f ON f.id = d.forum
                  JOIN {course} c ON c.id = d.course
                 WHERE f.type = 'news' AND d.course $insql
              ORDER BY d.timemodified DESC";

        $records = $DB->get_records_sql($sql, $params, 0, $limit);

        if (empty($records)) {
            return get_string('function_announcements_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $r) {
            $lines[] = '- **' . strip_tags($r->subject) . '** (' . $r->shortname . '): ' .
                shorten_text(strip_tags($r->message), 120);
        }

        return implode("\n", $lines);
    }
}
