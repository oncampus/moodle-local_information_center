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

use dml_exception;

/**
 * Utilities to check enrolments of users
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_utils {
    /**
     * User is enrolled as this role somewhere
     *
     * @param int $userid Userid of user to check
     * @param string $shortname Shortname of the role
     * @return bool True if enrolled as this role somewhere
     * @throws dml_exception Database cannot be reached
     */
    public static function is_enrolled_anywhere_as(int $userid, string $shortname): bool {
        global $DB;

        $roleid = self::get_roleid($shortname);

        $sql = "SELECT ra.id
                FROM {role_assignments} ra
                WHERE ra.userid = :userid
                AND ra.roleid = :roleid";

        $params = [
            'userid' => $userid,
            'roleid' => $roleid,
        ];

        return $DB->record_exists_sql($sql, $params);
    }

    /**
     * Helper to get the role ID by shortname.
     *
     * @param string $shortname Shortname of the role
     * @return int Role ID
     * @throws dml_exception Cannot connect to database
     */
    public static function get_roleid(string $shortname): int {
        global $DB;
        return $DB->get_field('role', 'id', ['shortname' => $shortname], MUST_EXIST);
    }
}
