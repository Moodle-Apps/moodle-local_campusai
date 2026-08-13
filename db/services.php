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
 * Web service definitions for Campus Assistant.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$services = [
    'local_campusai_chat' => [
        'functions'       => ['local_campusai_chat_send_message'],
        'restrictedusers' => 0,
        'enabled'         => 1,
    ],
];

$functions = [
    'local_campusai_chat_send_message' => [
        'classname'      => 'local_campusai\external\chat',
        'methodname'     => 'send_message',
        'classpath'      => 'classes/external/chat.php',
        'description'    => get_string('external_send_message_description', 'local_campusai'),
        'type'           => 'write',
        'ajax'           => true,
        'capabilities'   => 'local/campusai:use',
        'loginrequired'  => true,
    ],
];
