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

namespace local_information_center\message_handle;

require_once(__DIR__ . '/generator.php');
require_once(__DIR__ . '/../../../../lib/testing/classes/frozen_clock.php');

use advanced_testcase;
use core\clock;
use core\di;
use frozen_clock;
use local_information_center\message_handle\contracts\i_message_manager;
use local_information_center\message_handle\contracts\message;
use local_information_center\tasks\message_cleanup;
use tool_oc_remote_notification\generator;

final class message_manager_test extends advanced_testcase {
    private i_message_manager $manager;

    public function test_create_and_get_message(): void {
        $exspectedmessage = generator::generate_message();

        $id = $this->manager->add_or_update($exspectedmessage);
        $savedmessage = $this->manager->get($id);

        $savedmessage->id = null;
        $this->assertEquals($exspectedmessage, $savedmessage);
    }

    public function test_create_and_get_all_messages(): void {
        $exspectedmessage = generator::generate_message();

        $this->manager->add_or_update($exspectedmessage);
        $messages = $this->manager->get_all();

        $this->assertCount(1, $messages);
        $firstmessage = message::from_stdClass(reset($messages));
        $firstmessage->id = null;
        $this->assertEquals($exspectedmessage, $firstmessage);
    }

    public function test_update_and_get_message(): void {
        $exspectedmessage = generator::generate_message();
        $id = $this->manager->add_or_update($exspectedmessage);
        $exspectedmessage->id = $id;
        $exspectedmessage->subject = 'New Subject';

        $this->manager->add_or_update($exspectedmessage);
        $savedmessage = $this->manager->get($id);

        $this->assertEquals($exspectedmessage, $savedmessage);
    }

    public function test_delete_message(): void {
        $exspectedmessage = generator::generate_message();
        $id = $this->manager->add_or_update($exspectedmessage);

        $this->manager->delete($id);
        $messages = $this->manager->get_all();

        $this->assertCount(0, $messages);
    }

    public function test_delete_message_cron(): void {
        $exspectedmessage = generator::generate_message();
        $id = $this->manager->add_or_update($exspectedmessage);
        di::set(clock::class, new frozen_clock(1));

        $this->manager->delete($id);
        di::set(clock::class, new frozen_clock(31 * 24 * 3600));
        (new message_cleanup())->execute();

        $message = $this->manager->get($id);
        $this->assertFalse($message);
    }

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->manager = di::get(i_message_manager::class);
    }
}
