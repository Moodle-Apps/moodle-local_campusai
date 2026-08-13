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
 * Shared helper methods for AI providers.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider_helper {
    /**
     * Parses an OpenAI-compatible chat completion response.
     *
     * @param \curl $curl
     * @param mixed $response
     * @return array
     */
    public static function parse_openai_like(\curl $curl, $response): array {
        $info = $curl->get_info();
        $httpcode = $info['http_code'] ?? 0;

        if ($httpcode == 401 || $httpcode == 403) {
            throw new \moodle_exception('error_license_not_active', 'local_campusai');
        }
        if ($httpcode == 429) {
            throw new \moodle_exception('error_ratelimit', 'local_campusai');
        }

        $data = json_decode($response, true);
        if ($data === null) {
            debugging('Provider JSON decode error: ' . $response, DEBUG_DEVELOPER);
            throw new \moodle_exception('error_generic', 'local_campusai');
        }

        $message = $data['choices'][0]['message'] ?? [];
        $content = $message['content'] ?? '';
        $toolcalls = [];

        if (!empty($message['tool_calls'])) {
            foreach ($message['tool_calls'] as $tc) {
                if (($tc['type'] ?? '') === 'function') {
                    $toolcalls[] = [
                        'name'      => $tc['function']['name'] ?? '',
                        'arguments' => json_decode($tc['function']['arguments'] ?? '{}', true) ?? [],
                    ];
                }
            }
        }

        return [
            'content'    => $content,
            'tool_calls' => $toolcalls,
            'tokens'     => $data['usage']['total_tokens'] ?? 0,
        ];
    }

    /**
     * Checks the HTTP status of a provider response and throws on errors.
     *
     * @param \curl $curl
     * @return int HTTP status code.
     */
    public static function check_http_status(\curl $curl): int {
        $info = $curl->get_info();
        $httpcode = $info['http_code'] ?? 0;

        if ($httpcode == 401 || $httpcode == 403) {
            throw new \moodle_exception('error_license_not_active', 'local_campusai');
        }
        if ($httpcode == 429) {
            throw new \moodle_exception('error_ratelimit', 'local_campusai');
        }

        return (int) $httpcode;
    }
}
