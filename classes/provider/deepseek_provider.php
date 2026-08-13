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
 * DeepSeek API provider (OpenAI-compatible).
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deepseek_provider implements provider_interface {
    /** @var string API key. */
    private string $apikey;

    /** @var string Unused for DeepSeek. */
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
        return 'deepseek';
    }

    /**
     * Sends the request to DeepSeek.
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
        $url = 'https://api.deepseek.com/v1/chat/completions';

        $payload = [
            'model'       => $model,
            'messages'    => array_merge([['role' => 'system', 'content' => $systemprompt]], $messages),
            'tools'       => $tools,
            'tool_choice' => 'auto',
            'max_tokens'  => $maxtokens,
        ];

        $curl = new \curl();
        $curl->setHeader('Authorization: Bearer ' . $this->apikey);
        $curl->setHeader('Content-Type: application/json');
        $curl->setopt(['CURLOPT_TIMEOUT' => 30]);

        $response = $curl->post($url, json_encode($payload));

        return provider_helper::parse_openai_like($curl, $response);
    }
}
