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



 namespace local_campusai; defined('MOODLE_INTERNAL') || die(); class conversation { protected $userid; protected $sessionkey; const MAX_CONTEXT = 10; public function __construct(int $userid) { $this->userid = $userid; $this->sessionkey = 'campusai_messages_' . $userid; } public function get_messages(): array { global $SESSION; if (!isset($SESSION->{$this->sessionkey})) { return []; } return json_decode($SESSION->{$this->sessionkey}, true) ?: []; } public function add_message(string $role, string $content, ?string $functionname = null): void { global $SESSION; $messages = $this->get_messages(); $msg = ['role' => $role, 'content' => $content]; if ($functionname) { $msg['name'] = $functionname; } $messages[] = $msg; if (count($messages) > self::MAX_CONTEXT * 2) { $messages = array_slice($messages, -self::MAX_CONTEXT * 2); } $SESSION->{$this->sessionkey} = json_encode($messages); } public function add_function_call(string $functionname, array $arguments): void { global $SESSION; $messages = $this->get_messages(); $messages[] = [ 'role' => 'assistant', 'content' => null, 'tool_calls' => [[ 'id' => 'call_' . uniqid(), 'type' => 'function', 'function' => [ 'name' => $functionname, 'arguments' => json_encode($arguments), ], ]], ]; $SESSION->{$this->sessionkey} = json_encode($messages); } public function clear(): void { global $SESSION; unset($SESSION->{$this->sessionkey}); } public function log_interaction( string $usermessage, string $assistantmessage, array $functionscalled, string $provider, int $tokensused ): void { global $DB; if (!get_config('local_campusai', 'auditlog')) { return; } $record = (object) [ 'userid' => $this->userid, 'usermessage' => security::truncate($usermessage, 500), 'assistantmessage' => security::truncate($assistantmessage, 2000), 'functionscalled' => json_encode($functionscalled), 'provider' => $provider, 'tokensused' => $tokensused, 'timecreated' => time(), ]; $DB->insert_record('local_campusai_conversation', $record); } public static function purge_old_logs(): void { global $DB; $retention = (int) get_config('local_campusai', 'logretention'); if ($retention <= 0) { return; } $cutoff = time() - ($retention * DAYSECS); $DB->delete_records_select('local_campusai_conversation', 'timecreated < ?', [$cutoff]); } } 
