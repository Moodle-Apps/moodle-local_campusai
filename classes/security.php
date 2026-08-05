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



 namespace local_campusai; defined('MOODLE_INTERNAL') || die(); class security { const INJECTION_PATTERNS = [ '/ignore\s+(all\s+)?(previous|prior|above)\s+(instructions|prompts|rules)/i', '/you\s+are\s+(now|actually)\s+(a|an)\s+/i', '/disregard\s+(all\s+)?(previous|prior)\s+/i', '/forget\s+(everything|all\s+(previous|prior))\s+/i', '/system\s*[:>]\s*/i', '/\<\/?system\>/i', '/reveal\s+(your|the)\s+(system|initial|original)\s+(prompt|instructions?)/i', '/act\s+as\s+(if|though)\s+you\s+(are|were)\s+/i', '/pretend\s+(you\s+are|to\s+be)\s+(a|an)?\s*(different|jailbroken|unrestricted)/i', '/jailbreak/i', '/DAN\s*(mode|prompt)/i', ]; public static function is_safe_input(string $input): bool { if (strlen($input) > 2000) { return false; } foreach (self::INJECTION_PATTERNS as $pattern) { if (preg_match($pattern, $input)) { return false; } } return true; } public static function sanitise_output(string $text): string { $text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'); $text = nl2br($text); return $text; } public static function truncate(string $text, int $maxlength = 5000): string { if (strlen($text) > $maxlength) { $text = substr($text, 0, $maxlength) . '...'; } return $text; } } 
