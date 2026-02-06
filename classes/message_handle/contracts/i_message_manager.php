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

use dml_exception;
use stdClass;

/**
 * Handles the messages in the database
 */
interface i_message_manager {
    /**
     * Adds or updates a message in the database
     *
     * @param message $message The message data
     * @return int ID of the added message
     * @throws dml_exception
     */
    public function add_or_update(message $message): int;

    /**
     * Validates the data of a message
     *
     * @param message $message Data of the new message (change)
     * @return string[] Array of errors
     * @throws dml_exception Database cannot be reached
     */
    public function validate(message $message): array;

    /**
     * Returns a list of all non-deleted messages
     *
     * @return stdClass[] List of messages
     */
    public function get_all(): array;

    /**
     * Returns all data of a message
     *
     * @param int $id ID of the message
     * @return message|false Data object of the message
     * @throws dml_exception Could not connect to database
     */
    public function get(int $id): message|false;

    /**
     * Filters out messages according to the given get_request
     *
     * @param message_query_data $request Request with applied filters
     * @return stdClass[] Data according to the request
     * @throws dml_exception Could not reach the database
     */
    public function get_with_request(message_query_data $request): array;

    /**
     * Gives the messages with the filters in the request
     *
     * @param message_query_data $request Request with applied filters (Order, Limit, Offset should not be set)
     * @return int Count of messages
     * @throws dml_exception Error when database cannot be reached
     */
    public function count_with_request(message_query_data $request): int;

    /**
     * Soft deletes the message
     *
     * @param int $id ID of the message to delete
     * @return bool True if the soft delete was successful
     * @throws dml_exception Database cannot be reached
     */
    public function delete(int $id): bool;
}
