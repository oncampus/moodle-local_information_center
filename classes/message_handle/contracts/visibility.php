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

use context_system;
use local_information_center\message_handle\enrol_utils;

/**
 * Different visibilities messages can have.
 *
 * Defines which users can see a message.
 * Users with higher rights can see messages from persons with lower rights.
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
enum visibility: string {
    case VISIBILITY_ADMIN = 'admin';
    case VISIBILITY_MANAGER = 'manager';
    case VISIBILITY_TEACHER = 'teacher';
    case VISIBILITY_STUDENT = 'student';

    /**
     * Returns the rights of the given user
     *
     * @param int $userid User how to get the rights for
     * @return array Array of rights the user have
     */
    public static function get_users_visibility(int $userid): array {
        $rights = [];
        $ctx = context_system::instance();

        if (has_capability('local/information_center:read_student_messages', $ctx, $userid)) {
            $rights[] = self::VISIBILITY_STUDENT;
        }

        if (
            enrol_utils::is_enrolled_anywhere_as($userid, 'editingteacher')
            || enrol_utils::is_enrolled_anywhere_as($userid, 'teacher')
            || has_capability('local/information_center:read_manager_messages', $ctx, $userid)
        ) {
            $rights[] = self::VISIBILITY_TEACHER;
        }

        if (has_capability('local/information_center:read_manager_messages', $ctx, $userid)) {
            $rights[] = self::VISIBILITY_MANAGER;
        }

        if (has_capability('local/information_center:read_admin_messages', $ctx, $userid)) {
            $rights[] = self::VISIBILITY_ADMIN;
        }

        return $rights;
    }
}
