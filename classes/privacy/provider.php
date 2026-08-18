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

namespace local_campusai\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for Campus Assistant.
 *
 * @package    local_campusai
 * @copyright  2026 Moodle-Apps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\request\plugin\provider {
    /**
     * Returns metadata about the data stored by this plugin.
     *
     * @param collection $collection The initialised collection.
     * @return collection The updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_campusai_conversation',
            [
                'userid' => 'privacy:metadata:conversation:userid',
                'usermessage' => 'privacy:metadata:conversation:usermessage',
                'assistantmessage' => 'privacy:metadata:conversation:assistantmessage',
                'provider' => 'privacy:metadata:conversation:provider',
                'tokensused' => 'privacy:metadata:conversation:tokensused',
                'timecreated' => 'privacy:metadata:conversation:timecreated',
            ],
            'privacy:metadata:conversation'
        );

        $collection->add_database_table(
            'local_campusai_ratelimit',
            [
                'userid' => 'privacy:metadata:ratelimit:userid',
                'windowstart' => 'privacy:metadata:ratelimit:windowstart',
                'messagecount' => 'privacy:metadata:ratelimit:messagecount',
            ],
            'privacy:metadata:ratelimit'
        );

        // External AI providers: chat messages, recent conversation history and the results of
        // Moodle data lookups performed by the assistant are transmitted to the configured
        // provider in order to generate a reply.
        $aifields = [
            'messages' => 'privacy:metadata:ai:messages',
            'history' => 'privacy:metadata:ai:history',
            'toolresults' => 'privacy:metadata:ai:toolresults',
        ];

        $collection->add_external_location_link(
            'proxy',
            $aifields + ['userid' => 'privacy:metadata:proxy:userid'],
            'privacy:metadata:proxy'
        );
        $collection->add_external_location_link('openai', $aifields, 'privacy:metadata:openai');
        $collection->add_external_location_link('claude', $aifields, 'privacy:metadata:claude');
        $collection->add_external_location_link('gemini', $aifields, 'privacy:metadata:gemini');
        $collection->add_external_location_link('deepseek', $aifields, 'privacy:metadata:deepseek');

        return $collection;
    }

    /**
     * Returns the system context for every user that has used the assistant.
     *
     * @param int $userid User ID.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        if (self::user_has_data($userid)) {
            $contextlist->add_system_context();
        }

        return $contextlist;
    }

    /**
     * Exports user data to the privacy API writer.
     *
     * @param approved_contextlist $contextlist The approved contexts.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $user = $contextlist->get_user();
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_SYSTEM) {
                continue;
            }

            if (!has_capability('local/campusai:use', $context, $user->id)) {
                continue;
            }

            $conversations = $DB->get_records('local_campusai_conversation', ['userid' => $user->id], 'timecreated ASC');
            foreach ($conversations as $record) {
                $data = (array) $record;
                unset($data['id']);
                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'local_campusai'), 'conversation', $record->id],
                    (object) $data
                );
            }

            $ratelimits = $DB->get_records('local_campusai_ratelimit', ['userid' => $user->id], 'windowstart ASC');
            foreach ($ratelimits as $record) {
                $data = (array) $record;
                unset($data['id']);
                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'local_campusai'), 'ratelimit', $record->id],
                    (object) $data
                );
            }
        }
    }

    /**
     * Deletes all data for the user in the approved contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $user = $contextlist->get_user();
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_SYSTEM) {
                continue;
            }

            $DB->delete_records('local_campusai_conversation', ['userid' => $user->id]);
            $DB->delete_records('local_campusai_ratelimit', ['userid' => $user->id]);
        }
    }

    /**
     * Deletes all data in the supplied context.
     *
     * @param \context $context The context to delete from.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        $DB->delete_records('local_campusai_conversation', []);
        $DB->delete_records('local_campusai_ratelimit', []);
    }

    /**
     * Returns the list of users who have data in the given context.
     *
     * @param \context $context The context.
     * @return userlist
     */
    public static function get_users_in_context(\context $context): userlist {
        global $DB;

        $userlist = new userlist($context, 'local_campusai');

        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return $userlist;
        }

        $sql = "SELECT DISTINCT userid FROM {local_campusai_conversation}
                UNION
                SELECT DISTINCT userid FROM {local_campusai_ratelimit}";
        $userids = $DB->get_fieldset_sql($sql);
        $userlist->add_users($userids);

        return $userlist;
    }

    /**
     * Checks whether the user has any stored data.
     *
     * @param int $userid User ID.
     * @return bool
     */
    private static function user_has_data(int $userid): bool {
        global $DB;

        return $DB->record_exists('local_campusai_conversation', ['userid' => $userid])
            || $DB->record_exists('local_campusai_ratelimit', ['userid' => $userid]);
    }
}
