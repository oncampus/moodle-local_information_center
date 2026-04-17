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

use core\di;
use dml_exception;
use local_information_center\notification\contracts\NotificationManager;
use local_information_center\notification\contracts\notification;
use local_information_center\notification\contracts\visibility;

/**
 * Generates plugin-specific data
 *
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2025, oncampus GmbH, <support@oncampus.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class generator {
    /**
     * Creates a message in the database
     *
     * @return notification Message data object
     * @throws dml_exception Database connection failed
     */
    public static function create_notification(): notification {
        $manager = di::get(NotificationManager::class);
        $notification = self::generate_notification();
        $manager->add_or_update($notification);
        return $notification;
    }

    /**
     * Generates a message data object and returns it
     *
     * @return notification Message data object
     */
    public static function generate_notification(): notification {
        $notification = notification::create(
            'test',
            '<p>test</p>',
            FORMAT_HTML,
            '',
            visibility::VISIBILITY_STUDENT->value,
            1,
            1761126208,
            1761136208
        );
        $notification->useridfrom = 2;
        return $notification;
    }
}
