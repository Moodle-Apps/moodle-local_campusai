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
 * quiz_results function.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_results extends base_function {
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public static function name(): string {
        return 'quiz_results';
    }

    /**
     * Returns the human-readable description.
     *
     * @return string
     */
    public static function description(): string {
        return get_string('function_quiz_results_description', 'local_campusai');
    }

    /**
     * Returns example questions.
     *
     * @return array
     */
    public static function examples(): array {
        return [
            'Show my recent quiz results.',
            'What scores did I get on quizzes?',
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
                'limit' => [
                    'type' => 'integer',
                    'description' => get_string('function_quiz_results_param_limit', 'local_campusai'),
                    'default' => 10,
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

        $limit = $args['limit'] ?? 10;

        $sql = "SELECT qa.id, q.name, qa.sumgrades, q.grade, qa.timefinish, c.fullname
                  FROM {quiz_attempts} qa
                  JOIN {quiz} q ON q.id = qa.quiz
                  JOIN {course} c ON c.id = q.course
                 WHERE qa.userid = :userid AND qa.state = 'finished' AND qa.timefinish > 0
              ORDER BY qa.timefinish DESC";

        $records = $DB->get_records_sql($sql, ['userid' => $userid], 0, $limit);

        if (empty($records)) {
            return get_string('function_quiz_results_empty', 'local_campusai');
        }

        $lines = [];
        foreach ($records as $r) {
            $score = $r->grade > 0 ? round(($r->sumgrades / $r->grade) * 100, 1) : 0;
            $date = userdate($r->timefinish, get_string('strftimedatetime', 'langconfig'));
            $lines[] = get_string('function_quiz_results_item', 'local_campusai', (object) [
                'name' => $r->name,
                'fullname' => $r->fullname,
                'score' => $score,
                'date' => $date,
            ]);
        }

        return implode("\n", $lines);
    }
}
