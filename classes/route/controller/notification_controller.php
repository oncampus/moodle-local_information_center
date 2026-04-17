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

namespace local_information_center\route\controller;

use context_system;
use core\output\html_writer;
use core\param;
use core\router;
use core\router\require_login;
use core\router\route;
use core\router\route_controller;
use core\router\schema\parameters\path_parameter;
use core\router\schema\response\payload_response;
use Exception;
use invalid_parameter_exception;
use local_information_center\notification\contracts\notification;
use local_information_center\notification\contracts\NotificationManager;
use local_information_center\notification\contracts\NotificationsRead;
use local_information_center\output\edit_notification_form;
use local_information_center\output\notification_admin_table;
use local_information_center\output\notification_filter_area;
use local_information_center\output\notification_filter_form;
use local_information_center\route\api\schemes\notification_id;
use moodle_url;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class notification_controller {
    use route_controller;

    public function __construct(
        private router $router,
    ) {
    }

    #[route(
        path: paths::ADMIN_DASHBOARD,
        method: ['GET', 'POST'],
        requirelogin: new router\require_login(),
    )]
    public function index(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        global $OUTPUT;

        $context = context_system::instance();
        require_capability('local/information_center:can_view_message_control_board', $context);

        $baseurl = $this->get_baseurl($request);
        $this->init_admin_page(
            $baseurl,
            get_string('page:message_overview', 'local_information_center'),
            get_string('page:message_overview', 'local_information_center')
        );

        // Overview.
        $table = new notification_admin_table($baseurl);
        $table->setup();
        $filterform = new notification_filter_form();
        $filterform->set_filters($table);
        $filterrenderer = new notification_filter_area($filterform->render());

        $notifications = $table->get_data();

        ob_start();
        foreach ($notifications as $msg) {
            $table->add_notification_data($msg);
        }
        $table->finish_output();
        $tablehtml = ob_get_clean();

        $response->withStatus(200);
        $response->getBody()->write(
            $OUTPUT->header() .
            $OUTPUT->render($filterrenderer) .
            $tablehtml .
            html_writer::link(
                paths::edit_notification(),
                get_string('settings:btn_add', 'local_information_center'),
                ['class' => 'btn btn-secondary mt-3']
            ) .
            $OUTPUT->footer()
        );
        return $response;
    }

    #[route(
        path: paths::ADMIN_DASHBOARD . '/edit[/{id}]',
        method: ['GET', 'POST'],
        pathtypes: [new notification_id(false)],
        requirelogin: new router\require_login(),
    )]
    public function edit(
        ?string $uuid,
        ServerRequestInterface $request,
        ResponseInterface $response,
        NotificationsRead $notificationsread,
        NotificationManager $messagemanager
    ): ResponseInterface {
        global $CFG, $OUTPUT;
        require_once($CFG->libdir . '/tablelib.php');

        $context = context_system::instance();
        require_capability('local/information_center:update_or_create_messages', $context);

        $this->init_admin_page(
            $this->get_baseurl($request),
            get_string('pluginname', 'local_information_center'),
            get_string('form:header', 'local_information_center')
        );

        $mform = new edit_notification_form();

        if ($mform->is_cancelled()) {
            redirect(paths::admin_dashboard());
        } else if ($fromform = $mform->get_data()) {
            $notification = notification::create(
                $fromform->title,
                $fromform->message['text'],
                $fromform->message['format'],
                '',
                $fromform->visibility,
                $fromform->category,
                $fromform->startdate,
                $fromform->enddate,
                uuid: $uuid,
            );
            $messagemanager->add_or_update($notification);

            if ($fromform->renotify == 1) {
                $notificationsread->reset_readcount($notification->uuid);
            }

            return self::redirect(
                $response,
                paths::admin_dashboard()
            );
        }

        if ($uuid) {
            $message = $messagemanager->get($uuid);
            $mform->set_notification_data($message);
        }

        $response->getBody()->write(
            $OUTPUT->header() .
            $mform->render() .
            $OUTPUT->footer()
        );

        return $response;
    }

    #[route(
        title: 'Delete notification',
        description: 'Soft deletes a notification',
        path: paths::ADMIN_DASHBOARD . '/delete/{id}',
        pathtypes: [new notification_id(true)],
        requirelogin: new require_login(),
    )]
    public function delete(
        string $uuid,
        ServerRequestInterface $request,
        ResponseInterface $response,
        NotificationManager $manager,
    ): ResponseInterface {
        $context = context_system::instance();
        require_capability('local/information_center:delete_messages', $context);

        if (!confirm_sesskey()) {
            throw new invalid_parameter_exception(
                "Sesskey is not valid! Deletion was aborted for security reasons."
            );
        }

        $manager->delete($uuid);
        $response->withStatus(200);
        \core\notification::success(get_string('deletion_success', 'local_information_center'));
        return self::redirect(
            $response,
            paths::admin_dashboard()
        );
    }

    private function init_admin_page(
        moodle_url $url,
        string $title,
        string $header
    ): void {
        global $PAGE;
        $PAGE->set_context(context_system::instance());
        $PAGE->set_pagelayout('admin');
        $PAGE->set_url($url);
        $PAGE->set_title($title);
        $PAGE->set_heading($header);
    }

    private function get_baseurl(ServerRequestInterface $request): moodle_url {
        return new moodle_url($request->getUri()->getPath());
    }
}
