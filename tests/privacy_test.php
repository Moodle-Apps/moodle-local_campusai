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
use local_campusai\privacy\provider;

/**
 * Tests for the privacy provider.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_campusai\privacy\provider
 */
final class privacy_test extends advanced_testcase {
    /**
     * Tests that metadata describes both plugin tables.
     *
     * @return void
     */
    public function test_metadata_includes_tables(): void {
        $collection = new \core_privacy\local\metadata\collection('local_campusai');
        $collection = provider::get_metadata($collection);

        $items = $collection->get_collection();
        $this->assertCount(2, $items);

        $tables = array_map(function ($item) {
            return $item->get_name();
        }, $items);

        $this->assertContains('local_campusai_conversation', $tables);
        $this->assertContains('local_campusai_ratelimit', $tables);
    }

    /**
     * Tests that deleting user data removes conversation and rate limit rows.
     *
     * @return void
     */
    public function test_delete_user_data(): void {
        global $DB;

        $this->resetAfterTest(true);
        $user = $this->getDataGenerator()->create_user();

        $DB->insert_record('local_campusai_conversation', [
            'userid'           => $user->id,
            'usermessage'      => 'Hello',
            'assistantmessage' => 'Hi',
            'provider'         => 'proxy',
            'tokensused'       => 10,
            'timecreated'      => time(),
        ]);
        $DB->insert_record('local_campusai_ratelimit', [
            'userid'        => $user->id,
            'windowstart'   => time(),
            'messagecount'  => 1,
        ]);

        $contextlist = provider::get_contexts_for_userid($user->id);
        $approved = new \core_privacy\local\request\approved_contextlist($user, 'local_campusai', $contextlist->get_contextids());
        provider::delete_data_for_user($approved);

        $this->assertFalse($DB->record_exists('local_campusai_conversation', ['userid' => $user->id]));
        $this->assertFalse($DB->record_exists('local_campusai_ratelimit', ['userid' => $user->id]));
    }
}
