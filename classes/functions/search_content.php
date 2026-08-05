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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class search_content extends base_function { public function get_definition(): array { return [ 'name' => 'search_course_content', 'description' => 'Search for text in activity names, resource titles, and section summaries across your courses.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'query' => [ 'type' => 'string', 'description' => 'The search query.', ], 'course_id' => [ 'type' => 'integer', 'description' => 'Optional course ID to limit search scope.', ], ], 'required' => ['query'], ], ]; } public function execute(array $arguments): array { global $DB; $query = trim($arguments['query'] ?? ''); $courseid = $arguments['course_id'] ?? null; if (strlen($query) < 2) { return ['results' => [], 'message' => 'Query too short.']; } $courses = enrol_get_users_courses($this->userid); $courseids = array_keys($courses); if ($courseid) { $courseids = in_array($courseid, $courseids) ? [$courseid] : []; } if (empty($courseids)) { return ['results' => [], 'message' => 'No valid courses.']; } $results = []; $escaped = str_replace(['\%', '\_'], ['%', '_'], $query); foreach ($courseids as $cid) { $course = $courses[$cid]; $modinfo = get_fast_modinfo($cid, $this->userid); $cms = $modinfo->get_cms(); foreach ($cms as $cm) { if (!$cm->visible) { continue; } if (stripos($cm->name, $query) !== false) { $results[] = [ 'course' => $course->fullname, 'name' => $cm->name, 'type' => $cm->modname, 'section' => $cm->sectionnum, 'url' => (string)(new \moodle_url('/mod/' . $cm->modname . '/view.php', ['id' => $cm->id])), ]; } } $sections = $modinfo->get_section_info_all(); foreach ($sections as $section) { if (empty($section->summary)) { continue; } if (stripos(strip_tags($section->summary), $query) !== false) { $results[] = [ 'course' => $course->fullname, 'name' => 'Section: ' . ($section->name ?: ('Topic ' . $section->section)), 'type' => 'section', 'section' => $section->section, 'url' => (string)(new \moodle_url('/course/view.php', ['id' => $cid, 'section' => $section->section])), ]; } } } return ['results' => $results, 'count' => count($results)]; } } 
