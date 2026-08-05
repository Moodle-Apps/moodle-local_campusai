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



namespace local_campusai\functions\admin; defined('MOODLE_INTERNAL') || die(); class storage_usage extends base_admin { public function get_definition(): array { return [ 'name' => 'get_storage_usage', 'description' => 'Get estimated storage usage and top largest files in the system.', 'parameters' => ['type' => 'object', 'properties' => new \stdClass()], ]; } public function execute(array $arguments): array { global $CFG, $DB; $totalsize = $DB->get_field_sql("SELECT SUM(filesize) FROM {files} WHERE filesize > 0"); $totalfiles = $DB->count_records_select('files', 'filesize > 0'); $topfiles = $DB->get_records_select( 'files', 'filesize > 0', [], 'filesize DESC', 'filename, filesize, component, filearea, timemodified', 0, 10 ); $top = []; foreach ($topfiles as $file) { $top[] = [ 'filename' => $file->filename, 'size_mb' => round($file->filesize / (1024 * 1024), 2), 'component' => $file->component, 'area' => $file->filearea, ]; } $sql = "SELECT component, SUM(filesize) AS total
                  FROM {files}
                 WHERE filesize > 0
              GROUP BY component
              ORDER BY total DESC LIMIT 10"; $bycomponent = $DB->get_records_sql($sql); $components = []; foreach ($bycomponent as $comp) { $components[] = [ 'component' => $comp->component, 'size_mb' => round($comp->total / (1024 * 1024), 2), ]; } return [ 'total_size_gb' => round(($totalsize ?: 0) / (1024 * 1024 * 1024), 2), 'total_files' => (int)$totalfiles, 'top_files' => $top, 'by_component' => $components, ]; } } 
