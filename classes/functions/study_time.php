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



 namespace local_campusai\functions; defined('MOODLE_INTERNAL') || die(); class study_time extends base_function { public function get_definition(): array { return [ 'name' => 'get_study_time', 'description' => 'Get your study time stats: last access and engagement metrics per course.', 'parameters' => [ 'type' => 'object', 'properties' => new \stdClass(), ], ]; } public function execute(array $arguments): array { global $DB; $courses = enrol_get_users_courses($this->userid); $courseids = array_keys($courses); if (empty($courseids)) { return ['courses' => [], 'message' => 'No courses enrolled.']; } list($insql, $inparams) = $DB->get_in_or_equal($courseids); $params = array_merge([$this->userid], $inparams); $lastaccess = $DB->get_records_select( 'user_lastaccess', "userid = ? AND courseid $insql", $params, '', 'courseid, timeaccess' ); $cutoff = time() - (30 * DAYSECS); $params2 = array_merge([$this->userid, $cutoff], $inparams); $logsql = "SELECT courseid, COUNT(*) AS views
                     FROM {logstore_standard_log}
                    WHERE userid = ? AND timecreated >= ? AND courseid $insql AND action = 'viewed'
                    GROUP BY courseid"; $viewcounts = $DB->get_records_sql($logsql, $params2); $result = []; foreach ($courses as $course) { $access = $lastaccess[$course->id]->timeaccess ?? 0; $views = $viewcounts[$course->id]->views ?? 0; $result[] = [ 'course' => $course->fullname, 'last_access' => $access > 0 ? $this->format_date($access) : 'Never', 'views_30days' => (int)$views, ]; } return ['courses' => $result]; } } 
