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

use context_system;
use core\context\system;
use core\param;
use core\router\require_login;
use core\router\route;
use core\router\route_controller;
use core\router\schema\parameters\path_parameter;
use core\router\schema\request_body;
use core\router\schema\response\content\payload_response_type;
use core\router\schema\response\payload_response;
use core\router\schema\response\response;
use dml_exception;
use invalid_parameter_exception;
use local_information_center\notification\contracts\NotificationManager;
use local_information_center\notification\contracts\NotificationsRead;
use local_information_center\notification\contracts\notification;
use local_information_center\route\api\schemes\notification_schema;
use local_information_center\route\api\schemes\ok;
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
class notification_api {
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
        responses: [new ok()]
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
        path: '/notifications/{id}',
        method: ['PUT', 'POST'],
        pathtypes: [
            new path_parameter(
                name: 'id',
                type: param::INT,
            ),
        ],
        requestbody: new request_body(
            description: 'Notification details to create or update',
            content: new payload_response_type(
                schema: new notification_schema()
            ),
            required: true,
        ),
        responses: [new ok()]
    )]
    public function add_or_update_message(
        int $id,
        ResponseInterface $response,
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
            throw new invalid_parameter_exception($errordesc);
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
            response: $response
        );
    }

    #[route(
        title: 'Delete notification',
        description: 'Hard deletes a notification (think about a soft delete)',
        path: '/notifications/{id}',
        method: ['DELETE'],
        pathtypes: [
            new path_parameter(
                name: 'id',
                type: param::INT,
                required: true,
                description: 'Component external or internal',
            ),
        ],
        requirelogin: new require_login(),
    )]
    public function delete(
        int $id,
        ServerRequestInterface $request,
        ResponseInterface $response,
        NotificationManager $manager,
    ): payload_response {
        $context = context_system::instance();
        require_capability('local/information_center:delete_messages', $context);

        if (
            confirm_sesskey() &&
            $manager->delete($id)
        ) {
            return new payload_response(
                payload: [],
                request: $request,
                response: $response
            );
        }

        return new payload_response(
            payload: [],
            request: $request,
            response: $response
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

        $message = new notification();
        $message->useridfrom = $USER->id;

        $message->timestart = $notificationdata['timestart'] ?? null;
        $message->timeend = $notificationdata['timeend'] ?? null;
        $message->timedeleted = $notificationdata['timedeleted'] ?? null;

        $message->categoryid = $notificationdata['categoryid'];
        $message->fullmessage = $notificationdata['fullmessage'];
        $message->fullmessageformat = $notificationdata['fullmessageformat'];
        $message->smallmessage = $notificationdata['smallmessage'];
        $message->visibility = $notificationdata['visibility'];
        $message->subject = $notificationdata['subject'];

        $message->component = 'external';

        return $message;
    }
}
