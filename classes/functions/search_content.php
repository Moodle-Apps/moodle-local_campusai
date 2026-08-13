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
 * search_content function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_content extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'search_content';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_search_content_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'Search for content in my courses.',
            'Find pages about a topic.',
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
                'query' => [
                    'type' => 'string',
                    'description' => get_string('function_search_content_param_query', 'local_campusai'),
                ],
                'courseid' => [
                    'type' => 'integer',
                    'description' => get_string('function_search_content_param_courseid', 'local_campusai'),
                ],
            ],
            'required' => ['query'],
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

        $query = trim($args['query']);
        $courseid = $args['courseid'] ?? 0;

        if (empty($query)) {
            return get_string('function_search_content_missing_query', 'local_campusai');
        }

        if ($courseid && !is_enrolled(\context_course::instance($courseid), $userid)) {
            return get_string('error_no_course_access', 'local_campusai');
        }

        if ($courseid) {
            $courseids = [$courseid];
        } else {
            $courses = enrol_get_users_courses($userid, true, 'id');
            $courseids = array_keys($courses);
        }

        if (empty($courseids)) {
            return get_string('error_not_enrolled', 'local_campusai');
        }

        [$insql, $params] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $params['query1'] = '%' . $DB->sql_like_escape($query) . '%';
        $params['query2'] = '%' . $DB->sql_like_escape($query) . '%';
        $likename = $DB->sql_like('p.name', ':query1', false);
        $likecontent = $DB->sql_like('p.content', ':query2', false);

        $sql = "SELECT p.id, p.name AS pagename, p.content, c.fullname
                  FROM {page} p
                  JOIN {course_modules} cm ON cm.instance = p.id AND cm.module = :pagemodule AND cm.course $insql
                  JOIN {course} c ON c.id = cm.course
                 WHERE ($likename OR $likecontent)
              ORDER BY p.timemodified DESC";
        $params['pagemodule'] = $DB->get_field('modules', 'id', ['name' => 'page']);

        $records = $DB->get_records_sql($sql, $params, 0, 10);

        if (empty($records)) {
            return get_string('function_search_content_empty', 'local_campusai', (object) ['query' => $query]);
        }

        $lines = [];
        foreach ($records as $r) {
            $lines[] = '- **' . $r->pagename . '** (' . $r->fullname . '): ' .
                shorten_text(strip_tags($r->content), 120);
        }

        return implode("\n", $lines);
    }
}
