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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class course_resources extends base_function { public function get_definition(): array { return [ 'name' => 'get_course_resources', 'description' => 'Get study materials and resources (PDFs, videos, links, pages) from a course, excluding activities.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => [ 'type' => 'integer', 'description' => 'The course ID.', ], ], 'required' => ['course_id'], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = (int)($arguments['course_id'] ?? 0); if (!$courseid || !$this->is_enrolled($courseid)) { return ['resources' => [], 'message' => 'Invalid course or not enrolled.']; } $resourcetypes = ['resource', 'url', 'page', 'folder', 'book', 'imscp', 'media', 'videofile']; $modinfo = get_fast_modinfo($courseid, $this->userid); $cms = $modinfo->get_cms(); $resources = []; foreach ($cms as $cm) { if (!in_array($cm->modname, $resourcetypes)) { continue; } if (!$cm->visible) { continue; } $resources[] = [ 'name' => $cm->name, 'type' => $cm->modname, 'section' => $cm->sectionnum, 'url' => (string)(new \moodle_url('/mod/' . $cm->modname . '/view.php', ['id' => $cm->id])), ]; } $bysection = []; foreach ($resources as $res) { $bysection[$res['section']][] = $res; } return ['resources' => $resources, 'by_section' => $bysection]; } } 
