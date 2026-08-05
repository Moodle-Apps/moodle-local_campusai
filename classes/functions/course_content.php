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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class course_content extends base_function { public function get_definition(): array { return [ 'name' => 'get_course_activities', 'description' => 'Get the list of available activities and resources in a specific course.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => [ 'type' => 'integer', 'description' => 'The course ID to list activities for.', ], ], ], ]; } public function execute(array $arguments): array { $courseid = (int)($arguments['course_id'] ?? 0); if ($courseid <= 0) { return ['error' => 'A valid course ID is required.']; } if (!$this->is_enrolled($courseid)) { return ['error' => 'You are not enrolled in this course.']; } $modinfo = get_fast_modinfo($courseid, $this->userid); $sections = $modinfo->get_sections(); $result = []; foreach ($sections as $sectionnum => $cms) { $sectioninfo = $modinfo->get_section_info($sectionnum); if (!$sectioninfo->visible) { continue; } $sectionname = get_section_name($courseid, $sectionnum); $activities = []; foreach ($cms as $cm) { if (!$cm->visible || !$cm->uservisible) { continue; } $activities[] = [ 'name' => $cm->name, 'type' => $cm->modname, 'section' => $sectionname, ]; } if (!empty($activities)) { $result[] = [ 'section' => $sectionname, 'activities' => $activities, ]; } } return ['course_content' => $result]; } } 
