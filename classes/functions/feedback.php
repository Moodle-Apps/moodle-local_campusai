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
 * feedback function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feedback extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'feedback';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_feedback_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'Show feedback on my assignments.',
            'What feedback have I received?',
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
                'limit' => [
                    'type' => 'integer',
                    'description' => get_string('function_feedback_param_limit', 'local_campusai'),
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

        $limit = $args['limit'] ?? 10;

        $sql = "SELECT g.id, g.grade, g.feedback, g.timemodified, a.name AS assignmentname, c.fullname
                  FROM {assign_grades} g
                  JOIN {assign} a ON a.id = g.assignment
                  JOIN {course} c ON c.id = a.course
                  JOIN {course_modules} cm ON cm.instance = a.id AND cm.module = :assignmodule
                 WHERE g.userid = :userid AND g.feedback IS NOT NULL AND g.feedback <> ''
              ORDER BY g.timemodified DESC";

        $records = $DB->get_records_sql($sql, [
            'userid' => $userid,
            'assignmodule' => $DB->get_field('modules', 'id', ['name' => 'assign']),
        ], 0, $limit);

        if (empty($records)) {
            return get_string('function_feedback_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $r) {
            $date = userdate($r->timemodified, get_string('strftimedate', 'langconfig'));
            $lines[] = '- **' . $r->assignmentname . '** (' . $r->fullname . ') — ' . $date . ': ' .
                shorten_text(strip_tags($r->feedback), 120);
        }

        return implode("\n", $lines);
    }
}
