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



namespace local_campusai\provider; use moodle_exception; class claude_provider implements provider_interface { protected $apikey; protected $model; public function __construct(string $apikey, string $model) { $this->apikey = $apikey; $this->model = $model; } protected function build_tools(array $functions): array { $tools = []; foreach ($functions as $fn) { $tools[] = ['name' => $fn['name'], 'description' => $fn['description'], 'input_schema' => $fn['parameters'] ?? ['type' => 'object', 'properties' => new \stdClass()]]; } return $tools; } protected function request(array $messages, array $tools, string $systemprompt, int $maxtokens): array { $url = 'https://api.anthropic.com/v1/messages'; $payload = ['model' => $this->model, 'max_tokens' => $maxtokens, 'temperature' => 0.3, 'messages' => $messages]; if (!empty($systemprompt)) $payload['system'] = $systemprompt; if (!empty($tools)) $payload['tools'] = $tools; $ch = curl_init($url); curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($payload), CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'x-api-key: ' . $this->apikey, 'anthropic-version: 2023-06-01'], CURLOPT_TIMEOUT => 30, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_SSL_VERIFYPEER => true]); $response = curl_exec($ch); $error = curl_error($ch); curl_close($ch); if ($error) throw new moodle_exception('error_provider', 'local_campusai', '', null, 'Claude: ' . $error); $data = json_decode($response, true); if (isset($data['error'])) throw new moodle_exception('error_provider', 'local_campusai', '', null, 'Claude: ' . ($data['error']['message'] ?? 'unknown')); return $data; } protected function normalise_response(array $data): array { $tokens = ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0); $blocks = $data['content'] ?? []; foreach ($blocks as $block) { if ($block['type'] === 'tool_use') return ['type' => 'function_call', 'name' => $block['name'], 'arguments' => $block['input'] ?? [], 'id' => $block['id'] ?? '', 'tokens' => $tokens]; } $text = ''; foreach ($blocks as $block) if ($block['type'] === 'text') $text .= $block['text']; return ['type' => 'text', 'content' => $text, 'tokens' => $tokens]; } public function chat(array $messages, array $functions, string $systemprompt, int $maxtokens): array { $clean = array_filter($messages, fn($m) => $m['role'] !== 'system'); $data = $this->request(array_values($clean), $this->build_tools($functions), $systemprompt, $maxtokens); return $this->normalise_response($data); } public function continue_with_result(array $messages, array $functions, string $systemprompt, int $maxtokens): array { $clean = array_filter($messages, fn($m) => $m['role'] !== 'system'); $data = $this->request(array_values($clean), $this->build_tools($functions), $systemprompt, $maxtokens); return $this->normalise_response($data); } public function get_name(): string { return 'claude'; } } 
