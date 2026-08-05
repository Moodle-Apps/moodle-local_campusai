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



 function local_campusai_before_standard_html_head() { global $CFG, $PAGE, $USER; if (!isset($USER->id) || !$USER->id) { return ''; } if (isguestuser()) { return ''; } if (!get_config('local_campusai', 'enabled')) { return ''; } $licensekey = trim((string) get_config('local_campusai', 'licensekey')); if (empty($licensekey)) { return ''; } $context = context_system::instance(); if (!has_capability('local/campusai:use', $context)) { return ''; } if (!isset($PAGE->pagelayout) || $PAGE->pagelayout === 'admin' || $PAGE->pagelayout === 'maintenance' || $PAGE->pagelayout === 'login') { return ''; } $config = array( 'ajaxUrl' => $CFG->wwwroot . '/local/campusai/ajax.php', 'sesskey' => sesskey(), 'color' => get_config('local_campusai', 'color') ?: '#0066CC', 'position' => get_config('local_campusai', 'position') ?: 'bottom-right', 'title' => get_config('local_campusai', 'title') ?: 'Campus Assistant', 'welcome' => get_config('local_campusai', 'welcome') ?: 'Hi! How can I help you today?', ); $configjson = json_encode($config); $html = '<link rel="stylesheet" href="' . $CFG->wwwroot . '/local/campusai/styles.css">'; $html .= '<script>'; $html .= 'require(["' . $CFG->wwwroot . '/local/campusai/javascript/campusai.js"], function(m){ if(m&&m.init){m.init(' . $configjson . ');}});'; $html .= '</script>'; return $html; } function local_campusai_cron() { try { \local_campusai\conversation::purge_old_logs(); } catch (\Throwable $e) { error_log('Campus Assistant cron error: ' . $e->getMessage()); } } function local_campusai_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) { if ($context->contextlevel != CONTEXT_SYSTEM) { return false; } if ($filearea !== "fabicon") { return false; } $itemid = array_shift($args); $filename = array_shift($args); $filepath = "/"; $fs = get_file_storage(); $file = $fs->get_file($context->id, "local_campusai", $filearea, $itemid, $filepath, $filename); if (!$file) { return false; } send_stored_file($file, 86400, 0, $forcedownload, $options); } 
