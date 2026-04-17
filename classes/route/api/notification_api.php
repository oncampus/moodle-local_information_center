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

use context_system;
use core\context\system;
use core\exception\coding_exception;
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
use local_information_center\route\api\schemes\notification_id;
use local_information_center\route\api\schemes\notification_schema;
use local_information_center\route\api\schemes\ok_response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use required_capability_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Routing endpoint to create or update an information center message.
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2026, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification_api {
    use route_controller;

    /**
     * Reset the read status of receivers of a notification
     *
     * @param string $uuid Notification UUID
     * @param ServerRequestInterface $request HTTP Request
     * @param NotificationsRead $readstatusmanager Notification Readstatus Manager
     * @return payload_response HTTP Response
     */
    #[route(
        title: 'Notification Renotify',
        description: 'Reset the read status of a notification',
        security: [],
        path: '/messages/{id}/renotify',
        method: ['PUT', 'POST'],
        pathtypes: [
            new path_parameter(
                name: 'uuid',
                type: param::ALPHANUMEXT,
            ),
        ],
        responses: [new ok_response()]
    )]
    public function renotify(
        string $uuid,
        ServerRequestInterface $request,
        NotificationsRead $readstatusmanager,
    ): payload_response {
        $readstatusmanager->reset_readcount($uuid);
        return new payload_response(
            [],
            $request
        );
    }

    /**
     * Create or update a notification.
     *
     *  Expected JSON body:
     *  {
     *      "timestart": 1710000000,
     *      "timeend": 1710003600,
     *      "timedeleted": 0,
     *      "categoryid": 4,
     *      "fullmessage": "<p>Hello</p>",
     *      "fullmessageformat": 1,
     *      "smallmessage": "Hello",
     *      "visibility": "visible",
     *      "subject": "Subject"
     *  }
     *
     * @param string $uuid Notification UUID
     * @param ResponseInterface $response Empty HTTP Response
     * @param ServerRequestInterface $request HTTP Request
     * @param NotificationManager $notificationmanager Notification Manager
     * @return payload_response HTTP Response
     * @throws coding_exception
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
        pathtypes: [new notification_id(true)],
        requestbody: new request_body(
            description: 'Notification details to create or update',
            content: new payload_response_type(
                schema: new notification_schema()
            ),
            required: true,
        ),
        responses: [new ok_response()]
    )]
    public function add_or_update_message(
        string $uuid,
        ResponseInterface $response,
        ServerRequestInterface $request,
        NotificationManager $notificationmanager,
    ): payload_response {
        global $PAGE, $DB;

        $ctx = system::instance();
        $PAGE->set_context($ctx);
        require_capability('local/information_center:update_or_create_messages', $ctx);

        $body = $request->getParsedBody();
        $notification = $this->parse_to_notification($body);
        $notificationmanager->add_or_update($notification);

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
        if (!is_array($notificationdata)) {
            throw new invalid_parameter_exception('Request body must be a JSON object.');
        }

        $notification = notification::create(
            $notificationdata['subject'],
            $notificationdata['fullmessage'],
            $notificationdata['fullmessageformat'],
            $notificationdata['smallmessage'],
            $notificationdata['visibility'],
            $notificationdata['categoryid'],
            $notificationdata['timestart'] ?? null,
            $notificationdata['timeend'] ?? null,
            'external',
            $notificationdata['uuid']
        );
        $notification->timedeleted = $notificationdata['timedeleted'] ?? null;

        return $notification;
    }
}
