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
 * forum_unread function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class forum_unread extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'forum_unread';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_forum_unread_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'Are there unread forum posts?',
            'Show unread forum discussions.',
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
            'properties' => (object) [],
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

        $courses = enrol_get_users_courses($userid, true, 'id');
        if (empty($courses)) {
            return get_string('error_not_enrolled', 'local_campusai');
        }

        $courseids = array_keys($courses);
        [$insql, $params] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $params['userid'] = $userid;

        $sql = "SELECT f.id, f.name, f.course, COUNT(fp.id) AS unread
                  FROM {forum} f
                  JOIN {course_modules} cm ON cm.instance = f.id AND cm.module = :forummodule AND cm.course $insql
                  JOIN {forum_discussions} fd ON fd.forum = f.id
                  JOIN {forum_posts} fp ON fp.discussion = fd.id AND fp.userid <> :userid
             LEFT JOIN {forum_read} fr ON fr.postid = fp.id AND fr.userid = :userid2
                 WHERE fr.id IS NULL
              GROUP BY f.id, f.name, f.course
              ORDER BY unread DESC";

        $params['forummodule'] = $DB->get_field('modules', 'id', ['name' => 'forum']);
        $params['userid2'] = $userid;

        $records = $DB->get_records_sql($sql, $params, 0, 20);

        if (empty($records)) {
            return get_string('function_forum_unread_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $r) {
            $lines[] = get_string(
                'function_forum_unread_item',
                'local_campusai',
                (object) ['name' => $r->name, 'unread' => $r->unread]
            );
        }

        return implode("\n", $lines);
    }
}
