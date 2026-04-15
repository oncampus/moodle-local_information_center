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

namespace local_information_center\route\api;

defined('MOODLE_INTERNAL') || die();

use core\context\system;
use core\param;
use core\router\route;
use core\router\route_controller;
use core\router\schema\parameters\path_parameter;
use core\router\schema\response\payload_response;
use dml_exception;
use invalid_parameter_exception;
use local_information_center\notification\contracts\NotificationManager;
use local_information_center\notification\contracts\NotificationsRead;
use local_information_center\notification\contracts\notification;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use required_capability_exception;

/**
 * Routing endpoint to create or update an information center message.
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class message_api {
    use route_controller;

    /**
     * Reset the read status of receivers of a notification
     *
     * @param int $id
     * @param notification_id_helper $idhelper
     * @param NotificationsRead $readstatusmanager
     * @return void
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    #[route(
        title: 'Notification Renotify',
        description: 'Reset the read status of a notification',
        security: [],
        path: '/messages/{id}/renotify',
        method: ['PUT', 'POST'],
        pathtypes: [
            new path_parameter(
                name: 'id',
                type: param::INT,
            ),
        ],
    )]
    public function renotify(
        int $id,
        ServerRequestInterface $request,
        NotificationsRead $readstatusmanager,
    ): payload_response {
        global $DB;
        $idhelper = new notification_id_helper($DB);
        $localid = $idhelper->get_local_id($id);
        if ($localid === null) {
            throw new invalid_parameter_exception("ID is not valid");
        }
        $readstatusmanager->reset_readcount($localid);
        return new payload_response(
            [],
            $request
        );
    }

    /**
     * Create or update a notification.
     *
     * Expected JSON body:
     * {
     *     "timestart": 1710000000,
     *     "timeend": 1710003600,
     *     "timedeleted": 0,
     *     "categoryid": 4,
     *     "fullmessage": "<p>Hello</p>",
     *     "fullmessageformat": 1,
     *     "smallmessage": "Hello",
     *     "visibility": "visible",
     *     "subject": "Subject"
     * }
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    #[route(
        title: 'Create or update a notification',
        description: 'Create or update a notification',
        security: [],
        path: '/messages/{id}',
        method: ['PUT', 'POST'],
        pathtypes: [
            new path_parameter(
                name: 'id',
                type: param::INT,
            ),
        ],
    )]
    public function add_or_update_message(
        int $id,
        ServerRequestInterface $request,
        NotificationManager $notificationmanager,
    ): payload_response {
        global $PAGE, $DB;

        $ctx = system::instance();
        $PAGE->set_context($ctx);
        require_capability('local/information_center:update_or_create_messages', $ctx);

        $idhelper = new notification_id_helper($DB);

        $body = $request->getParsedBody();
        $message = $this->parse_to_notification($body);

        $errors = $notificationmanager->validate($message);
        if (!empty($errors)) {
            $errordesc = "";
            foreach ($errors as $field => $error) {
                $errordesc .= "$field: $error\n";
            }
            throw new invalid_parameter_exception(
                  $errordesc
            );
        }

        // Search if the message exists locally.
        $message->id = $idhelper->get_local_id($id);
        $localid = $notificationmanager->add_or_update($message);
        if ($message->id === null) {
            $idhelper->save_local_id($localid, $id);
        }

        return new payload_response(
            payload: [],
            request: $request,
        );
    }

    /**
     * Validate and normalise the incoming message data.
     *
     * @param array|null|object $notificationdata Body data
     * @return notification Parsed notification
     * @throws invalid_parameter_exception
     */
    private function parse_to_notification(array|null|object $notificationdata): notification {
        global $USER;
        if (!is_array($notificationdata)) {
            throw new invalid_parameter_exception('Request body must be a JSON object.');
        }

        $required = [
            'categoryid',
            'fullmessage',
            'fullmessageformat',
            'smallmessage',
            'visibility',
            'subject',
        ];

        foreach ($required as $field) {
            if (!array_key_exists($field, $notificationdata)) {
                throw new invalid_parameter_exception("Missing required field: {$field}");
            }
        }

        $message = new notification();
        $message->useridfrom = $USER->id;
        $message->timestart = array_key_exists('timestart', $notificationdata)
            ? clean_param($notificationdata['timestart'], PARAM_INT)
            : null;
        $message->timeend = array_key_exists('timeend', $notificationdata)
            ? clean_param($notificationdata['timeend'], PARAM_INT)
            : null;
        $message->timedeleted = array_key_exists('timedeleted', $notificationdata)
            ? clean_param($notificationdata['timedeleted'], PARAM_INT)
            : null;
        $message->categoryid = clean_param($notificationdata['categoryid'], PARAM_INT);
        $message->fullmessage = clean_param($notificationdata['fullmessage'], PARAM_RAW);
        $message->fullmessageformat = clean_param($notificationdata['fullmessageformat'], PARAM_INT);
        $message->smallmessage = clean_param($notificationdata['smallmessage'], PARAM_RAW);
        $message->visibility = clean_param($notificationdata['visibility'], PARAM_TEXT);
        $message->subject = clean_param($notificationdata['subject'], PARAM_TEXT);
        $message->component = 'external';
        return $message;
    }
}
