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
 * calendar_personal function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class calendar_personal extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'calendar_personal';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_calendar_personal_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What is on my personal calendar?',
            'Show my upcoming personal events.',
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
                'days' => [
                    'type' => 'integer',
                    'description' => get_string('function_calendar_personal_param_days', 'local_campusai'),
                    'default' => 30,
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

        $days = $args['days'] ?? 30;
        $now = time();
        $end = $now + ($days * DAYSECS);

        $records = $DB->get_records_select(
            'event',
            'userid = :userid AND courseid = 0 AND groupid = 0 AND timestart >= :now AND timestart <= :end',
            ['userid' => $userid, 'now' => $now, 'end' => $end],
            'timestart ASC',
            'id, name, description, timestart',
            0,
            20
        );

        if (empty($records)) {
            return get_string('function_calendar_personal_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $r) {
            $date = userdate($r->timestart, get_string('strftimedatetime', 'langconfig'));
            $lines[] = '- **' . $r->name . '** — ' . $date;
        }

        return implode("\n", $lines);
    }
}
