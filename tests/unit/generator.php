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

namespace tool_oc_remote_notification;

use core\di;
use dml_exception;
use local_information_center\message_handle\contracts\i_message_manager;
use local_information_center\message_handle\contracts\message;
use local_information_center\message_handle\contracts\visibility;

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
     * @return message Message data object
     * @throws dml_exception Database connection failed
     */
    public static function create_message(): message {
        $message = self::generate_message();
        $manager = di::get(i_message_manager::class);
        $message->id = $manager->add_or_update($message);
        return $message;
    }

    /**
     * Generates a message data object and returns it
     *
     * @return message Message data object
     */
    public static function generate_message(): message {
        $message = new message();
        $message->component = 'message_manager';
        $message->categoryid = 1;
        $message->visibility = visibility::VISIBILITY_STUDENT->value;
        $message->subject = 'test';
        $message->fullmessage = '<p>test</p>';
        $message->fullmessageformat = FORMAT_HTML;
        $message->smallmessage = '';
        $message->timestart = 1761126208;
        $message->timeend = 1761136208;
        $message->useridfrom = 1;
        return $message;
    }
}
