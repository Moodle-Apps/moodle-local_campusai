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

namespace local_campusai;

/**
 * Input sanitisation and output escaping helpers.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class security {
    /**
     * Sanitises user input before it is sent to a provider or stored.
     *
     * @param string $input Raw user message.
     * @return string Cleaned message.
     */
    public static function sanitize_user_input(string $input): string {
        $input = trim($input);
        $input = strip_tags($input);
        return $input;
    }

    /**
     * Escapes assistant output before persistence and display.
     *
     * @param string $output Raw assistant message.
     * @return string Escaped message.
     */
    public static function sanitize_assistant_output(string $output): string {
        $output = trim($output);
        return htmlspecialchars($output, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
