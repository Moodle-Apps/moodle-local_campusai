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
 * course_grades function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_grades extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'course_grades';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_course_grades_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What are my grades in this course?',
            'Show my course grades.',
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
            'required' => ['courseid'],
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

        $courseid = $args['courseid'];

        if (!is_enrolled(\context_course::instance($courseid), $userid)) {
            return get_string('error_no_course_access', 'local_campusai');
        }

        $coursecontext = \context_course::instance($courseid);
        if (!has_capability('gradereport/user:view', $coursecontext, $userid)) {
            return get_string('function_course_grades_error_permission', 'local_campusai');
        }

        $sql = "SELECT gi.itemname, gi.itemtype, gi.grademax, g.finalgrade, g.feedback
                  FROM {grade_items} gi
             LEFT JOIN {grade_grades} g ON g.itemid = gi.id AND g.userid = :userid
                 WHERE gi.courseid = :courseid
              ORDER BY gi.sortorder ASC";

        $records = $DB->get_records_sql($sql, ['userid' => $userid, 'courseid' => $courseid]);

        if (empty($records)) {
            return get_string('function_course_grades_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $r) {
            if ($r->itemtype === 'course') {
                continue;
            }
            $grade = $r->finalgrade !== null ? round($r->finalgrade, 2) : '-';
            $max = round($r->grademax, 2);
            $lines[] = '- **' . ($r->itemname ?: get_string('grade')) . '**: ' . $grade . ' / ' . $max;
        }

        if (empty($lines)) {
            return get_string('function_course_grades_none_published', 'local_campusai');
        }

        return implode("\n", $lines);
    }
}
