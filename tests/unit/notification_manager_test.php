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

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/generator.php');
require_once(__DIR__ . '/../../../../lib/testing/classes/frozen_clock.php');

use core\clock;
use core\di;
use local_information_center\notification\contracts\NotificationManager;
use local_information_center\notification\contracts\notification;
use local_information_center\tasks\notification_cleanup;

/**
 * Tests if the notification manager is working correctly
 *
 * @covers \local_information_center\tasks\notification_cleanup
 * @covers \local_information_center\notification\contracts\NotificationManager
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2025, oncampus GmbH, <support@oncampus.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class notification_manager_test extends advanced_testcase {
    /** @var NotificationManager Object to test */
    private NotificationManager $manager;

    /**
     * Test if a notification can be saved and fetched again by get
     *
     * @covers ::get
     * @return void
     * @throws dml_exception Database connection error
     */
    public function test_create_and_get_notification(): void {
        $exspectednotification = generator::generate_notification();

        $id = $this->manager->add_or_update($exspectednotification);
        $savednotification = $this->manager->get($id);

        $savednotification->id = null;
        $this->assertEquals($exspectednotification, $savednotification);
    }

    /**
     * Test if a notification can be saved and fetched again by get_all
     *
     * @covers ::get_all
     * @return void
     * @throws dml_exception Database connection error
     */
    public function test_create_and_get_all_notification(): void {
        $exspectednotification = generator::generate_notification();

        $this->manager->add_or_update($exspectednotification);
        $notifications = $this->manager->get_all();

        $this->assertCount(1, $notifications);
        $firstnotification = notification::from_stdClass(reset($notifications));
        $firstnotification->id = null;
        $this->assertEquals($exspectednotification, $firstnotification);
    }

    /**
     * Tests if a notification can be updated
     *
     * @covers ::get
     * @return void
     * @throws dml_exception Database connection error
     */
    public function test_update_and_get_notification(): void {
        $exspectednotification = generator::generate_notification();
        $id = $this->manager->add_or_update($exspectednotification);
        $exspectednotification->id = $id;
        $exspectednotification->subject = 'New Subject';

        $this->manager->add_or_update($exspectednotification);
        $savednotification = $this->manager->get($id);

        $this->assertEquals($exspectednotification, $savednotification);
    }

    /**
     * Tests if a notification can be deleted
     *
     * @covers ::delete
     * @return void
     * @throws dml_exception Database connection error
     */
    public function test_delete_notification(): void {
        $exspectednotification = generator::generate_notification();
        $id = $this->manager->add_or_update($exspectednotification);

        $this->manager->delete($id);
        $notifications = $this->manager->get_all();

        $this->assertCount(0, $notifications);
    }

    /**
     * Tests if old deleted notifications will be cleaned up by cron
     *
     * @covers ::execute
     * @return void
     * @throws dml_exception Database connection error
     */
    public function test_delete_notification_cron(): void {
        $exspectednotification = generator::generate_notification();
        $id = $this->manager->add_or_update($exspectednotification);
        di::set(clock::class, new frozen_clock(1));

        $this->manager->delete($id);
        di::set(clock::class, new frozen_clock(31 * 24 * 3600));
        (new notification_cleanup())->execute();

        $notification = $this->manager->get($id);
        $this->assertFalse($notification);
    }

    /**
     * Setup for this class
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->manager = di::get(NotificationManager::class);
    }
}
