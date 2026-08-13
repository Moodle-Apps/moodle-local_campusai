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

namespace local_campusai\provider;

/**
 * Google Gemini API provider.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gemini_provider implements provider_interface {
    /** @var string API key. */
    private string $apikey;

    /** @var string Unused for Gemini. */
    private string $jwtsecret;

    /**
     * Constructor.
     *
     * @param string $apikey
     * @param string $jwtsecret
     */
    public function __construct(string $apikey, string $jwtsecret) {
        $this->apikey = $apikey;
        $this->jwtsecret = $jwtsecret;
    }

    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'gemini';
    }

    /**
     * Sends the request to Gemini.
     *
     * @param string $systemprompt
     * @param array $messages
     * @param array $tools
     * @param string $model
     * @param int $maxtokens
     * @return array
     */
    public function chat(
        string $systemprompt,
        array $messages,
        array $tools,
        string $model,
        int $maxtokens
    ): array {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' .
            urlencode($model) . ':generateContent?key=' .
            urlencode($this->apikey);

        $contents = [];
        foreach ($messages as $msg) {
            $role = $msg['role'];
            if ($role === 'tool') {
                $role = 'function';
            }
            if ($role === 'assistant') {
                $role = 'model';
            }
            $contents[] = [
                'role'  => $role,
                'parts' => [['text' => $msg['content']]],
            ];
        }

        $functiondeclarations = [];
        foreach ($tools as $tool) {
            $functiondeclarations[] = $tool['function'];
        }

        $payload = [
            'systemInstruction' => ['parts' => [['text' => $systemprompt]]],
            'contents'          => $contents,
            'tools'             => [['functionDeclarations' => $functiondeclarations]],
            'generationConfig'  => ['maxOutputTokens' => $maxtokens],
        ];

        $curl = new \curl();
        $curl->setHeader('Content-Type: application/json');
        $curl->setopt(['CURLOPT_TIMEOUT' => 30]);

        $response = $curl->post($url, json_encode($payload));

        return $this->parse_response($curl, $response);
    }

    /**
     * Parses the Gemini API response.
     *
     * @param \curl $curl
     * @param mixed $response
     * @return array
     */
    private function parse_response(\curl $curl, $response): array {
        provider_helper::check_http_status($curl);

        $data = json_decode($response, true);
        if ($data === null) {
            debugging('Gemini provider JSON decode error: ' . $response, DEBUG_DEVELOPER);
            throw new \moodle_exception('error_generic', 'local_campusai');
        }

        $candidate = $data['candidates'][0] ?? [];
        $parts = $candidate['content']['parts'] ?? [];

        $content = '';
        $toolcalls = [];
        foreach ($parts as $part) {
            if (!empty($part['text'])) {
                $content .= $part['text'];
            }
            if (!empty($part['functionCall'])) {
                $toolcalls[] = [
                    'name'      => $part['functionCall']['name'] ?? '',
                    'arguments' => $part['functionCall']['args'] ?? [],
                ];
            }
        }

        $tokens = $data['usageMetadata']['totalTokenCount'] ?? 0;

        return [
            'content'    => $content,
            'tool_calls' => $toolcalls,
            'tokens'     => $tokens,
        ];
    }
}
