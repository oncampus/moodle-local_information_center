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

namespace local_information_center\external;

defined('MOODLE_INTERNAL') || die();

use context;
use context_system;
use core\di;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use dml_exception;
use Exception;
use invalid_parameter_exception;
use local_information_center\notification\contracts\NotificationManager;
use local_information_center\notification\contracts\NotificationsRead;
use local_information_center\notification\contracts\notification;
use moodle_database;
use required_capability_exception;
use stdClass;

require_once($CFG->libdir . '/externallib.php');

/**
 * Sends a message via the information center
 *
 * Messages send by this way can only be added by the main admin
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_message extends external_api {
    /**
     * Adds a message and sends it to the specified users
     *
     * @param $messagedata
     * @param bool $renotify
     * @return stdClass Errors to return
     * @throws dml_exception Database cannot be reached
     * @throws invalid_parameter_exception Input parameters are not valid
     * @throws required_capability_exception Capability are not meet to send this message
     */
    public static function execute($messagedata, bool $renotify): stdClass {
        global $USER;

        [
            'messagedata' => $messagedata,
        ] = self::validate_parameters(
            self::execute_parameters(),
            ['messagedata' => $messagedata]
        );

        $ctx = context_system::instance();
        if (!$ctx instanceof context) {
            throw new Exception("System context could not be loaded");
        }
        self::validate_context($ctx);
        require_capability('local/information_center:update_or_create_messages', $ctx);

        $message = new notification();
        $message->useridfrom = $USER->id;
        $message->timestart = $messagedata['timestart'];
        $message->timeend = $messagedata['timeend'];
        $message->timedeleted = $messagedata['timedeleted'];
        $message->categoryid = $messagedata['categoryid'];
        $message->fullmessageformat = $messagedata['fullmessageformat'];
        $message->fullmessage = $messagedata['fullmessage'];
        $message->smallmessage = $messagedata['smallmessage'];
        $message->visibility = $messagedata['visibility'];
        $message->subject = $messagedata['subject'];
        $message->component = 'external';

        $messagemanager = di::get(NotificationManager::class);
        $errors = $messagemanager->validate($message);
        if (!empty($errors)) {
            $output = new stdClass();
            $output->errors = array_map(function ($field, $message) {
                return [
                    'field' => $field,
                    'message' => $message,
                ];
            }, array_keys($errors), $errors);
            return $output;
        }

        // Search if the message exists locally.
        $message->id = self::get_local_id($messagedata['id']);
        $id = $messagemanager->add_or_update($message);
        if ($message->id === null) {
            self::save_local_id($message->id, $id);
        }

        if ($renotify) {
            $readmng = di::get(NotificationsRead::class);
            $readmng->reset_readcount($id);
        }

        $output = new stdClass();
        $output->errors = [];
        return $output;
    }

    /**
     * Saves a mapping of a local ID to a remote ID
     *
     * @param int $localid local ID
     * @param int $remoteid remote ID
     * @return bool Successful
     * @throws dml_exception
     */
    private static function save_local_id(int $localid, int $remoteid): bool {
        $db = di::get(moodle_database::class);
        return $db->insert_record(
            'local_information_center_external_ids',
            (object) ['messageid' => $localid, 'externalid' => $remoteid],
            false
        );
    }

    /**
     * Check for local mapping of the given remote id
     *
     * @param int $remoteid remote ID
     * @return int|null Local notification ID, if mapping exists
     * @throws dml_exception
     */
    private static function get_local_id(int $remoteid): int|null {
        $db = di::get(moodle_database::class);
        $localid = $db->get_field(
            'local_information_center_external_ids',
            'messageid',
            ['externalid' => $remoteid]
        );
        if ($localid === false) {
            return null;
        }
        return $localid;
    }

    /**
     * Allowed input parameter structure
     *
     * @return external_function_parameters Structure of a message
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'messagedata' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'external message id'),
                'timestart' => new external_value(PARAM_INT, 'Timestamp start', VALUE_OPTIONAL),
                'timeend' => new external_value(PARAM_INT, 'Timestamp end', VALUE_OPTIONAL),
                'timedeleted' => new external_value(PARAM_INT, 'Timestamp deleted', VALUE_OPTIONAL),
                'categoryid' => new external_value(PARAM_INT, 'Category ID'),
                'fullmessage' => new external_value(PARAM_RAW, 'Full message'),
                'fullmessageformat' => new external_value(PARAM_INT, 'Full message format'),
                'smallmessage' => new external_value(PARAM_RAW, 'Small message'),
                'visibility' => new external_value(PARAM_TEXT, 'Visibility', VALUE_OPTIONAL),
                'subject' => new external_value(PARAM_TEXT, 'Subject'),
            ]),
            'renotify' => new external_value(PARAM_BOOL, 'renotify, if message is edited', VALUE_DEFAULT, default: false),
        ]);
    }

    /**
     * Allowed input parameter structure
     *
     * @return external_function_parameters Structure of a message
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure(
            [
                'errors' => new external_multiple_structure(
                    new external_single_structure(
                        [
                        'field'   => new external_value(PARAM_ALPHANUMEXT, 'Name of the field that has an error'),
                        'message' => new external_value(PARAM_TEXT, 'Error message for that field'),
                        ],
                        'Error report during validation'
                    ),
                    'List of errors during validation'
                ),
            ]
        );
    }
}
