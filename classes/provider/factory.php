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
 * Factory for AI provider instances.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class factory {
    /**
     * Creates a provider instance by name.
     *
     * @param string $name Provider name.
     * @param string $apikey API key or license key.
     * @param string $jwtsecret JWT shared secret (used by proxy).
     * @return provider_interface
     */
    public static function create(string $name, string $apikey, string $jwtsecret): provider_interface {
        return match ($name) {
            'openai'   => new openai_provider($apikey, $jwtsecret),
            'gemini'   => new gemini_provider($apikey, $jwtsecret),
            'claude'   => new claude_provider($apikey, $jwtsecret),
            'deepseek' => new deepseek_provider($apikey, $jwtsecret),
            'proxy'    => new proxy_provider($apikey, $jwtsecret),
            default    => throw new \moodle_exception('error_provider', 'local_campusai'),
        };
    }
}
