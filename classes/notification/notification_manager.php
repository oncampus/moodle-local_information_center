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
use invalid_parameter_exception;
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
     * @return void
     * @throws dml_exception
     */
    public function add_or_update(notification $notification): void {
        $data = new stdClass();
        $data->timemodified = $notification->timemodified;
        $data->subject = $notification->subject;
        $data->fullmessage = $notification->fullmessage;
        $data->fullmessageformat = $notification->fullmessageformat;
        $data->smallmessage = $notification->smallmessage;
        $data->visibility = $notification->visibility->value;
        $data->component = $notification->component;
        $data->categoryid = $notification->categoryid;
        $data->timestart = $notification->timestart;
        $data->timeend = $notification->timeend;
        $data->timedeleted = $notification->timedeleted;

        if (!$this->db->record_exists('local_information_center_categories', ['id' => $notification->categoryid])) {
            throw new invalid_parameter_exception(get_string(
                'validation:category:notexist',
                'local_information_center',
                $notification->categoryid
            ));
        }

        if (!$this->db->record_exists('user', ['id' => $notification->useridfrom])) {
            throw new invalid_parameter_exception(get_string(
                'validation:useridfrom:notexist',
                'local_information_center',
                $notification->useridfrom
            ));
        }

        $id = $this->db->get_field(self::TABLE, 'id', ['uuid' => $notification->uuid]);

        if (!$id) {
            $data->timecreated = di::get(clock::class)->time();
            $data->useridfrom = $notification->useridfrom;
            $data->uuid = $notification->uuid;
            $this->db->insert_record(self::TABLE, $data);
        } else {
            $useridfrom = $this->db->get_field(self::TABLE, 'useridfrom', ['id' => $id]);

            if ($useridfrom != $notification->useridfrom) {
                throw new Exception(get_string(
                    'validation:useridfrom:cannotbechanged',
                    'local_information_center',
                    $notification->useridfrom
                ));
            }

            $data->id = $id;
            $this->db->update_record(self::TABLE, $data);
        }
    }

    /**
     * Gets a message with the given uuid
     *
     * @param string $uuid Message UUID
     * @return notification|false The message data or false if not found
     * @throws dml_exception
     */
    public function get(string $uuid): notification|false {
        $data = $this->db->get_record(self::TABLE, ['uuid' => $uuid]);
        if (!$data) {
            return false;
        }
        return $this->parse_to_notification($data);
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
        $request->order = "";
        $request->offset = null;
        $request->limit = null;
        $query = new notification_query($request);
        $sql = $query->get_sql('COUNT(1)');
        return $DB->count_records_sql($sql[0], $sql[1]);
    }

    /**
     * Deletes the message with the given ID
     *
     * @param int $uuid Message UUID
     * @throws dml_exception
     */
    public function delete(int $uuid): void {
        if (!$record = $this->db->get_record(self::TABLE, ["uuid" => $uuid])) {
            return;
        }

        $record->timedeleted = di::get(clock::class)->time();
        $this->db->update_record(self::TABLE, $record);
    }

    /**
     * Returns all non-deleted messages
     *
     * @return notification[] Message data
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
     * Gets notifications with the given request
     *
     * @param notification_query_data $request Notification data request
     * @return notification[] Notifications
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_with_request(notification_query_data $request): array {
        $query = new notification_query($request);
        $sql = $query->get_sql('m.*');
        $rawdata = $this->db->get_records_sql($sql[0], $sql[1], $sql[2], $sql[3]);
        return array_map(fn ($record) => $this->parse_to_notification($record), $rawdata);
    }

    private function parse_to_notification(stdClass $data): notification {
        $visibility = visibility::from($data->visibility);

        return new notification(
            $data->uuid,
            $data->useridfrom,
            $data->subject,
            $data->fullmessage,
            $data->fullmessageformat,
            $data->smallmessage,
            $visibility,
            $data->component,
            $data->categoryid,
            $data->timemodified,
            $data->timestart,
            $data->timeend,
            $data->timedeleted,
        );
    }

    public function get_categories_with_messages(string $component): array {
        return $this->db->get_fieldset(
            self::TABLE,
            'DISTINCT categoryid',
            ['timedeleted' => null, 'component' => $component]
        );
    }
}
