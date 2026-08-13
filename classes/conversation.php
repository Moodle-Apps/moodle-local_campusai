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

namespace local_campusai;

/**
 * Conversation data access object.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class conversation {
    /** @var string Table name for conversation turns. */
    public const TABLE = 'local_campusai_conversation';

    /**
     * Records a conversation turn.
     *
     * @param int $userid User ID.
     * @param string $usermessage Sanitised user message.
     * @param string $assistantmessage Sanitised assistant message.
     * @param string $provider Provider name.
     * @param int $tokensused Approximate token usage.
     * @param string|null $functionscalled JSON array of function calls.
     * @return void
     */
    public static function record(
        int $userid,
        string $usermessage,
        string $assistantmessage,
        string $provider,
        int $tokensused = 0,
        ?string $functionscalled = null
    ): void {
        global $DB;

        if (!get_config('local_campusai', 'auditlog')) {
            return;
        }

        $record = new \stdClass();
        $record->userid = $userid;
        $record->usermessage = $usermessage;
        $record->assistantmessage = $assistantmessage;
        $record->functionscalled = $functionscalled;
        $record->provider = $provider;
        $record->tokensused = $tokensused;
        $record->timecreated = time();

        $DB->insert_record(self::TABLE, $record);
    }

    /**
     * Returns recent conversation history for a user.
     *
     * @param int $userid User ID.
     * @param int $limit Number of recent turns to return.
     * @return array List of records.
     */
    public static function get_recent(int $userid, int $limit = 6): array {
        global $DB;

        return $DB->get_records(
            self::TABLE,
            ['userid' => $userid],
            'timecreated DESC',
            '*',
            0,
            $limit
        );
    }

    /**
     * Purges conversation logs older than the configured retention period.
     *
     * @return void
     */
    public static function purge_old_logs(): void {
        global $DB;

        $days = (int) get_config('local_campusai', 'logretention');
        if ($days <= 0) {
            return;
        }

        $cutoff = time() - ($days * 86400);
        $DB->delete_records_select(self::TABLE, 'timecreated < :cutoff', ['cutoff' => $cutoff]);
    }
}
