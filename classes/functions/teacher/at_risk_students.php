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



namespace local_campusai\functions\teacher; defined('MOODLE_INTERNAL') || die(); class at_risk_students extends base_teacher { public function get_definition(): array { return [ 'name' => 'get_at_risk_students', 'description' => 'Get students at risk: low grades, low completion, or no recent access in a course.', 'parameters' => [ 'type' => 'object', 'properties' => [ 'course_id' => ['type' => 'integer', 'description' => 'The course ID.'], 'threshold' => ['type' => 'integer', 'description' => 'Risk threshold percentage (default 50).'], ], 'required' => ['course_id'], ], ]; } public function execute(array $arguments): array { global $DB; $courseid = (int)($arguments['course_id'] ?? 0); $threshold = (int)($arguments['threshold'] ?? 50); if (!$courseid || !$this->is_teacher_in_course($courseid)) { return ['error' => 'Invalid course or you are not a teacher in this course.']; } $course = get_course($courseid); $context = \context_course::instance($courseid); $students = get_role_users(5, $context, false, 'u.id, u.firstname, u.lastname', '', '', '', 500); if (empty($students)) { return ['at_risk' => [], 'message' => 'No students enrolled.']; } $completion = new \completion_info($course); $modinfo = get_fast_modinfo($courseid); $cms = $modinfo->get_cms(); $trackable = array_filter($cms, fn($cm) => $cm->completion); $at_risk = []; $now = time(); foreach ($students as $student) { $riskreasons = []; $lastaccess = $DB->get_field('user_lastaccess', 'timeaccess', ['userid' => $student->id, 'courseid' => $courseid]); if (!$lastaccess || ($now - $lastaccess) > (14 * DAYSECS)) { $days = $lastaccess ? round(($now - $lastaccess) / DAYSECS) : 0; $riskreasons[] = $days > 0 ? "No access in {$days} days" : 'Never accessed'; } if (!empty($trackable)) { $completed = 0; foreach ($trackable as $cm) { $data = $completion->get_data($cm, false, $student->id); if ($data->completionstate == COMPLETION_COMPLETE || $data->completionstate == COMPLETION_COMPLETE_PASS) { $completed++; } } $pct = round(($completed / count($trackable)) * 100); if ($pct < $threshold) { $riskreasons[] = "Completion: {$pct}%"; } } $gradequery = $DB->get_record_sql( "SELECT AVG(gg.finalgrade) AS avggrade, MAX(gi.grademax) AS grademax
                   FROM {grade_grades} gg
                   JOIN {grade_items} gi ON gg.itemid = gi.id
                  WHERE gg.userid = ? AND gi.courseid = ? AND gg.finalgrade IS NOT NULL", [$student->id, $courseid] ); if ($gradequery && $gradequery->avggrade !== null && $gradequery->grademax > 0) { $gradepct = round(($gradequery->avggrade / $gradequery->grademax) * 100); if ($gradepct < $threshold) { $riskreasons[] = "Avg grade: {$gradepct}%"; } } if (!empty($riskreasons)) { $at_risk[] = [ 'name' => trim($student->firstname . ' ' . $student->lastname), 'reasons' => $riskreasons, ]; } } return ['at_risk_students' => $at_risk, 'count' => count($at_risk), 'threshold' => $threshold]; } } 
