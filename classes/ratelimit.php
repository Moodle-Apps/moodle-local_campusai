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
 * Per-user rate limiting.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ratelimit {
    /** @var string Table name for rate limit counters. */
    public const TABLE = 'local_campusai_ratelimit';

    /**
     * Checks whether the user may send another message.
     *
     * @param int $userid User ID.
     * @return bool True if allowed, false if exceeded.
     */
    public static function check(int $userid): bool {
        global $DB;

        $limit = (int) get_config('local_campusai', 'ratelimit');
        $window = (int) get_config('local_campusai', 'window');
        $now = time();
        $windowstart = $now - ($window > 0 ? $window : 600);

        $record = $DB->get_record(self::TABLE, ['userid' => $userid, 'windowstart' => $windowstart]);

        if ($record) {
            if ($record->messagecount >= $limit) {
                return false;
            }
        } else {
            // Reset/create a fresh window record.
            $DB->delete_records(self::TABLE, ['userid' => $userid, 'windowstart' => $windowstart]);
            $record = new \stdClass();
            $record->userid = $userid;
            $record->windowstart = $windowstart;
            $record->messagecount = 0;
            $record->id = $DB->insert_record(self::TABLE, $record);
        }

        return true;
    }

    /**
     * Increments the user's message counter.
     *
     * @param int $userid User ID.
     * @return void
     */
    public static function increment(int $userid): void {
        global $DB;

        $window = (int) get_config('local_campusai', 'window');
        $windowstart = time() - ($window > 0 ? $window : 600);

        $record = $DB->get_record(self::TABLE, ['userid' => $userid, 'windowstart' => $windowstart]);

        if ($record) {
            $record->messagecount++;
            $DB->update_record(self::TABLE, $record);
        } else {
            $record = new \stdClass();
            $record->userid = $userid;
            $record->windowstart = $windowstart;
            $record->messagecount = 1;
            $DB->insert_record(self::TABLE, $record);
        }
    }

    /**
     * Purges stale rate limit counters.
     *
     * @param int $cutoffseconds Age in seconds.
     * @return void
     */
    public static function purge(int $cutoffseconds = 86400): void {
        global $DB;

        $cutoff = time() - $cutoffseconds;
        $DB->delete_records_select(self::TABLE, 'windowstart < :cutoff', ['cutoff' => $cutoff]);
    }
}
