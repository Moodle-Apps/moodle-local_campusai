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



namespace local_campusai\functions\teacher; defined('MOODLE_INTERNAL') || die(); class gradebook_summary extends base_teacher { public function get_definition(): array { return [ 'name' => 'get_gradebook_summary', 'description' => 'Get grade averages and distribution per activity in a course.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => ['type' => 'integer', 'description' => 'The course ID.'], ], 'required' => ['course_id'], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = (int)($arguments['course_id'] ?? 0); if (!$courseid || !$this->is_teacher_in_course($courseid)) { return ['error' => 'Invalid course or you are not a teacher in this course.']; } $course = get_course($courseid); $sql = "SELECT gi.id, gi.itemname, gi.itemtype, gi.grademax,
                       COUNT(gg.id) AS gradedcount,
                       AVG(gg.finalgrade) AS avggrade,
                       MIN(gg.finalgrade) AS mingrade,
                       MAX(gg.finalgrade) AS maxgrade
                  FROM {grade_items} gi
             LEFT JOIN {grade_grades} gg ON gi.id = gg.itemid AND gg.finalgrade IS NOT NULL
                 WHERE gi.courseid = ?
              GROUP BY gi.id, gi.itemname, gi.itemtype, gi.grademax
              ORDER BY gi.itemtype, gi.sortorder"; $items = $DB->get_records_sql($sql, [$courseid]); $result = []; foreach ($items as $item) { if ($item->gradedcount == 0) continue; $avg = round((float)$item->avggrade, 1); $pct = $item->grademax > 0 ? round(($avg / $item->grademax) * 100) : 0; $result[] = [ 'activity' => $item->itemname ?: ucfirst($item->itemtype), 'type' => $item->itemtype, 'students_graded' => (int)$item->gradedcount, 'average' => $avg, 'max_possible' => (float)$item->grademax, 'average_pct' => $pct, 'min' => round((float)$item->mingrade, 1), 'max' => round((float)$item->maxgrade, 1), ]; } return ['course' => $course->fullname, 'activities' => $result]; } } 
