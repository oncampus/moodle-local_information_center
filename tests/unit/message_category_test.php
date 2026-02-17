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

namespace local_information_center\message_handle;

use advanced_testcase;
use coding_exception;
use core\di;
use dml_exception;
use local_information_center\message_handle\contracts\i_message_category;

/**
 * Test the message category class
 *
 * @covers local_information_center\message_handle\contracts\i_message_category
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2025, oncampus GmbH, <support@oncampus.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class message_category_test extends advanced_testcase {
    /**
     * Test if all message categories exist
     *
     * @covers ::get_all
     * @return void
     * @throws coding_exception Cache cannot be loaded
     * @throws dml_exception Database connection failed
     */
    public function test_get_all(): void {
        $messagecategory = di::get(i_message_category::class);

        $categories = $messagecategory->get_all();

        $this->assertCount(5, $categories);
    }
}
