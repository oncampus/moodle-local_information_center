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

namespace local_information_center\notification\contracts;

use stdClass;

/**
 * Data object for notifications
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification {
    /** @var int|null Notification ID */
    public ?int $id = null;
    /** @var int|null Notification author */
    public ?int $useridfrom = null;
    /** @var string|null Title of the notification */
    public ?string $subject = null;
    /** @var string|null Body of the notification */
    public ?string $fullmessage = null;
    /** @var int|null Format, like html */
    public ?int $fullmessageformat = null;
    /** @var string|null Short form of the body */
    public ?string $smallmessage = null;
    /** @var int|null Start time, when it is visible */
    public ?int $timestart = null;
    /** @var int|null End time, when it gets hidden */
    public ?int $timeend = null;
    /** @var int|null Time when the notification was deleted */
    public ?int $timedeleted = null;
    /** @var string|null Visibility, e.g., visible to teachers, admins… */
    public ?string $visibility = null;
    /** @var string|null Plugin that created this notification */
    public ?string $component = null;
    /** @var int|null Category ID */
    public ?int $categoryid = null;

    /**
     * Converts a stdClass object to this class
     *
     * @param stdClass $data A stdClass object to convert into this data object
     */
    public static function from_stdclass(stdClass $data): notification {
        $message = new notification();

        foreach ($data as $key => $value) {
            if (property_exists($message, $key)) {
                $message->$key = $value;
            }
        }

        return $message;
    }
}
