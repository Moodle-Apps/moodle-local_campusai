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
 * Recent forum questions.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class forum_questions extends base_teacher {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'teacher_forum_questions';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_teacher_forum_questions_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What questions have students asked in forums?',
            'Show recent forum questions.',
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
                    'description' => get_string('function_teacher_forum_questions_param_courseid', 'local_campusai'),
                ],
                'limit' => [
                    'type'        => 'integer',
                    'description' => get_string('function_teacher_forum_questions_param_limit', 'local_campusai'),
                    'default'     => 5,
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
        $limit = !empty($args['limit']) ? (int) $args['limit'] : 5;

        if ($courseid) {
            $course = $DB->get_record('course', ['id' => $courseid]);
            if (!$course || !has_capability('moodle/course:update', \context_course::instance($courseid), $userid)) {
                return get_string('function_teacher_forum_questions_not_teacher', 'local_campusai');
            }
            $courseids = [$courseid];
        } else {
            $courses = get_user_capability_course('moodle/course:update', $userid);
            if (!$courses) {
                return get_string('function_teacher_forum_questions_no_teaching', 'local_campusai');
            }
            $courseids = array_map(function ($c) {
                return (int) $c->id;
            }, $courses);
        }

        [$insql, $inparams] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'c');

        $likesubject = $DB->sql_like('p.subject', ':q1', false);
        $likemessage = $DB->sql_like('p.message', ':q2', false);

        $sql = "SELECT p.id, p.subject, p.message, p.created,
                       f.name AS forumname, c.shortname
                  FROM {forum_posts} p
                  JOIN {forum_discussions} d ON d.id = p.discussion
                  JOIN {forum} f ON f.id = d.forum
                  JOIN {course} c ON c.id = f.course
                 WHERE f.course $insql
                   AND ($likesubject OR $likemessage)
                 ORDER BY p.created DESC";

        $params = array_merge($inparams, ['q1' => '%?%', 'q2' => '%?%']);
        $posts = $DB->get_records_sql($sql, $params, 0, $limit);

        if (!$posts) {
            return get_string('function_teacher_forum_questions_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($posts as $post) {
            $subject = strip_tags(trim($post->subject ?: get_string('status_no_subject', 'local_campusai')));
            $date = \userdate($post->created, '%d/%m/%Y');
            $lines[] = get_string('function_teacher_forum_questions_item', 'local_campusai', (object) [
                'subject' => $subject,
                'forumname' => $post->forumname,
                'shortname' => $post->shortname,
                'date' => $date,
            ]);
        }

        return implode("\n", $lines);
    }
}
