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

namespace local_information_center;

use coding_exception;
use context_system;
use core\di;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;
use dml_exception;
use local_information_center\privacy\provider;
use moodle_database;
use stdClass;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/generator.php');

/**
 * Tests if the privacy provider is working correctly
 *
 * @covers \local_information_center\privacy\provider
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2025, oncampus GmbH, <support@oncampus.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider_test extends provider_testcase {
    /**
     * Setup for this test
     *
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Check if the metadata overview is returned correctly
     *
     * @covers ::get_metadata
     * @return void
     */
    public function test_get_metadata(): void {
        $collection = new collection('local_information_center');
        $collection = provider::get_metadata($collection);
        $items = $collection->get_collection();

        $this->assertCount(2, $items);
    }

    /**
     * Test if the exported user data is not empty, when user data exists
     *
     * @covers ::export_user_data
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_export_user_data(): void {
        $db = di::get(moodle_database::class);
        $context = context_system::instance();
        $user = $this->getDataGenerator()->create_user();
        $notification = generator::create_notification($user->id);
        $db->insert_record('local_information_center', (object)[
            'userid' => $user->id,
            'messageuuid' => $notification->uuid,
        ]);

        $user = $db->get_record('user', ['id' => $user->id]);
        $approvedcontextlist = new approved_contextlist($user, 'local_information_center', [$context->id]);
        provider::export_user_data($approvedcontextlist);

        $exportread = writer::with_context($context)->get_data(['Infocenter/Read Messages']);
        $this->assertNotEmpty($exportread);
        $this->assertNotEmpty($exportread->messages_read);

        $exportwrote = writer::with_context($context)->get_data(['Infocenter/Own Messages']);
        $this->assertNotEmpty($exportread);
        $this->assertNotEmpty($exportwrote->messages);
    }

    /**
     * Test if user data gets deleted properly
     *
     * @covers ::delete_data_for_user
     * @return void
     * @throws dml_exception
     */
    public function test_delete_data_for_user(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $context = context_system::instance();

        // Insert dummy data.
        $notification = generator::create_notification($user->id);
        $read = (object)[
            'userid' => $user->id,
            'messageuuid' => $notification->uuid,
        ];
        $DB->insert_record('local_information_center', $read);

        $contextlist = new approved_contextlist($user, 'local_information_center', [$context->id]);
        provider::delete_data_for_user($contextlist);

        $this->assertEmpty($DB->get_records('local_information_center', ['userid' => $user->id]));
        $this->assertEmpty($DB->get_records('local_information_center_messages', ['useridfrom' => $user->id]));
    }

    /**
     * Tests if users with user data are returned by the provider
     *
     * @covers ::get_users_in_context
     * @return void
     * @throws dml_exception
     */
    public function test_get_users_in_context(): void {
        global $DB;

        $context = context_system::instance();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        // Add user1 as sender.
        $notification = generator::create_notification($user1->id);

        // Add user2 as reader.
        $DB->insert_record('local_information_center', (object)[
            'userid' => $user2->id,
            'messageid' => $notification->uuid,
        ]);

        $userlist = new userlist($context, 'local_information_center');
        provider::get_users_in_context($userlist);
        $userids = $userlist->get_userids();

        $this->assertContains((int)$user1->id, $userids);
        $this->assertContains((int)$user2->id, $userids);
    }

    /**
     * Tests if user data is deleted properly.
     *
     * @covers ::delete_data_for_all_users_in_context
     * @return void
     * @throws dml_exception
     */
    public function test_delete_data_for_all_users_in_context(): void {
        global $DB;

        $context = context_system::instance();
        $user = $this->getDataGenerator()->create_user();

        $message = generator::create_notification($user->id);
        $DB->insert_record('local_information_center', (object)[
            'userid' => $user->id,
            'messageid' => $message->uuid,
        ]);

        provider::delete_data_for_all_users_in_context($context);

        $this->assertEquals(0, $DB->count_records('local_information_center'));
        $this->assertEquals(0, $DB->count_records('local_information_center_messages'));
    }
}
