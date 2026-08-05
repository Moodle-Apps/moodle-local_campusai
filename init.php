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



require(dirname(dirname(dirname(__FILE__))) . '/config.php'); header('Content-Type: application/javascript'); header('Cache-Control: no-cache, must-revalidate'); $licensekey = trim((string) get_config('local_campusai', 'licensekey')); if (empty($licensekey)) { return; } $context = context_system::instance(); if (!has_capability('local/campusai:use', $context)) { return; } if (!isloggedin() || isguestuser()) { return; } $color = get_config('local_campusai', 'color') ?: '#0066CC'; $title = get_config('local_campusai', 'title') ?: 'Campus Assistant'; $position = get_config('local_campusai', 'position') ?: 'bottom-right'; $lang = get_config('local_campusai', 'language') ?: 'es'; $iconurl = ''; $syscontext = context_system::instance(); $fs = get_file_storage(); $files = $fs->get_area_files($syscontext->id, 'local_campusai', 'fabicon', 0, 'sortorder', false); foreach ($files as $file) { if ($file->is_valid_image()) { $iconurl = moodle_url::make_pluginfile_url($syscontext->id, 'local_campusai', 'fabicon', 0, '/', $file->get_filename())->out(); break; } } $isadmin = false; if (isloggedin() && !isguestuser()) { $isadmin = \local_campusai\functions\registry::is_admin_mode($USER->id); } $config = [ 'ajaxUrl' => $CFG->wwwroot . '/local/campusai/ajax.php', 'sesskey' => sesskey(), 'color' => $color, 'position' => $position, 'title' => $isadmin ? $title . ' (Admin)' : $title, 'defaultLang' => $lang, 'iconUrl' => $iconurl, 'isAdmin' => $isadmin, 'userRole' => \local_campusai\functions\registry::get_role_type($USER->id), ]; echo 'document.addEventListener("DOMContentLoaded", function(){'; echo '  if (window.CampusAI) {'; echo '    CampusAI.init(' . json_encode($config) . ');'; echo '  }'; echo '});'; 
