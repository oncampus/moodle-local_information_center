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

use core\clock;
use dml_exception;
use local_information_center\message_handle\contracts\i_message_read;
use local_information_center\message_handle\contracts\visibility;
use moodle_database;

/**
 * Manages the read status of messages
 *
 * @author      Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright   2025, oncampus GmbH, <support@oncampus.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class message_read implements i_message_read {
    /**
     * Constructor
     *
     * @param moodle_database $db Moodle Database
     * @param clock $clock Clock
     */
    public function __construct(
        private moodle_database $db,
        private clock $clock,
    ) {
    }

    /**
     * Sets the message to read
     *
     * @param int $messageid Message ID
     * @param int $userid User ID
     * @return void
     * @throws dml_exception
     */
    public function set_read(int $messageid, int $userid): void {
        if ($this->is_read($messageid, $userid)) {
            return;
        }

        $this->db->insert_record('local_information_center', [
            'userid'    => $userid,
            'messageid' => $messageid,
        ]);
    }

    /**
     * Checks if the user have read this message
     *
     * @param int $messageid Message ID
     * @param int $userid User ID
     * @return bool True if read
     * @throws dml_exception
     */
    public function is_read(int $messageid, int $userid): bool {
        return $this->db->record_exists('local_information_center', [
            'userid'    => $userid,
            'messageid' => $messageid,
        ]);
    }

    /**
     * Counts the unread messages for a user
     *
     * @param int $userid User to count for
     * @param bool|null $external If external set only counts external messages, else only internal
     * @return int Unread messages for given user
     * @throws \coding_exception
     * @throws dml_exception
     */
    public function count_unread(int $userid, ?bool $external = null): int {
        $rights = array_map(
            fn($v) => $v->value,
            visibility::get_users_visibility($userid)
        );

        [$insql, $inparams] = $this->db->get_in_or_equal(
            $rights,
            SQL_PARAMS_NAMED,
            'vis',
            onemptyitems: true
        );

        $sqlcomponent = "";
        if ($external === true) {
            $sqlcomponent = "AND m.component = 'external'";
        } else if ($external === false) {
            $sqlcomponent = "AND m.component <> 'external'";
        }

        // Count messages that are currently active, not soft-deleted,
        // visible for the user's rights, and not yet marked as read.
        $sql = "SELECT COUNT(m.id)
              FROM {local_information_center_messages} m
             WHERE m.timedeleted IS NULL
               AND m.timestart <= :now
               AND m.timeend   >= :now2
               AND m.visibility $insql
               $sqlcomponent
               AND NOT EXISTS (
                    SELECT 1
                      FROM {local_information_center} r
                     WHERE r.messageid = m.id AND r.userid = :userid
               )";

        $now = $this->clock->time();
        $inparams['now'] = $now;
        $inparams['now2'] = $now;
        $inparams['userid'] = $userid;
        return $this->db->count_records_sql($sql, $inparams);
    }

    /**
     * Resets, that the message is read for all users
     *
     * @param int $messageid Message ID
     * @return void
     * @throws dml_exception
     */
    public function reset_readcount(int $messageid): void {
        $this->db->delete_records('local_information_center', [
            'messageid' => $messageid,
        ]);
    }
}
