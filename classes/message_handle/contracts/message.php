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

namespace local_information_center\message_handle\contracts;

use stdClass;

/**
 * Dataobject for messages
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class message {
    public ?int $id = null;
    public ?int $useridfrom = null;
    public ?string $subject = null;
    public ?string $fullmessage = null;
    public ?int $fullmessageformat = null;
    public ?string $smallmessage = null;
    public ?int $timestart = null;
    public ?int $timeend = null;
    public ?int $timedeleted = null;
    public ?string $visibility = null;
    public ?string $component = null;
    public ?int $categoryid = null;

    /**
     * Converts stdClass data to this class
     */
    public static function from_stdclass(stdClass $data): message {
        $message = new message();

        foreach ($data as $key => $value) {
            if (property_exists($message, $key)) {
                $message->$key = $value;
            }
        }

        return $message;
    }
}
