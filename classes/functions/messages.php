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
 * messages function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class messages extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'messages';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_messages_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'Show my recent messages.',
            'Do I have any new messages?',
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
                    'description' => get_string('function_messages_param_limit', 'local_campusai'),
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

        $sql = "SELECT m.id, m.subject, m.fullmessage, m.timecreated, u.firstname, u.lastname
                  FROM {messages} m
                  JOIN {message_conversation_members} mcm ON mcm.conversationid = m.conversationid
                  JOIN {user} u ON u.id = m.useridfrom
                 WHERE mcm.userid = :userid AND m.useridfrom <> :userid2
              ORDER BY m.timecreated DESC";

        $records = $DB->get_records_sql($sql, ['userid' => $userid, 'userid2' => $userid], 0, $limit);

        if (empty($records)) {
            return get_string('function_messages_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $r) {
            $date = userdate($r->timecreated, get_string('strftimedatetime', 'langconfig'));
            $text = $r->subject ?: shorten_text(strip_tags($r->fullmessage), 80);
            $lines[] = '- **' . fullname($r) . '** — ' . $date . ': ' . $text;
        }

        return implode("\n", $lines);
    }
}
