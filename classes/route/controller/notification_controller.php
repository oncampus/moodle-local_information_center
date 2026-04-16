<?php

namespace local_information_center\route\controller;

use context_system;
use core\output\html_writer;
use core\param;
use core\router;
use core\router\route;
use core\router\route_controller;
use core\router\schema\parameters\path_parameter;
use Exception;
use local_information_center\notification\contracts\notification;
use local_information_center\notification\contracts\NotificationManager;
use local_information_center\notification\contracts\NotificationsRead;
use moodle_url;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class notification_controller {
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

        $messages = $table->get_data();

        ob_start();
        foreach ($messages as $msg) {
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
        pathtypes: [
            new path_parameter(
                name: 'id',
                type: param::INT,
                default: null,
                description: 'ID of the notification to edit',
            ),
        ],
        requirelogin: new router\require_login(),
    )]
    public function edit(
        ?int $id,
        ServerRequestInterface $request,
        ResponseInterface $response,
        NotificationsRead $notificationsread,
        NotificationManager $messagemanager
    ): ResponseInterface {
        global $CFG, $OUTPUT, $USER;
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
            $message = new notification();
            $message->id = $fromform->id == -1 ? null : $fromform->id;
            $message->useridfrom = $USER->id;
            $message->subject = $fromform->title;
            $message->fullmessage = $fromform->message['text'];
            $message->fullmessageformat = $fromform->message['format'];
            $message->smallmessage = '';
            $message->timestart = $fromform->startdate;
            $message->timeend = $fromform->enddate;
            $message->categoryid = $fromform->category;
            $message->visibility = $fromform->visibility;
            $message->component = 'local_information_center';

            $errors = $messagemanager->validate($message);
            if (!empty($errors)) {
                throw new Exception(var_export($errors, true));
            }

            $messagemanager->add_or_update($message);

            if ($fromform->renotify == 1) {
                $notificationsread->reset_readcount($message->id);
            }

            redirect(paths::admin_dashboard());
        }

        if ($id && $id != -1) {
            $message = $messagemanager->get($id);
            $mform->set_notification_data($message);
        }

        $response->getBody()->write(
            $OUTPUT->header() .
            $mform->render() .
            $OUTPUT->footer()
        );

        return $response;
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
