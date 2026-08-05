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



namespace local_campusai\provider; use moodle_exception; class openai_provider implements provider_interface { protected $apikey; protected $model; protected $baseurl; public function __construct(string $apikey, string $model, string $baseurl = 'https://api.openai.com/v1') { $this->apikey = $apikey; $this->model = $model; $this->baseurl = rtrim($baseurl, '/'); } protected function build_tools(array $functions): array { $tools = []; foreach ($functions as $fn) { $tools[] = [ 'type' => 'function', 'function' => [ 'name' => $fn['name'], 'description' => $fn['description'], 'parameters' => $fn['parameters'] ?? ['type' => 'object', 'properties' => new \stdClass()], ], ]; } return $tools; } protected function request(array $messages, array $tools, int $maxtokens): array { $url = $this->baseurl . '/chat/completions'; $payload = [ 'model' => $this->model, 'messages' => $messages, 'max_tokens' => $maxtokens, 'temperature' => 0.3, ]; if (!empty($tools)) { $payload['tools'] = $tools; $payload['tool_choice'] = 'auto'; } $ch = curl_init($url); curl_setopt_array($ch, [ CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($payload), CURLOPT_HTTPHEADER => [ 'Content-Type: application/json', 'Authorization: Bearer ' . $this->apikey, ], CURLOPT_TIMEOUT => 30, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_SSL_VERIFYPEER => true, ]); $response = curl_exec($ch); $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE); $error = curl_error($ch); if (defined('CLI_SCRIPT') || debugging('', DEBUG_DEVELOPER)) { $dbgmsg = date('H:i:s') . " OPENAI http={$httpcode} body=" . substr($response, 0, 300) . "\n"; $dbgfile = $GLOBALS['CFG']->dataroot . '/campusai_debug.log'; @file_put_contents($dbgfile, $dbgmsg, FILE_APPEND); } curl_close($ch); if ($error) { throw new moodle_exception('error_provider', 'local_campusai', '', null, 'API error: ' . $error); } $data = json_decode($response, true); if (isset($data['error'])) { throw new moodle_exception('error_provider', 'local_campusai', '', null, 'API error: ' . ($data['error']['message'] ?? 'unknown')); } return $data; } protected function normalise_response(array $data): array { $choice = $data['choices'][0] ?? null; if (!$choice) return ['type' => 'text', 'content' => '', 'tokens' => 0]; $message = $choice['message']; $tokens = $data['usage']['total_tokens'] ?? 0; if (!empty($message['tool_calls'])) { $toolcall = $message['tool_calls'][0]; return [ 'type' => 'function_call', 'name' => $toolcall['function']['name'] ?? '', 'arguments' => json_decode($toolcall['function']['arguments'] ?? '{}', true) ?: [], 'id' => $toolcall['id'] ?? '', 'tokens' => $tokens, ]; } return ['type' => 'text', 'content' => $message['content'] ?? '', 'tokens' => $tokens]; } public function chat(array $messages, array $functions, string $systemprompt, int $maxtokens): array { $full = [['role' => 'system', 'content' => $systemprompt]]; $full = array_merge($full, $messages); $data = $this->request($full, $this->build_tools($functions), $maxtokens); return $this->normalise_response($data); } public function continue_with_result(array $messages, array $functions, string $systemprompt, int $maxtokens): array { $full = [['role' => 'system', 'content' => $systemprompt]]; $full = array_merge($full, $messages); $data = $this->request($full, $this->build_tools($functions), $maxtokens); return $this->normalise_response($data); } public function get_name(): string { return 'openai'; } } 
