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
use core\clock;
use core\di;
use dml_exception;
use Exception;
use local_information_center\notification\contracts\NotificationManager;
use local_information_center\notification\contracts\notification;
use local_information_center\notification\contracts\notification_query_data;
use local_information_center\notification\contracts\visibility;
use moodle_database;
use stdClass;

/**
 * Gives options to handle the messages of this plugin
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification_manager implements NotificationManager {
    /** @var string Table to save and read messages from */
    private const TABLE = 'local_information_center_messages';
    /** @var clock Clock */
    private clock $clock;
    /** @var moodle_database Moodle Database */
    private moodle_database $db;


    /**
     * Constructor
     *
     * @param clock $clock Clock
     * @param moodle_database $db Moodle Database
     */
    public function __construct(
        clock $clock,
        moodle_database $db,
    ) {
        $this->clock = $clock;
        $this->db = $db;
    }

    /**
     * Adds or updates the message
     *
     * @param notification $notification Message object
     * @return int ID of the saved message
     * @throws dml_exception
     */
    public function add_or_update(notification $notification): int {
        $data = (object) get_object_vars($notification);
        $data->timemodified = $this->clock->time();

        if ($notification->id !== null) {
            $success = $this->db->update_record(self::TABLE, $data);
            if (!$success) {
                throw new Exception("Failed to update record");
            }
            return $notification->id;
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

    /**
     * Gets a message with the given id
     *
     * @param int $id Message ID
     * @return notification|false The message data or false if not found
     * @throws dml_exception
     */
    public function get(int $id): notification|false {
        $data = $this->db->get_record(self::TABLE, ['id' => $id]);
        if (!$data) {
            return false;
        }
        return notification::from_stdClass($data);
    }

    /**
     * Validate the data of a message
     *
     * @param notification $notification Message data object
     * @return string[] Param => Validation error reason
     * @throws dml_exception
     */
    public function validate(notification $notification): array {
        $error = [];

        if (!$this->db->record_exists('local_information_center_categories', ['id' => $notification->categoryid])) {
            $error['categoryid'] = get_string(
                'validation:category:notexist',
                'local_information_center',
                $notification->categoryid
            );
        }

        if (
            $notification->timestart !== null
            && $notification->timestart < 0
        ) {
            $error['timestart'] = get_string(
                'validation:timestart:notnegative',
                'local_information_center',
            );
        }

        if (
            $notification->timeend !== null
            && $notification->timeend < 0
        ) {
            $error['timeend'] = get_string(
                'validation:timeend:notnegative',
                'local_information_center',
            );
        }

        if (
            $notification->timestart !== null
            && $notification->timeend !== null
            && $notification->timestart > $notification->timeend
        ) {
            $error['timeend'] = get_string(
                'validation:timeend:aftertimestart',
                'local_information_center',
            );
        }

        if (
            $notification->visibility !== null
            && visibility::tryFrom($notification->visibility) == null
        ) {
            $error['visibility'] = get_string(
                'validation:visibility:invalid',
                'local_information_center',
                $notification->visibility
            );
        }

        if (!$this->db->record_exists('user', ['id' => $notification->useridfrom])) {
            $error['useridfrom'] = get_string(
                'validation:useridfrom:notexist',
                'local_information_center',
                $notification->useridfrom
            );
        }

        // Only check on update.
        if ($notification->id !== null) {
            $useridfrom = $this->db->get_field(self::TABLE, 'useridfrom', ['id' => $notification->id]);

            if ($useridfrom != $notification->useridfrom) {
                $error['useridfrom'] = get_string(
                    'validation:useridfrom:cannotbechanged',
                    'local_information_center',
                    $notification->useridfrom
                );
            }
        }

        return $error;
    }

    /**
     * Counts the messages returned by a request
     *
     * @param notification_query_data $request Request DTO
     * @return int Message count inside this request
     * @throws coding_exception
     * @throws dml_exception
     */
    public function count_with_request(
        notification_query_data $request,
    ): int {
        global $DB;
        $request = clone $request;
        $request->select = "COUNT(1)";
        $request->order = "";
        $request->offset = null;
        $request->limit = null;
        $query = new notification_query($request);
        $sql = $query->get_sql();
        return $DB->count_records_sql($sql[0], $sql[1]);
    }

    /**
     * Deletes the message with the given ID
     *
     * @param int $id Message ID
     * @return bool True if successful
     * @throws dml_exception
     */
    public function delete(int $id): bool {
        if (!$record = $this->db->get_record(self::TABLE, ["id" => $id])) {
            return false;
        }

        $record->timedeleted = di::get(clock::class)->time();
        return $this->db->update_record(self::TABLE, $record);
    }

    /**
     * Returns all non-deleted messages
     *
     * @return stdClass[] Message data
     * @throws dml_exception
     */
    public function get_all(): array {
        global $USER;

        $getrequest = new notification_query_data();
        $getrequest->notdeleted = true;
        $getrequest->userid = $USER->id;
        return $this->get_with_request($getrequest);
    }

    /**
     * Gets messages with the given request
     *
     * @param notification_query_data $request Message data request
     * @return stdClass[] Message data
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_with_request(notification_query_data $request): array {
        $query = new notification_query($request);
        $sql = $query->get_sql();
        return $this->db->get_records_sql($sql[0], $sql[1]);
    }
}
