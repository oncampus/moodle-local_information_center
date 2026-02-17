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
    /** @var int|null Message ID */
    public ?int $id = null;
    /** @var int|null Message author */
    public ?int $useridfrom = null;
    /** @var string|null Title of the message */
    public ?string $subject = null;
    /** @var string|null Body of the message */
    public ?string $fullmessage = null;
    /** @var int|null Format, like html */
    public ?int $fullmessageformat = null;
    /** @var string|null Short form of the body */
    public ?string $smallmessage = null;
    /** @var int|null Start time, when its visible */
    public ?int $timestart = null;
    /** @var int|null End time, when its gets hidden */
    public ?int $timeend = null;
    /** @var int|null Time, where the message got deleted */
    public ?int $timedeleted = null;
    /** @var string|null Visibility, like visible for teachers, admins… */
    public ?string $visibility = null;
    /** @var string|null Creating plugin of this message */
    public ?string $component = null;
    /** @var int|null Category ID */
    public ?int $categoryid = null;

    /**
     * Converts stdClass data to this class
     *
     * @param stdClass $data Converts a stdClass into this data object
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
