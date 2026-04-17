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

use core\exception\coding_exception;
use mod_booking\booking_rules\conditions\select_booking_manager;
use stdClass;

readonly class notification_category {
    /**
     * Constructor
     *
     * @param int $id
     * @param string $name
     * @param string $color
     * @param string $icon
     */
    public function __construct(
        /** @var int Sequence-ID of the category */
        public int $id,
        /** @var string Unique shortname of category */
        public string $name,
        /** @var string Color, like #234212 */
        public string $color,
        /** @var string Font awesome icon, like fa-gear */
        public string $icon,
    ) {
    }

    /**
     * Get the category label
     *
     * @return string category label
     * @throws coding_exception
     */
    public function get_label(): string {
        return get_string(
            'category:' . $this->name,
            'local_information_center'
        );
    }

    /**
     * Get the icon
     *
     * @return string Font awesome icon
     */
    public function get_icon(): string {
        return "fa-solid $this->icon";
    }
}
