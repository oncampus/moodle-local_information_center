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

use context;
use context_system;
use core\clock;
use core\di;
use Exception;
use local_information_center\message_handle\contracts\i_message_manager;
use local_information_center\message_handle\contracts\message;
use local_information_center\message_handle\contracts\message_query_data;
use local_information_center\message_handle\contracts\visibility;
use moodle_database;

/**
 * Gives options to handle the messages of this plugin
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
readonly class message_manager implements i_message_manager {
    const TABLE = 'local_information_center_messages';

    public function __construct(
        private clock $clock,
        private moodle_database $db,
    ) {
    }

    public function add_or_update(message $message): int {
        $ctx = context_system::instance();
        if (!$ctx instanceof context) {
            throw new Exception("Failed to fetch context");
        }
        require_capability('local/information_center:update_or_create_messages', $ctx);

        $data = (object) get_object_vars($message);
        $data->timemodified = $this->clock->time();

        if ($message->id !== null) {
            $success = $this->db->update_record(self::TABLE, $data);
            if (!$success) {
                throw new Exception("Failed to update record");
            }
            return $message->id;
        } else {
            unset($data->id);
            $data->timecreated = di::get(clock::class)->time();
            $successorid = $this->db->insert_record(self::TABLE, $data);
            if ($successorid === false) {
                throw new Exception("Failed to insert record");
            }
            return $successorid;
        }
    }

    public function get(int $id): message|false {
        $data = $this->db->get_record(self::TABLE, ['id' => $id]);
        if (!$data) {
            return false;
        }
        return message::from_stdClass($data);
    }

    public function validate(message $message): array {
        $error = [];

        if (!$this->db->record_exists('local_information_center_categories', ['id' => $message->categoryid])) {
            $error['categoryid'] = "Category $message->categoryid does not exist";
        }

        if (
            $message->timestart !== null
            && $message->timestart < 0
        ) {
            $error['timestart'] = "Timestart can not be negative";
        }

        if (
            $message->timeend !== null
            && $message->timeend < 0
        ) {
            $error['timeend'] = "Timeend can not be negative";
        }

        if (
            $message->timestart !== null
            && $message->timeend !== null
            && $message->timestart > $message->timeend
        ) {
            $error['timeend'] = "Timeend can not be smaller than timestart";
        }

        if (
            $message->visibility !== null
            && visibility::tryFrom($message->visibility) == null
        ) {
            $error['visibility'] = "Visibility is not valid";
        }

        if (!$this->db->record_exists('user', ['id' => $message->useridfrom])) {
            $error['useridfrom'] = 'User does not exist';
        }

        // Only check on update.
        if ($message->id !== null) {
            $useridfrom = $this->db->get_field(self::TABLE, 'useridfrom', ['id' => $message->id]);

            if ($useridfrom != $message->useridfrom) {
                $error['useridfrom'] = 'The owner of messages cannot be changed';
            }
        }

        return $error;
    }

    public function count_with_request(
        message_query_data $request,
    ): int {
        global $DB;
        $request = clone $request;
        $request->select = "COUNT(1)";
        $request->order = "";
        $request->offset = null;
        $request->limit = null;
        $query = new message_query($request);
        $sql = $query->get_sql();
        return $DB->count_records_sql($sql[0], $sql[1]);
    }

    public function delete(int $id): bool {
        $ctx = context_system::instance();
        if (!$ctx instanceof context) {
            throw new Exception("Failed to fetch context");
        }
        require_capability('local/information_center:delete_messages', $ctx);

        if (!$record = $this->db->get_record(self::TABLE, ["id" => $id])) {
            return false;
        }

        $record->timedeleted = di::get(clock::class)->time();
        return $this->db->update_record(self::TABLE, $record);
    }

    public function get_all(): array {
        global $USER;

        $getrequest = new message_query_data();
        $getrequest->notdeleted = true;
        $getrequest->userid = $USER->id;
        return $this->get_with_request($getrequest);
    }

    public function get_with_request(message_query_data $request): array {
        $query = new message_query($request);
        $sql = $query->get_sql();
        return $this->db->get_records_sql($sql[0], $sql[1]);
    }
}
