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
 * Anthropic Claude API provider.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class claude_provider implements provider_interface {
    /** @var string API key. */
    private string $apikey;

    /** @var string Unused for Claude. */
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
        return 'claude';
    }

    /**
     * Sends the request to Claude.
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
        $url = 'https://api.anthropic.com/v1/messages';

        $payload = [
            'model'      => $model,
            'system'     => $systemprompt,
            'messages'   => $messages,
            'tools'      => $tools,
            'max_tokens' => $maxtokens,
        ];

        $curl = new \curl();
        $curl->setHeader('x-api-key: ' . $this->apikey);
        $curl->setHeader('anthropic-version: 2023-06-01');
        $curl->setHeader('Content-Type: application/json');
        $curl->setopt(['CURLOPT_TIMEOUT' => 30]);

        $response = $curl->post($url, json_encode($payload));

        return $this->parse_response($curl, $response);
    }

    /**
     * Parses the Claude API response.
     *
     * @param \curl $curl
     * @param mixed $response
     * @return array
     */
    private function parse_response(\curl $curl, $response): array {
        provider_helper::check_http_status($curl);

        $data = json_decode($response, true);
        if ($data === null) {
            debugging('Claude provider JSON decode error: ' . $response, DEBUG_DEVELOPER);
            throw new \moodle_exception('error_generic', 'local_campusai');
        }

        $content = '';
        $toolcalls = [];
        foreach ($data['content'] ?? [] as $block) {
            if ($block['type'] === 'text') {
                $content .= $block['text'];
            } else if ($block['type'] === 'tool_use') {
                $toolcalls[] = [
                    'name'      => $block['name'] ?? '',
                    'arguments' => $block['input'] ?? [],
                ];
            }
        }

        return [
            'content'    => $content,
            'tool_calls' => $toolcalls,
            'tokens'     => ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0),
        ];
    }
}
