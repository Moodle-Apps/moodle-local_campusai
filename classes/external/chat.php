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

namespace local_campusai\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;
use local_campusai\handler;

/**
 * External service for the Campus Assistant chat.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class chat extends external_api {
    /**
     * Returns the parameter definition.
     *
     * @return external_function_parameters
     */
    public static function send_message_parameters(): external_function_parameters {
        return new external_function_parameters([
            'message' => new external_value(
                PARAM_RAW_TRIMMED,
                get_string('external_param_message', 'local_campusai'),
                VALUE_REQUIRED
            ),
        ]);
    }

    /**
     * Returns the return value definition.
     *
     * @return external_single_structure
     */
    public static function send_message_returns(): external_single_structure {
        return new external_single_structure([
            'reply'    => new external_value(PARAM_RAW, get_string('external_return_reply', 'local_campusai')),
            'warnings' => new external_warnings(),
        ]);
    }

    /**
     * Sends a user message to the assistant and returns the reply.
     *
     * @param string $message User message.
     * @return array
     */
    public static function send_message(string $message): array {
        global $USER;

        $params = self::validate_parameters(self::send_message_parameters(), ['message' => $message]);
        $context = \context_system::instance();
        self::validate_context($context);

        if (!has_capability('local/campusai:use', $context)) {
            return [
                'reply'    => '',
                'warnings' => [[
                    'item'        => 'campusai',
                    'itemid'      => 0,
                    'warningcode' => 'nopermissions',
                    'message'     => get_string('external_error_nopermission', 'local_campusai'),
                ]],
            ];
        }

        $reply = handler::handle((int) $USER->id, $params['message']);

        if ($reply === '') {
            return [
                'reply'    => '',
                'warnings' => [[
                    'item'        => 'campusai',
                    'itemid'      => 0,
                    'warningcode' => 'nolicense',
                    'message'     => get_string('external_error_noreply', 'local_campusai'),
                ]],
            ];
        }

        return [
            'reply'    => $reply,
            'warnings' => [],
        ];
    }
}
