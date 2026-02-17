<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_information_center\privacy;

use coding_exception;
use context;
use core\di;
use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadata_provider;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\plugin\provider as request_provider;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use dml_exception;
use moodle_database;

/**
 * Privacy Provider to overview, collect and delete user specific data
 *
 * @author      Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright   2025, oncampus GmbH, <support@oncampus.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements core_userlist_provider, metadata_provider, request_provider {
    /**
     * Gives an overview over all user data saved by the plugin
     *
     * @param collection $collection Collection to gather user data
     * @return collection Collection with gathered user data
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_information_center',
            [
                'userid' => 'privacy:metadata:local_information_center:userid',
                'messageid' => 'privacy:metadata:local_information_center:messageid',
            ],
            'privacy:metadata:local_information_center'
        );

        $collection->add_database_table(
            'local_information_center_messages',
            [
                'userid' => 'privacy:metadata:local_information_center_messages:useridfrom',
                'subject' => 'privacy:metadata:local_information_center_messages:subject',
                'fullmessage' => 'privacy:metadata:local_information_center_messages:fullmessage',
                'fullmessageformat' => 'privacy:metadata:local_information_center_messages:fullmessageformat',
                'smallmessage' => 'privacy:metadata:local_information_center_messages:smallmessage',
                'timestart' => 'privacy:metadata:local_information_center_messages:timestart',
                'timeend' => 'privacy:metadata:local_information_center_messages:timeend',
                'visibility' => 'privacy:metadata:local_information_center_messages:visibility',
                'categoryid' => 'privacy:metadata:local_information_center_messages:categoryid',
                'component' => 'privacy:metadata:local_information_center_messages:component',
                'timecreated' => 'privacy:metadata:local_information_center_messages:timecreated',
                'timemodified' => 'privacy:metadata:local_information_center_messages:timemodified',
                'timedeleted' => 'privacy:metadata:local_information_center_messages:timedeleted',
            ],
            'privacy:metadata:local_information_center_messages'
        );

        return $collection;
    }

    /**
     * Gives back the system context, cause the plugin only exists in system context
     *
     * @param int $userid User ID
     * @return contextlist List with system context
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $contextlist->add_system_context();
        return $contextlist;
    }

    /**
     * Collects all users which send or read messages
     *
     * @param userlist $userlist List with all users, which have user data in this plugin
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        $sql = "SELECT DISTINCT msg.useridfrom AS userid
                FROM {local_information_center_messages} msg";
        $userlist->add_from_sql('userid', $sql, []);

        $sql = "SELECT DISTINCT msgread.userid
                FROM {local_information_center} msgread";
        $userlist->add_from_sql('userid', $sql, []);
    }

    /**
     * Gather all user data about a user in a context
     *
     * @param approved_contextlist $contextlist User, Context to call the data for
     * @return void
     * @throws coding_exception Config cannot be loaded
     * @throws dml_exception Database not reachable
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        $userid = $contextlist->get_user()->id;
        $db = di::get(moodle_database::class);

        foreach ($contextlist->get_contexts() as $context) {
            $messages = $db->get_records('local_information_center_messages', ['useridfrom' => $userid]);
            if (!empty($messages)) {
                $privacyinfos = [];
                $privacyinfos['messages'] = $messages;
                writer::with_context($context)->export_data(['Infocenter/Own Messages'], (object) $privacyinfos);
            }

            $msgreadinfos = $db->get_records('local_information_center', ['userid' => $userid]);
            if (!empty($msgreadinfos)) {
                $privacyinfos = [];
                $privacyinfos['messages_read'] = $msgreadinfos;
                writer::with_context($context)->export_data(['Infocenter/Read Messages'], (object) $privacyinfos);
            }

            if (!empty($messages)) {
                $messageids = array_column($messages, 'id');
                [$messagesinsql, $messagesinparams] = $db->get_in_or_equal($messageids);
                $ownmessagesread = $db->get_records_select(
                    'local_information_center',
                    "messageid $messagesinsql",
                    $messagesinparams
                );

                if (!empty($ownmessagesread)) {
                    writer::with_context($context)->export_related_data(
                        ['Infocenter/Own Messages'],
                        'Read By',
                        (object) [
                            'infocenter_own_messages_read' => $ownmessagesread,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Delete data for every user in the context
     *
     * @param context $context Context, only deletes in system context
     * @return void
     * @throws dml_exception
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        $db = di::get(moodle_database::class);
        $db->delete_records('local_information_center_messages');
        $db->delete_records('local_information_center');
    }

    /**
     * Deletes data for given users
     *
     * @param approved_userlist $userlist List of users to delete data for
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        $db = di::get(moodle_database::class);
        [$userinsql, $userinparams] = $db->get_in_or_equal($userlist->get_userids());

        $ownmessages = $db->get_fieldset_select(
            'local_information_center_messages',
            'id',
            "useridfrom $userinsql",
            $userinparams
        );
        [$messageinsql, $messageinparams] = $db->get_in_or_equal($ownmessages);

        $db->delete_records_select(
            'local_information_center',
            "userid $userinsql OR messageid $messageinsql",
            array_merge($userinparams, $messageinparams)
        );
        $db->delete_records_select(
            'local_information_center_messages',
            "useridfrom $userinsql",
            $userinparams
        );
    }

    /**
     * Delete data for a single user
     *
     * @param approved_contextlist $contextlist Context and user
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        if (empty($contextlist->count())) {
            return;
        }

        $db = di::get(moodle_database::class);
        $userid = $contextlist->get_user()->id;

        $ownmessages = $db->get_fieldset('local_information_center_messages', 'id', ['useridfrom' => $userid]);
        [$messageinsql, $messageinparams] = $db->get_in_or_equal($ownmessages, onemptyitems: true);

        $db->delete_records_select(
            'local_information_center',
            "userid = ? OR messageid $messageinsql",
            array_merge([$userid], $messageinparams)
        );

        $db->delete_records('local_information_center_messages', ['useridfrom' => $userid]);
    }
}
