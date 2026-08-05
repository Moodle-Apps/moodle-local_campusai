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



namespace local_campusai\provider; use moodle_exception; class gemini_provider implements provider_interface { protected $apikey; protected $model; public function __construct(string $apikey, string $model) { $this->apikey = $apikey; $this->model = $model; } protected function build_tools(array $functions): array { $declarations = []; foreach ($functions as $fn) { $params = $fn['parameters'] ?? ['type' => 'object', 'properties' => new \stdClass()]; unset($params['$schema'], $params['$ref'], $params['additionalProperties']); if (!isset($params['type'])) $params['type'] = 'object'; if ($params['type'] === 'object' && empty($params['properties'])) $params['properties'] = new \stdClass(); $declarations[] = ['name' => $fn['name'], 'description' => $fn['description'], 'parameters' => $params]; } return [['function_declarations' => $declarations]]; } protected function build_contents(array $messages): array { $contents = []; foreach ($messages as $msg) { $role = ($msg['role'] === 'assistant') ? 'model' : 'user'; if (isset($msg['role']) && $msg['role'] === 'function') { $contents[] = [ 'role' => 'function', 'parts' => [[ 'function_response' => [ 'name' => $msg['name'], 'response' => ['result' => $msg['content']], ], ]], ]; continue; } $contents[] = ['role' => $role, 'parts' => [['text' => $msg['content']]]]; } return $contents; } protected function request(array $contents, array $tools, string $systemprompt, int $maxtokens): array { $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent?key=' . $this->apikey; $payload = ['contents' => $contents, 'generationConfig' => ['maxOutputTokens' => $maxtokens, 'temperature' => 0.3]]; if (!empty($systemprompt)) $payload['systemInstruction'] = ['parts' => [['text' => $systemprompt]]]; if (!empty($tools)) $payload['tools'] = $tools; $ch = curl_init($url); curl_setopt_array($ch, [ CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($payload), CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_TIMEOUT => 30, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_SSL_VERIFYPEER => true, ]); $response = curl_exec($ch); $error = curl_error($ch); curl_close($ch); if ($error) throw new moodle_exception('error_provider', 'local_campusai', '', null, 'Gemini: ' . $error); $data = json_decode($response, true); if (isset($data['error'])) throw new moodle_exception('error_provider', 'local_campusai', '', null, 'Gemini: ' . ($data['error']['message'] ?? 'unknown')); return $data; } protected function normalise_response(array $data): array { $tokens = $data['usageMetadata']['totalTokenCount'] ?? 0; $candidate = $data['candidates'][0] ?? null; if (!$candidate) return ['type' => 'text', 'content' => '', 'tokens' => 0]; $parts = $candidate['content']['parts'] ?? []; foreach ($parts as $part) { if (isset($part['functionCall'])) return ['type' => 'function_call', 'name' => $part['functionCall']['name'], 'arguments' => $part['functionCall']['args'] ?? [], 'id' => $part['functionCall']['name'], 'tokens' => $tokens]; } $text = ''; foreach ($parts as $part) if (isset($part['text'])) $text .= $part['text']; return ['type' => 'text', 'content' => $text, 'tokens' => $tokens]; } public function chat(array $messages, array $functions, string $systemprompt, int $maxtokens): array { $data = $this->request($this->build_contents($messages), $this->build_tools($functions), $systemprompt, $maxtokens); return $this->normalise_response($data); } public function continue_with_result(array $messages, array $functions, string $systemprompt, int $maxtokens): array { $data = $this->request($this->build_contents($messages), $this->build_tools($functions), $systemprompt, $maxtokens); return $this->normalise_response($data); } public function get_name(): string { return 'gemini'; } } 
