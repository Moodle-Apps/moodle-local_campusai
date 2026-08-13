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
 * Install hook for Campus Assistant.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Sets default configuration values on installation.
 *
 * @return bool
 */
function xmldb_local_campusai_install(): bool {
    set_config('enabled', 1, 'local_campusai');
    set_config('provider', 'proxy', 'local_campusai');
    set_config('ratelimit', '30', 'local_campusai');
    set_config('window', '600', 'local_campusai');
    set_config('auditlog', '1', 'local_campusai');
    set_config('logretention', '90', 'local_campusai');
    set_config('position', 'bottom-right', 'local_campusai');
    set_config('color', '#0066CC', 'local_campusai');
    set_config('title', 'Campus Assistant', 'local_campusai');
    set_config('language', 'en', 'local_campusai');

    return true;
}
