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

namespace local_campusai\provider;

/**
 * Common interface for AI providers.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface provider_interface {
    /**
     * Returns the provider identifier.
     *
     * @return string
     */
    public static function name(): string;

    /**
     * Sends a message to the LLM and returns a normalised response.
     *
     * @param string $systemprompt System instructions.
     * @param array $messages Conversation history.
     * @param array $tools Function calling schema in OpenAI tool format.
     * @param string $model Model identifier.
     * @param int $maxtokens Maximum tokens to generate.
     * @return array Normalised response with keys content, tool_calls and tokens.
     */
    public function chat(
        string $systemprompt,
        array $messages,
        array $tools,
        string $model,
        int $maxtokens
    ): array;
}
