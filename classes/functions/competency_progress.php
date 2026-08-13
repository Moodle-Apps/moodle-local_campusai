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

namespace local_campusai\functions;

/**
 * competency_progress function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class competency_progress extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'competency_progress';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_competency_progress_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What is my competency progress?',
            'Show my skill achievements.',
        ];
    }

    /**
     * Returns the JSON schema parameters.
     *
     * @return array
     */
    public static function parameters(): array {
        return [
            'type' => 'object',
            'properties' => [
                'courseid' => [
                    'type' => 'integer',
                    'description' => get_string('param_courseid', 'local_campusai'),
                ],
            ],
        ];
    }

    /**
     * Executes the function and returns a plain text result.
     * @param int $userid
     * @param array $args
     * @return string
     */
    public function execute(int $userid, array $args): string {
        global $DB;

        $courseid = $args['courseid'] ?? 0;

        if ($courseid && !is_enrolled(\context_course::instance($courseid), $userid)) {
            return get_string('error_no_course_access', 'local_campusai');
        }

        if (!$DB->get_manager()->table_exists('competency_usercomp')) {
            return get_string('function_competency_progress_not_available', 'local_campusai');
        }

        $params = ['userid' => $userid];
        $coursewhere = '';
        if ($courseid) {
            $coursewhere = 'AND c.courseid = :courseid';
            $params['courseid'] = $courseid;
        }

        $sql = "SELECT c.id, c.shortname, c.description, uc.proficiency, uc.grade
                  FROM {competency_usercomp} uc
                  JOIN {competency} c ON c.id = uc.competencyid
                 WHERE uc.userid = :userid $coursewhere
              ORDER BY c.shortname ASC";

        $records = $DB->get_records_sql($sql, $params, 0, 20);

        if (empty($records)) {
            return get_string('function_competency_progress_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $r) {
            $status = $r->proficiency
                ? get_string('status_competent', 'local_campusai')
                : get_string('status_in_progress', 'local_campusai');
            $lines[] = get_string(
                'function_competency_progress_item',
                'local_campusai',
                (object) ['shortname' => $r->shortname, 'status' => $status]
            );
        }

        return implode("\n", $lines);
    }
}
