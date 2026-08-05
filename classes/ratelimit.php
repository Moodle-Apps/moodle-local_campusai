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

/**
 * @package    local_campusai
 * @copyright  2026 Campus Assistant <hola@campusassistant.app>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This file is part of the Campus Assistant plugin for Moodle.
// It is distributed under the GNU GPL v3 or later license.



 namespace local_campusai; defined('MOODLE_INTERNAL') || die(); class ratelimit { protected $userid; protected $maxmessages; const WINDOW = 3600; public function __construct(int $userid, int $maxmessages = 20) { $this->userid = $userid; $this->maxmessages = max(1, $maxmessages); } public function can_send(): bool { global $DB; $now = time(); $windowstart = $now - ($now % self::WINDOW); $record = $DB->get_record('local_campusai_ratelimit', [ 'userid' => $this->userid, 'windowstart' => $windowstart, ]); if (!$record) { return true; } return $record->messagecount < $this->maxmessages; } public function record_message(): void { global $DB; $now = time(); $windowstart = $now - ($now % self::WINDOW); $record = $DB->get_record('local_campusai_ratelimit', [ 'userid' => $this->userid, 'windowstart' => $windowstart, ]); if ($record) { $record->messagecount++; $DB->update_record('local_campusai_ratelimit', $record); } else { $newrecord = (object) [ 'userid' => $this->userid, 'windowstart' => $windowstart, 'messagecount' => 1, ]; $DB->insert_record('local_campusai_ratelimit', $newrecord); } $cutoff = $now - (self::WINDOW * 2); $DB->delete_records_select('local_campusai_ratelimit', 'windowstart < ?', [$cutoff]); } public function get_remaining(): int { global $DB; $now = time(); $windowstart = $now - ($now % self::WINDOW); $record = $DB->get_record('local_campusai_ratelimit', [ 'userid' => $this->userid, 'windowstart' => $windowstart, ]); if (!$record) { return $this->maxmessages; } return max(0, $this->maxmessages - $record->messagecount); } } 
