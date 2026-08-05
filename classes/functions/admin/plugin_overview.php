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



namespace local_campusai\functions\admin; defined('MOODLE_INTERNAL') || die(); class plugin_overview extends base_admin { public function get_definition(): array { return [ 'name' => 'get_plugin_overview', 'description' => 'Get installed plugins with their versions and status.', 'parameters' => ['type' => 'object', 'properties' => new \stdClass()], ]; } public function execute(array $arguments): array { global $CFG; $pluginman = \core_plugin_manager::instance(); $types = $pluginman->get_plugin_types(); $result = []; foreach ($types as $type => $typename) { $plugins = $pluginman->get_plugins_of_type($type); foreach ($plugins as $plugin) { if ($plugin->versiondb === null) { continue; } $status = 'ok'; if ($plugin->versiondb != $plugin->versiondisk) { $status = 'version_mismatch'; } elseif ($plugin->is_enabled() === false) { $status = 'disabled'; } $result[] = [ 'name' => $plugin->displayname, 'type' => $type, 'version' => $plugin->versiondb, 'status' => $status, 'requires' => $plugin->versiondisk, ]; } } usort($result, function($a, $b) { if ($a['status'] !== 'ok' && $b['status'] === 'ok') return -1; if ($a['status'] === 'ok' && $b['status'] !== 'ok') return 1; return strcmp($a['name'], $b['name']); }); $problems = array_filter($result, fn($p) => $p['status'] !== 'ok'); return [ 'total_plugins' => count($result), 'problems' => count($problems), 'plugins' => array_slice($result, 0, 50), ]; } } 
