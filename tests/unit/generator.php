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
use local_information_center\message_handle\contracts\i_message_manager;
use local_information_center\message_handle\contracts\message;
use local_information_center\message_handle\contracts\visibility;

class generator {
    public static function create_message(): message {
        $message = self::generate_message();
        $manager = di::get(i_message_manager::class);
        $message->id = $manager->add_or_update($message);
        return $message;
    }

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