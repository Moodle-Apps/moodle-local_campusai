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

namespace local_campusai\functions\teacher;
/**
 * Gradebook summary.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradebook_summary extends base_teacher {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'teacher_gradebook_summary';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_teacher_gradebook_summary_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What is the gradebook summary for my course?',
            'Show average, min, and max grades.',
        ];
    }

    /**
     * Returns the JSON schema parameters.
     *
     * @return array
     */
    public static function parameters(): array {
        return [
            'type'       => 'object',
            'properties' => [
                'courseid' => [
                    'type'        => 'integer',
                    'description' => get_string('function_teacher_gradebook_summary_param_courseid', 'local_campusai'),
                ],
            ],
            'required'   => ['courseid'],
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

        $courseid = !empty($args['courseid']) ? (int) $args['courseid'] : 0;
        if (!$courseid) {
            return get_string('function_teacher_gradebook_summary_missing_courseid', 'local_campusai');
        }

        $course = $DB->get_record('course', ['id' => $courseid]);
        if (!$course || !has_capability('moodle/course:update', \context_course::instance($courseid), $userid)) {
            return get_string('function_teacher_gradebook_summary_not_teacher', 'local_campusai');
        }

        $summary = $DB->get_record_sql(
            "SELECT AVG(gg.finalgrade / gi.grademax * 100) AS avgpercent,
                    MAX(gg.finalgrade / gi.grademax * 100) AS maxpercent,
                    MIN(gg.finalgrade / gi.grademax * 100) AS minpercent,
                    COUNT(*) AS count
               FROM {grade_grades} gg
               JOIN {grade_items} gi ON gi.id = gg.itemid
              WHERE gi.courseid = :courseid
                AND gi.itemtype = 'course'
                AND gg.finalgrade IS NOT NULL",
            ['courseid' => $courseid]
        );

        if (!$summary || !$summary->count) {
            return get_string('function_teacher_gradebook_summary_empty', 'local_campusai');
        }

        $avg = round((float) $summary->avgpercent, 1);
        $min = round((float) $summary->minpercent, 1);
        $max = round((float) $summary->maxpercent, 1);

        return get_string('function_teacher_gradebook_summary_result', 'local_campusai', (object) [
            'avg' => $avg,
            'min' => $min,
            'max' => $max,
            'count' => $summary->count,
        ]);
    }
}
