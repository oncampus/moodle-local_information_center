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

use coding_exception;
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
     * Adds / Updates a notification and sends it to a user group
     *
     * @param string $uuid UUID of the notification
     * @param array $messagedata Data of the notification to create or update
     * @param bool $renotify True, if notification status should be resetted for all users
     * @return stdClass Validation errors of the notification
     * @throws dml_exception Database cannot be reached
     * @throws invalid_parameter_exception Input parameters are not valid
     * @throws required_capability_exception Capability are not meet to send this message
     */
    public static function execute(string $uuid, array $messagedata, bool $renotify): stdClass {
        global $USER;
        [
            'messagedata' => $messagedata,
            'uuid' => $uuid,
            'renotify' => $renotify,
        ] = self::validate_parameters(
            self::execute_parameters(),
            [
                'messagedata' => $messagedata,
                'uuid' => $uuid,
                'renotify' => $renotify,
            ]
        );

        $ctx = context_system::instance();
        if (!$ctx instanceof context) {
            throw new Exception("System context could not be loaded");
        }
        self::validate_context($ctx);
        require_capability('local/information_center:update_or_create_messages', $ctx);

        $messagedata["useridfrom"] = $USER->id;
        $notification = self::parse_to_notification($uuid, $messagedata);

        $notificationmng = di::get(NotificationManager::class);
        $notificationmng->add_or_update($notification);

        if ($renotify) {
            $readmng = di::get(NotificationsRead::class);
            $readmng->reset_readcount($uuid);
        }

        return (object) ['errors' => []];
    }

    /**
     * Convert data to a notification
     *
     * @param string $uuid UUID of notification
     * @param array|object|null $notificationdata Data of notification
     * @return notification Created notification
     * @throws coding_exception
     * @throws invalid_parameter_exception
     */
    private static function parse_to_notification(string $uuid, array|null|object $notificationdata): notification {
        if (!is_array($notificationdata)) {
            throw new invalid_parameter_exception('Request body must be a JSON object.');
        }

        $notification = notification::create(
            $notificationdata['subject'],
            $notificationdata['fullmessage'],
            FORMAT_HTML,
            '',
            $notificationdata['visibility'],
            $notificationdata['categoryid'],
            $notificationdata['timestart'] ?? null,
            $notificationdata['timeend'] ?? null,
            'external',
            $uuid
        );
        $notification->timedeleted = $notificationdata['timedeleted'] ?? null;

        return $notification;
    }

    /**
     * Allowed input parameter structure
     *
     * @return external_function_parameters Structure of a message
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'uuid' => new external_value(PARAM_ALPHANUM, 'message uuid'),
            'messagedata' => new external_single_structure([
                'timestart' => new external_value(PARAM_INT, 'Timestamp start', VALUE_OPTIONAL),
                'timeend' => new external_value(PARAM_INT, 'Timestamp end', VALUE_OPTIONAL),
                'timedeleted' => new external_value(PARAM_INT, 'Timestamp deleted', VALUE_OPTIONAL),
                'categoryid' => new external_value(PARAM_INT, 'Category ID'),
                'fullmessage' => new external_value(PARAM_RAW, 'Full message'),
                'fullmessageformat' => new external_value(PARAM_INT, 'Full message format'),
                'smallmessage' => new external_value(PARAM_RAW, 'Small message'),
                'visibility' => new external_value(PARAM_TEXT, 'Visibility'),
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
