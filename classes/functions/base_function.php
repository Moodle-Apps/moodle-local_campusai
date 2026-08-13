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
 * Abstract base class for assistant functions.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_function {
    /**
     * Unique snake_case identifier used by the LLM.
     *
     * @return string
     */
    abstract public static function name(): string;

    /**
     * Human-readable description for the LLM.
     *
     * @return string
     */
    abstract public static function description(): string;

    /**
     * JSON Schema parameters in OpenAI tool format.
     *
     * @return array
     */
    abstract public static function parameters(): array;

    /**
     * Executes the function and returns a plain text result.
     *
     * @param int $userid User ID.
     * @param array $args Arguments from the LLM.
     * @return string
     */
    abstract public function execute(int $userid, array $args): string;

    /**
     * Returns example questions that this function can answer.
     *
     * @return string[]
     */
    public static function examples(): array {
        return [];
    }

    /**
     * Returns the tool definition for this function.
     *
     * @return array
     */
    final public static function to_tool(): array {
        return [
            'type'     => 'function',
            'function' => [
                'name'        => static::name(),
                'description' => static::description(),
                'parameters'  => static::parameters(),
            ],
        ];
    }
}
