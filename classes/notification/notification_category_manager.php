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
use local_information_center\notification\contracts\notification_category;
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
class notification_category_manager implements NotificationCategory {
    /** @var moodle_database Moodle Database */
    private readonly moodle_database $db;
    /** @var string[] Allowed categories to choose from */
    private readonly array $allowedcategories;

    /**
     * Constructor.
     *
     * @param moodle_database $db Moodle Database
     */
    public function __construct(
        moodle_database $db
    ) {
        $this->db = $db;
        $this->allowedcategories = ['infos', 'events', 'administrative'];
    }

    /**
     * Get category options, that are allowed to be sent locally
     *
     * @return array List of notification type categories, as id => displayname
     * @throws dml_exception Database cannot be reached
     * @throws coding_exception Could not fetch language string
     */
    public function get_options(): array {
        $categories = $this->get_all();

        $categoryselect = [];
        foreach ($categories as $category) {
            // Filter out categories not allowed for local sending.
            if (!in_array($category->name, $this->allowedcategories)) {
                continue;
            }

            $categoryselect[$category->id] = $category->get_label();
        }

        return $categoryselect;
    }

    /**
     * Fetches a category by id
     *
     * @param int $id DB id
     * @return notification_category|false Category or false if id not exists
     * @throws dml_exception
     */
    public function get(int $id): notification_category|false {
        $rawcategory = $this->db->get_record(
            'local_information_center_categories',
            ['id' => $id]
        );
        if (!$rawcategory) {
            return false;
        }

        return $this->category_from_db_stdclass($rawcategory);
    }

    /**
     * Returns all categories
     * (with lang string in out)
     *
     * @return notification_category[] Message categories
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_all(): array {
        $rawcategories = $this->db->get_records(
            'local_information_center_categories',
        );

        return array_map(fn($c) => $this->category_from_db_stdclass($c), $rawcategories);
    }

    /**
     * Converts a standard class to a notification category
     *
     * @param stdClass $rawcategory
     * @return notification_category
     */
    public function category_from_db_stdclass(stdClass $rawcategory): notification_category {
        return new notification_category(
            $rawcategory->id,
            $rawcategory->name,
            $rawcategory->color,
            $rawcategory->icon
        );
    }
}
