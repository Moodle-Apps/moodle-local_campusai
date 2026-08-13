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
 * late_submissions function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class late_submissions extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'late_submissions';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_late_submissions_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'Which of my submissions were late?',
            'Show my late assignments.',
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
                    'description' => get_string('function_late_submissions_param_limit', 'local_campusai'),
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

        $sql = "SELECT s.id, a.name, s.timemodified, a.duedate, c.fullname
                  FROM {assign_submission} s
                  JOIN {assign} a ON a.id = s.assignment
                  JOIN {course} c ON c.id = a.course
                 WHERE s.userid = :userid AND s.status = 'submitted' AND a.duedate > 0
                   AND s.timemodified > a.duedate
              ORDER BY s.timemodified DESC";

        $records = $DB->get_records_sql($sql, ['userid' => $userid], 0, $limit);

        if (empty($records)) {
            return get_string('function_late_submissions_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $r) {
            $submitted = userdate($r->timemodified, get_string('strftimedatetime', 'langconfig'));
            $due = userdate($r->duedate, get_string('strftimedatetime', 'langconfig'));
            $lines[] = get_string('function_late_submissions_item', 'local_campusai', (object) [
                'name' => $r->name,
                'fullname' => $r->fullname,
                'submitted' => $submitted,
                'due' => $due,
            ]);
        }

        return implode("\n", $lines);
    }
}
