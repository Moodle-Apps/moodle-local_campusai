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
 * License state manager for the managed proxy.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class license_manager {
    /** @var string Free tier state. */
    public const STATUS_FREE = 'free';

    /** @var string Active licensed state. */
    public const STATUS_ACTIVE = 'active';

    /** @var string Grace period state. */
    public const STATUS_GRACE = 'grace';

    /**
     * Returns the current license status.
     *
     * A non-empty license key is treated as active. An empty key with the proxy
     * provider selected falls back to free/grace depending on configuration.
     *
     * @return string One of the STATUS_* constants.
     */
    public static function get_status(): string {
        $provider = get_config('local_campusai', 'provider');
        $licensekey = get_config('local_campusai', 'licensekey');

        if ($provider !== 'proxy') {
            return self::STATUS_ACTIVE;
        }

        if (!empty($licensekey)) {
            return self::STATUS_ACTIVE;
        }

        // No license configured: the proxy cannot be used.
        return self::STATUS_FREE;
    }

    /**
     * Checks whether the proxy provider is licensed.
     *
     * @return bool
     */
    public static function is_proxy_licensed(): bool {
        return self::get_status() === self::STATUS_ACTIVE || self::get_status() === self::STATUS_GRACE;
    }
}
