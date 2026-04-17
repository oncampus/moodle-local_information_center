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

use core\clock;
use core\di;
use core\uuid;
use invalid_parameter_exception;

/**
 * Data object for notifications
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification {
    public function __construct(
        /** @var string Notification UUID */
        public string $uuid,
        /** @var int Notification author */
        public int $useridfrom,
        /** @var string Title of the notification */
        public string $subject,
        /** @var string Body of the notification */
        public string $fullmessage,
        /** @var int Format, like html */
        public int $fullmessageformat,
        /** @var string Short form of the body */
        public string $smallmessage,
        /** @var visibility Visibility, e.g., visible to teachers, admins… */
        public visibility $visibility,
        /** @var string|null Plugin that created this notification */
        public string $component,
        /** @var int Category ID */
        public int $categoryid,
        /** @var int|null Time, when the notification got modified last time */
        public int $timemodified,
        /** @var int|null Start time, when it is visible */
        public ?int $timestart = null,
        /** @var int|null End time, when it gets hidden */
        public ?int $timeend = null,
        /** @var int|null Time when the notification was deleted */
        public ?int $timedeleted = null,
    ) {
    }

    public function get_message_body(): string {
        return message_format_message_text((object) [
            'fullmessageformat' => $this->fullmessageformat,
            'smallmessage' => $this->smallmessage,
            'fullmessage' => $this->fullmessage,
            'fullmessagehtml' => null,
        ]);
    }

    public function get_time_visible(): int {
        return max($this->timestart, $this->timemodified);
    }

    public static function create(
        string $subject,
        string $fullmessage,
        int $fullmessageformat,
        string $smallmessage,
        string $visibility,
        int $categoryid,
        ?int $timestart = null,
        ?int $timeend = null,
        string $component = 'local_information_center',
        ?string $uuid = null,
    ): self {
        global $USER;

        if (!$visibilityparsed = visibility::tryFrom($visibility)) {
            throw new invalid_parameter_exception(get_string(
                'validation:visibility:invalid',
                'local_information_center',
                $visibility
            ));
        }

        if ($timestart && $timestart < 0) {
            throw new invalid_parameter_exception(get_string(
                'validation:timestart:notnegative',
                'local_information_center',
            ));
        }

        if ($timeend && $timeend < 0) {
            throw new invalid_parameter_exception(get_string(
                'validation:timeend:notnegative',
                'local_information_center',
            ));
        }

        if ($timestart && $timeend && $timestart > $timeend) {
            throw new invalid_parameter_exception(get_string(
                'validation:timeend:aftertimestart',
                'local_information_center',
            ));
        }

        return new self(
            $uuid ?? uuid::generate(),
            $USER->id,
            $subject,
            $fullmessage,
            $fullmessageformat,
            $smallmessage,
            $visibilityparsed,
            $component,
            $categoryid,
            di::get(clock::class)->now(),
            $timestart,
            $timeend,
            null,
        );
    }
}
