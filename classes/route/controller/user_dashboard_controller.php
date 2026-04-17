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
use core\exception\required_capability_exception;
use core\output\tabobject;
use core\param;
use core\router;
use core\router\route;
use core\router\route_controller;
use core\router\schema\parameters\path_parameter;
use core\router\schema\parameters\query_parameter;
use html_writer;
use local_information_center\notification\contracts\NotificationsRead;
use local_information_center\output\infocenter;
use moodle_url;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class user_dashboard_controller {
    use route_controller;

    public function __construct(
        private router $router,
    ) {
    }

    #[route(
        path: paths::USER_DASHBOARD . '[/{component}]',
        pathtypes: [
            new path_parameter(
                name: 'component',
                type: param::ALPHA,
                default: 'external',
                description: 'Component external or internal',
            ),
        ],
        queryparams: [
            new query_parameter(
                name: 'query',
                type: param::RAW,
                default: null,
                description: 'Search input of the user',
            ),
            new query_parameter(
                name: 'page',
                type: param::INT,
                default: 0,
                description: 'Page the user currently is on',
            ),
            new query_parameter(
                name: 'category',
                type: param::INT,
                default: null,
                description: 'Notification category to filter by',
            ),
        ],
        requirelogin: new router\require_login(),
    )]
    public function dashboard(
        ?string $component,
        ServerRequestInterface $request,
        ResponseInterface $response,
        NotificationsRead $notificationsread,
    ): ResponseInterface {
        global $USER, $OUTPUT, $PAGE;

        $context = context_system::instance();
        $component = $component ?? 'external';

        if (
            !has_any_capability([
                'local/information_center:read_student_messages',
                'local/information_center:read_teacher_messages',
                'local/information_center:read_manager_messages',
                'local/information_center:read_admin_messages',
            ], $context)
        ) {
            throw new required_capability_exception(
                $context,
                'local/information_center:read_student_messages',
                'nopermission',
                ''
            );
        }

        $url = paths::user_dashboard($component);
        $PAGE->set_context($context);
        $PAGE->set_url($url);
        $PAGE->set_title(get_string('pluginname', 'local_information_center'));
        $PAGE->set_heading(get_string('overview:title', 'local_information_center'));

        $queryparams = $request->getQueryParams();
        $redirecturl = new moodle_url($url, $queryparams);
        $renderer = $PAGE->get_renderer('core', 'message');
        $infocenter = new infocenter(
            $redirecturl,
            new moodle_url(paths::admin_dashboard()),
            $component,
            $queryparams['query'],
            $queryparams['page'],
            $queryparams['category']
        );

        $tabs = [];
        foreach (['internal', 'external'] as $tabcomponent) {
            $unreadmsgs = $notificationsread->count_unread($USER->id, $tabcomponent == 'external');
            $tabs[] = $this->get_notification_tab($tabcomponent, $unreadmsgs);
        }

        $response->withStatus(200);
        $response->getBody()->write(
            $OUTPUT->header() .
            print_tabs([$tabs], $component, return: true) .
            $renderer->render($infocenter) .
            $OUTPUT->footer()
        );

        return $response;
    }

    public function get_notification_tab(
        string $component,
        int $unreadmsgs
    ): tabobject {
        $text = get_string("overview:$component", 'local_information_center');
        if (0 < $unreadmsgs) {
            $text .= html_writer::span(
                html_writer::span(
                    $unreadmsgs,
                    'infocenter-count-container',
                    ['aria-label' => get_string('navigationnode:unreadcount', 'local_information_center', $unreadmsgs)]
                ),
                'position-relative d-inline-block'
            );
        }

        return new tabobject(
            $component,
            paths::user_dashboard($component),
            $text,
            get_string("overview:$component", 'local_information_center')
        );
    }
}
