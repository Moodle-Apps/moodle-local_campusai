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
 * submission_status function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submission_status extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'submission_status';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_submission_status_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'What is the status of my assignment?',
            'Did I submit my assignment?',
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
                'assignmentid' => [
                    'type' => 'integer',
                    'description' => get_string('function_submission_status_param_assignmentid', 'local_campusai'),
                ],
            ],
            'required' => ['assignmentid'],
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

        $assignmentid = $args['assignmentid'];

        $assign = $DB->get_record('assign', ['id' => $assignmentid], '*', MUST_EXIST);

        if (!is_enrolled(\context_course::instance($assign->course), $userid)) {
            return get_string('error_no_course_access', 'local_campusai');
        }

        $submission = $DB->get_record('assign_submission', [
            'assignment' => $assignmentid,
            'userid' => $userid,
        ]);

        $status = $submission ? $submission->status : get_string('function_submission_status_no_submission', 'local_campusai');
        if ($assign->duedate) {
            $duedate = userdate($assign->duedate, get_string('strftimedatetime', 'langconfig'));
        } else {
            $duedate = get_string('status_no_due_date', 'local_campusai');
        }

        $grade = $DB->get_record('assign_grades', [
            'assignment' => $assignmentid,
            'userid' => $userid,
        ]);

        $result = get_string('function_submission_status_result', 'local_campusai', (object) [
            'status' => $status,
            'duedate' => $duedate,
        ]);
        if ($grade && $grade->grade !== null) {
            $result .= get_string(
                'function_submission_status_grade',
                'local_campusai',
                (object) ['grade' => round($grade->grade, 2)]
            );
        }

        return $result;
    }
}
