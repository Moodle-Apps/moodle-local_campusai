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



namespace local_campusai\provider; class factory { public static function create(): provider_interface { $provider = get_config('local_campusai', 'provider'); $apikey = get_config('local_campusai', 'apikey'); $model = get_config('local_campusai', 'model'); if (debugging('', DEBUG_DEVELOPER)) { debugging('campusai provider=' . $provider . ' model=' . $model, DEBUG_DEVELOPER); } if ($provider === 'proxy') { $licensekey = trim((string) get_config('local_campusai', 'licensekey')); if (empty($licensekey)) { throw new \moodle_exception('error_noapikey', 'local_campusai'); } return new proxy_provider($licensekey); } if (empty($apikey)) { throw new \moodle_exception('error_noapikey', 'local_campusai'); } switch ($provider) { case 'openai': return new openai_provider($apikey, $model); case 'gemini': return new gemini_provider($apikey, $model); case 'claude': return new claude_provider($apikey, $model); case 'deepseek': return new deepseek_provider($apikey, $model); case 'proxy': $licensekey = trim((string) get_config('local_campusai', 'licensekey')); return new proxy_provider($licensekey); default: throw new \moodle_exception('error_provider', 'local_campusai', '', null, 'Unknown: ' . $provider); } } public static function get_providers(): array { return [ 'proxy' => 'Free (Managed by Campus Assistant)', 'openai' => 'OpenAI (your API key)', 'gemini' => 'Google Gemini (your API key)', 'claude' => 'Anthropic Claude (your API key)', 'deepseek' => 'DeepSeek (your API key)', ]; } public static function get_recommended_models(): array { return [ 'openai' => ['gpt-4o-mini', 'gpt-4o'], 'gemini' => ['gemini-1.5-flash', 'gemini-1.5-pro'], 'claude' => ['claude-3-5-haiku-20241022', 'claude-3-5-sonnet-20241022'], 'deepseek' => ['deepseek-chat', 'deepseek-reasoner'], ]; } } 
