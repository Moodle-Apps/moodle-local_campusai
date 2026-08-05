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



require(dirname(dirname(dirname(__FILE__))) . '/config.php'); header('Content-Type: application/json'); if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('HTTP/1.1 405 Method Not Allowed'); echo json_encode(['success' => false, 'message' => 'Method not allowed']); exit; } try { require_login(); if (isguestuser() || !isloggedin()) { echo json_encode(['success' => false, 'message' => get_string('error_generic', 'local_campusai')]); exit; } confirm_sesskey(required_param('sesskey', PARAM_RAW)); $context = context_system::instance(); require_capability('local/campusai:use', $context); $license = \local_campusai\license_manager::get_status(); if (!$license['valid']) { echo json_encode([ 'success' => false, 'message' => '🔒 License not active: ' . ($license['error'] ?? 'Unknown error') . '. Go to Site Administration > Plugins > Campus Assistant to configure your license key.', ]); exit; } $message = required_param('message', PARAM_RAW); $lang = optional_param('lang', 'es', PARAM_ALPHA); $handler = new \local_campusai\handler($USER->id); $handler->set_language($lang); $response = $handler->process($message); echo json_encode($response); } catch (\Throwable $e) { error_log('Campus Assistant error: ' . $e->getMessage() . "\n" . $e->getTraceAsString()); echo json_encode(['success' => false, 'message' => get_string('error_generic', 'local_campusai')]); } 
