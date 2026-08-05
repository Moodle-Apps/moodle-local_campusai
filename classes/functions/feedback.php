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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class feedback extends base_function { public function get_definition(): array { return [ 'name' => 'get_feedback', 'description' => 'Get teacher feedback and comments on your graded assignments. Optionally filter by course.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => [ 'type' => 'integer', 'description' => 'Optional course ID to filter feedback.', ], ], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = $arguments['course_id'] ?? null; $sql = "SELECT gg.id, gg.finalgrade, gg.timemodified, gi.courseid, gi.itemname, gi.itemtype,
                       c.fullname AS coursename
                  FROM {grade_grades} gg
                  JOIN {grade_items} gi ON gg.itemid = gi.id
                  JOIN {course} c ON gi.courseid = c.id
                 WHERE gg.userid = ? AND gg.finalgrade IS NOT NULL"; $params = [$this->userid]; if ($courseid) { $sql .= " AND gi.courseid = ?"; $params[] = $courseid; } $sql .= " ORDER BY gg.timemodified DESC LIMIT 20"; $grades = $DB->get_records_sql($sql, $params); $result = []; foreach ($grades as $grade) { $feedback = ''; if ($grade->itemtype === 'mod') { $feedbackrec = $DB->get_record('assignfeedback_comments', ['grade' => $grade->id], 'commenttext', IGNORE_MISSING); if ($feedbackrec && !empty($feedbackrec->commenttext)) { $feedback = strip_tags($feedbackrec->commenttext); } } if (!empty($feedback) || $grade->finalgrade !== null) { $result[] = [ 'course' => $grade->coursename, 'assignment' => $grade->itemname ?: 'N/A', 'grade' => round((float)$grade->finalgrade, 1), 'feedback' => $feedback ?: 'No written feedback provided.', 'date' => $this->format_date($grade->timemodified), ]; } } return ['feedback' => $result]; } } 
