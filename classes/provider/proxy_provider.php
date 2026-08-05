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



 namespace local_campusai\provider; use moodle_exception; defined('MOODLE_INTERNAL') || die(); class proxy_provider implements provider_interface { protected $licensekey; const SERVER_URL = 'https://campusassistant.app/app/api/chat.php'; public function __construct(string $licensekey) { $this->licensekey = $licensekey; } public function chat(array $messages, array $functions, string $systemprompt, int $maxtokens): array { return $this->request($messages, $functions, $systemprompt, $maxtokens); } public function continue_with_result(array $messages, array $functions, string $systemprompt, int $maxtokens): array { return $this->request($messages, $functions, $systemprompt, $maxtokens); } protected function request(array $messages, array $functions, string $systemprompt, int $maxtokens): array { global $CFG; $payload = [ 'license_key' => $this->licensekey, 'domain' => parse_url($CFG->wwwroot, PHP_URL_HOST), 'messages' => $messages, 'functions' => $functions, 'system_prompt' => $systemprompt, 'max_tokens' => $maxtokens, ]; $ch = curl_init(self::SERVER_URL); curl_setopt_array($ch, [ CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($payload), CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_TIMEOUT => 45, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_SSL_VERIFYPEER => true, ]); $response = curl_exec($ch); $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE); $error = curl_error($ch); curl_close($ch); if ($error) { throw new moodle_exception('error_provider', 'local_campusai', '', null, 'Proxy: ' . $error); } $data = json_decode($response, true); if (!$data || isset($data['error'])) { $msg = $data['error'] ?? 'Invalid proxy response'; throw new moodle_exception('error_provider', 'local_campusai', '', null, 'Proxy: ' . $msg); } return [ 'type' => $data['type'] ?? 'text', 'content' => $data['content'] ?? '', 'name' => $data['name'] ?? '', 'arguments' => $data['arguments'] ?? [], 'id' => $data['id'] ?? '', 'tokens' => $data['tokens'] ?? 0, ]; } public function get_name(): string { return 'proxy'; } } 
