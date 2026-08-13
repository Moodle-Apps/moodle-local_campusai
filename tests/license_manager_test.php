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

use advanced_testcase;

/**
 * Tests for the license manager.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_campusai\license_manager
 */
final class license_manager_test extends advanced_testcase {
    /**
     * Tests that a non-proxy provider is always treated as active.
     *
     * @return void
     */
    public function test_non_proxy_provider_is_active(): void {
        $this->resetAfterTest(true);
        set_config('provider', 'openai', 'local_campusai');
        set_config('licensekey', '', 'local_campusai');

        $this->assertEquals(license_manager::STATUS_ACTIVE, license_manager::get_status());
        $this->assertTrue(license_manager::is_proxy_licensed());
    }

    /**
     * Tests that the proxy provider requires a license key.
     *
     * @return void
     */
    public function test_proxy_requires_license_key(): void {
        $this->resetAfterTest(true);
        set_config('provider', 'proxy', 'local_campusai');
        set_config('licensekey', '', 'local_campusai');

        $this->assertEquals(license_manager::STATUS_FREE, license_manager::get_status());
        $this->assertFalse(license_manager::is_proxy_licensed());
    }

    /**
     * Tests that a proxy provider with a license key is active.
     *
     * @return void
     */
    public function test_proxy_with_license_is_active(): void {
        $this->resetAfterTest(true);
        set_config('provider', 'proxy', 'local_campusai');
        set_config('licensekey', 'valid-license-key', 'local_campusai');

        $this->assertEquals(license_manager::STATUS_ACTIVE, license_manager::get_status());
        $this->assertTrue(license_manager::is_proxy_licensed());
    }
}
