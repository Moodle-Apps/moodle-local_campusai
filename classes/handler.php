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

namespace local_campusai;

use local_campusai\functions\registry;
use local_campusai\provider\factory;

/**
 * Orchestrates provider calls, function execution and audit logging.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class handler {
    /** @var int Maximum tool call iterations. */
    private const MAX_TOOL_ITERATIONS = 5;

    /** @var int Default max tokens for provider calls. */
    private const DEFAULT_MAX_TOKENS = 1024;

    /**
     * Handles a user message and returns the assistant reply.
     *
     * @param int $userid User ID.
     * @param string $message Raw user message.
     * @return string Assistant reply.
     */
    public static function handle(int $userid, string $message): string {
        $message = security::sanitize_user_input($message);

        if ($message === '') {
            return get_string('error_generic', 'local_campusai');
        }

        $context = \context_system::instance();
        if (!has_capability('local/campusai:use', $context, $userid)) {
            return get_string('error_generic', 'local_campusai');
        }

        if (!ratelimit::check($userid)) {
            return get_string('error_ratelimit', 'local_campusai');
        }

        $providername = get_config('local_campusai', 'provider');
        if ($providername === 'proxy' && !license_manager::is_proxy_licensed()) {
            return get_string('error_license_not_active', 'local_campusai');
        }

        $functions = registry::for_user($userid);
        $tools = array_map(fn($fn) => $fn::to_tool(), $functions);

        $systemprompt = get_config('local_campusai', 'systemprompt');
        if (empty($systemprompt)) {
            $systemprompt = get_string('default_systemprompt', 'local_campusai');
        }

        $messages = self::build_history($userid);
        $messages[] = ['role' => 'user', 'content' => $message];

        $apikey = ($providername === 'proxy')
            ? get_config('local_campusai', 'licensekey')
            : get_config('local_campusai', 'apikey');
        $jwtsecret = get_config('local_campusai', 'jwtsecret');
        $model = get_config('local_campusai', 'model');
        $maxtokens = (int) get_config('local_campusai', 'maxtokens');
        if ($maxtokens <= 0) {
            $maxtokens = self::DEFAULT_MAX_TOKENS;
        }

        try {
            $provider = factory::create($providername, (string) $apikey, (string) $jwtsecret);
        } catch (\moodle_exception $e) {
            return get_string('error_provider', 'local_campusai');
        }

        $functionscalls = [];
        $totaltokens = 0;

        for ($i = 0; $i < self::MAX_TOOL_ITERATIONS; $i++) {
            $response = $provider->chat($systemprompt, $messages, $tools, (string) $model, $maxtokens);
            $totaltokens += $response['tokens'] ?? 0;

            if (empty($response['tool_calls'])) {
                $assistantmessage = $response['content'] ?? '';
                break;
            }

            $messages[] = [
                'role'         => 'assistant',
                'content'      => $response['content'] ?? '',
                'tool_calls'   => self::normalise_tool_calls_for_provider($response['tool_calls']),
            ];

            foreach ($response['tool_calls'] as $toolcall) {
                $result = self::execute_function($functions, $userid, $toolcall);
                $messages[] = [
                    'role'    => 'tool',
                    'name'    => $toolcall['name'],
                    'content' => $result,
                ];
                $functionscalls[] = [
                    'name'      => $toolcall['name'],
                    'arguments' => $toolcall['arguments'],
                    'result'    => $result,
                ];
            }
        }

        if (!isset($assistantmessage)) {
            $assistantmessage = get_string('error_generic', 'local_campusai');
        }

        $assistantmessage = security::sanitize_assistant_output($assistantmessage);

        conversation::record(
            $userid,
            $message,
            $assistantmessage,
            $providername,
            $totaltokens,
            !empty($functionscalls) ? json_encode($functionscalls) : null
        );

        ratelimit::increment($userid);

        return $assistantmessage;
    }

    /**
     * Builds the recent conversation history for the provider.
     *
     * @param int $userid User ID.
     * @return array
     */
    private static function build_history(int $userid): array {
        $records = conversation::get_recent($userid, 6);
        $records = array_reverse($records);

        $messages = [];
        foreach ($records as $record) {
            $messages[] = ['role' => 'user', 'content' => $record->usermessage];
            $messages[] = ['role' => 'assistant', 'content' => $record->assistantmessage];
        }

        return $messages;
    }

    /**
     * Executes a function call and returns its string result.
     *
     * @param array $functions Available function instances.
     * @param int $userid User ID.
     * @param array $toolcall Tool call data.
     * @return string
     */
    private static function execute_function(array $functions, int $userid, array $toolcall): string {
        $name = $toolcall['name'] ?? '';
        $args = $toolcall['arguments'] ?? [];

        foreach ($functions as $function) {
            if ($function::name() === $name) {
                try {
                    return $function->execute($userid, $args);
                } catch (\Throwable $e) {
                    debugging('Campus Assistant function error: ' . $e->getMessage(), DEBUG_DEVELOPER);
                    return get_string('error_function_execution', 'local_campusai');
                }
            }
        }

        return get_string('error_function_not_available', 'local_campusai');
    }

    /**
     * Normalises tool calls for the provider message format.
     *
     * @param array $toolcalls
     * @return array
     */
    private static function normalise_tool_calls_for_provider(array $toolcalls): array {
        $normalised = [];
        foreach ($toolcalls as $index => $toolcall) {
            $normalised[] = [
                'id'       => (string) $index,
                'type'     => 'function',
                'function' => [
                    'name'      => $toolcall['name'],
                    'arguments' => json_encode($toolcall['arguments'] ?? []),
                ],
            ];
        }
        return $normalised;
    }
}
