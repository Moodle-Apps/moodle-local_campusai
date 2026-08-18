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
 * AJAX endpoint for the Campus Assistant chat.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require(__DIR__ . '/../../config.php');
require_login();

header('Content-Type: application/json; charset=utf-8');

$response = [
    'reply'    => '',
    'warnings' => [],
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['warnings'][] = [
        'item'        => 'campusai',
        'itemid'      => 0,
        'warningcode' => 'error_method_not_allowed',
        'message'     => get_string('error_method_not_allowed', 'local_campusai'),
    ];
    echo json_encode($response);
    exit;
}

require_sesskey();

$context = context_system::instance();
if (!has_capability('local/campusai:use', $context)) {
    $response['warnings'][] = [
        'item'        => 'campusai',
        'itemid'      => 0,
        'warningcode' => 'nopermissions',
        'message'     => get_string('error_generic', 'local_campusai'),
    ];
    echo json_encode($response);
    exit;
}

$message = optional_param('message', '', PARAM_TEXT);
if ($message === '') {
    echo json_encode($response);
    exit;
}

try {
    $reply = \local_campusai\handler::handle($USER->id, $message);
    $response['reply'] = $reply;
} catch (\Throwable $e) {
    debugging('Campus Assistant AJAX error: ' . $e->getMessage(), DEBUG_DEVELOPER);
    $response['warnings'][] = [
        'item'        => 'campusai',
        'itemid'      => 0,
        'warningcode' => 'error_generic',
        'message'     => get_string('error_generic', 'local_campusai'),
    ];
}

echo json_encode($response);
