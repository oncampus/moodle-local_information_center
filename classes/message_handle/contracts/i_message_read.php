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

use core\exception\coding_exception;
use dml_exception;

/**
 * Handles the read status of messages
 */
interface i_message_read {
    /**
     * Marks the message as read for the user.
     *
     * @param int $messageid
     * @param int $userid
     * @throws dml_exception Database cannot be reached.
     */
    public function set_read(int $messageid, int $userid): void;

    /**
     * Returns whether the user already has a read-marker for this message.
     *
     * @param int $messageid
     * @param int $userid
     * @return bool true if read, false otherwise
     * @throws dml_exception Database cannot be reached.
     */
    public function is_read(int $messageid, int $userid): bool;

    /**
     * Counts unread messages for a user.
     *
     * @param int $userid The user ID for whom to count unread messages.
     * @param null|bool $external Whether to search for internal or external unread (null: count for all)
     * @return int Number of unread, visible messages for this user.
     * @throws dml_exception|coding_exception Database cannot be reached.
     */
    public function count_unread(int $userid, ?bool $external = null): int;

    /**
     * Resets the message read status for a message (to unread)
     *
     * @param int $messageid ID of the message
     */
    public function reset_readcount(int $messageid): void;
}
