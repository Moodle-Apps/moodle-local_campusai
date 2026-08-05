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



 namespace local_campusai\privacy; use core_privacy\local\metadata\collection; use core_privacy\local\request\approved_contextlist; use core_privacy\local\request\approved_userlist; use core_privacy\local\request\contextlist; use core_privacy\local\request\helper; use core_privacy\local\request\transform; use core_privacy\local\request\writer; defined('MOODLE_INTERNAL') || die(); class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\core_userlist_provider, \core_privacy\local\request\plugin\provider { public static function get_metadata(collection $collection): collection { $collection->add_database_table('local_campusai_conversation', [ 'userid' => 'privacy:metadata:conversation:userid', 'usermessage' => 'privacy:metadata:conversation:usermessage', 'assistantmessage' => 'privacy:metadata:conversation:assistantmessage', 'timecreated' => 'privacy:metadata:conversation:timecreated', ], 'privacy:metadata:conversation'); return $collection; } public static function get_contexts_for_userid(int $userid): contextlist { $contextlist = new contextlist(); $contextlist->add_system_context(); return $contextlist; } public static function get_users_in_context(\core_privacy\local\request\userlist $userlist) { if (!$userlist->get_context() instanceof \context_system) { return; } global $DB; $sql = "SELECT DISTINCT userid FROM {local_campusai_conversation}"; $userids = $DB->get_fieldset_sql($sql); $userlist->add_users($userids); } public static function export_user_data(approved_contextlist $contextlist) { global $DB; $userid = $contextlist->get_user()->id; $records = $DB->get_records('local_campusai_conversation', ['userid' => $userid], 'timecreated ASC'); foreach ($records as $record) { $data = (object) [ 'user_message' => $record->usermessage, 'assistant_message' => $record->assistantmessage, 'functions_called' => $record->functionscalled, 'provider' => $record->provider, 'tokens_used' => $record->tokensused, 'time' => transform::datetime($record->timecreated), ]; writer::with_context(\context_system::instance()) ->export_data(['Campus Assistant [' . $record->id . ']'], $data); } } public static function delete_data_for_all_users_in_context(\context $context) { if ($context instanceof \context_system) { global $DB; $DB->delete_records('local_campusai_conversation', []); } } public static function delete_data_for_user(approved_contextlist $contextlist) { global $DB; $userid = $contextlist->get_user()->id; $DB->delete_records('local_campusai_conversation', ['userid' => $userid]); } public static function delete_data_for_users(approved_userlist $userlist) { global $DB; $userids = $userlist->get_userids(); list($insql, $inparams) = $DB->get_in_or_equal($userids); $DB->delete_records_select('local_campusai_conversation', "userid $insql", $inparams); } } 
