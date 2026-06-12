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

namespace local_information_center\output;

/**
 * Filterable Table, multiple filters can be applied
 *
 * @author      Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright   2025, oncampus GmbH, <support@oncampus.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface FilterableTable {
    /**
     * Adds an additional filter
     *
     * @param string $field Query language field to filter by
     * @param string $comparator One comparator of 'contains', <, >, 'eq' or 'isset'
     * @param mixed $value Value to compare with (null if not needed)
     */
    public function add_filter(
        string $field,
        string $comparator = "eq",
        mixed $value = null
    ): void;
}
