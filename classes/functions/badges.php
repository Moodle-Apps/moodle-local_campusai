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
 * badges function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class badges extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'badges';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_badges_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What badges have I earned?',
            'Show my achievements.',
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
                    'description' => get_string('function_badges_param_limit', 'local_campusai'),
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

        $sql = "SELECT bi.id, b.name, b.description, bi.dateissued
                  FROM {badge_issued} bi
                  JOIN {badge} b ON b.id = bi.badgeid
                 WHERE bi.userid = :userid
              ORDER BY bi.dateissued DESC";

        $records = $DB->get_records_sql($sql, ['userid' => $userid], 0, $limit);

        if (empty($records)) {
            return get_string('function_badges_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $r) {
            $date = userdate($r->dateissued, get_string('strftimedate', 'langconfig'));
            $lines[] = '- **' . $r->name . '** — ' . $date;
        }

        return implode("\n", $lines);
    }
}
