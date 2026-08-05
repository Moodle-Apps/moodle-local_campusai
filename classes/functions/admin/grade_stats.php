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



namespace local_campusai\functions\admin; class grade_stats extends base_admin { public function get_definition(): array { return [ 'name' => 'get_grade_stats', 'description' => 'Get pass/fail grade statistics across courses. Returns average grade and pass rate per course.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_name' => [ 'type' => 'string', 'description' => 'Optional: filter by course name (partial match). If omitted, returns all courses.', ], ], ], ]; } public function execute(array $arguments): array { global $DB; $search = trim($arguments['course_name'] ?? ''); $courses = enrol_get_users_courses($this->userid, true); $results = []; foreach ($courses as $course) { if ($search && stripos($course->fullname, $search) === false) { continue; } $gradeitems = \grade_item::fetch_all(['courseid' => $course->id]); if (empty($gradeitems)) continue; $courseitem = null; foreach ($gradeitems as $item) { if ($item->itemtype === 'course') { $courseitem = $item; break; } } if (!$courseitem) continue; $grades = $DB->get_records('grade_grades', ['itemid' => $courseitem->id, 'finalgrade' => null], '', 'userid, finalgrade'); $grades = $DB->get_records_select('grade_grades', "itemid = ? AND finalgrade IS NOT NULL AND hidden = 0", [$courseitem->id], '', 'userid, finalgrade'); if (count($grades) === 0) continue; $values = array_map(function($g) { return (float)$g->finalgrade; }, array_values($grades)); $avg = array_sum($values) / count($values); $passing = 0; $passmark = $courseitem->gradepass > 0 ? $courseitem->gradepass : ($courseitem->grademax * 0.5); foreach ($values as $v) { if ($v >= $passmark) $passing++; } $results[] = [ 'course' => $course->fullname, 'students_graded' => count($values), 'average_grade' => round($avg, 1), 'max_grade' => (float)$courseitem->grademax, 'pass_rate' => round(($passing / count($values)) * 100, 1) . '%', 'passing' => $passing, 'failing' => count($values) - $passing, ]; if (count($results) >= 20) break; } return ['grade_stats' => $results]; } } 
