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

namespace local_information_center\notification;

use coding_exception;
use dml_exception;
use local_information_center\notification\contracts\NotificationCategory;
use moodle_database;
use stdClass;

/**
 * Manages message categories
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification_category implements NotificationCategory {
    /** @var moodle_database Moodle Database */
    private readonly moodle_database $db;

    /**
     * Constructor.
     *
     * @param moodle_database $db Moodle Database
     */
    public function __construct(
        moodle_database $db
    ) {
        $this->db = $db;
    }

    /**
     * Returns all categories
     * (with lang string in out)
     *
     * @return stdClass[] Data objects
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_all(): array {
        $categories = $this->db->get_records(
            'local_information_center_categories',
            fields: 'id,name'
        );

        foreach ($categories as $category) {
            $category->out = get_string(
                'category:' . $category->name,
                'local_information_center'
            );
        }

        return $categories;
    }
}
