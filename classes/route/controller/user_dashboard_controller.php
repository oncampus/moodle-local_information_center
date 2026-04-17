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
use core\exception\coding_exception;
use core\exception\moodle_exception;
use core\exception\required_capability_exception;
use core\output\tabobject;
use core\param;
use core\router;
use core\router\route;
use core\router\route_controller;
use core\router\schema\parameters\path_parameter;
use core\router\schema\parameters\query_parameter;
use dml_exception;
use html_writer;
use local_information_center\notification\contracts\NotificationsRead;
use local_information_center\output\infocenter;
use moodle_url;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Notification inbox for users, separated in two tabs external and internal.
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2026, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_dashboard_controller {
    use route_controller;

    /**
     * Constructor
     *
     * @param router $router
     */
    public function __construct(
        /** @var router router needed for controllers */
        private readonly router $router,
    ) {
    }

    /**
     * Inbox area, where users receive notifications
     *
     * @param string|null $component External or internal
     * @param ServerRequestInterface $request HTTP Request
     * @param ResponseInterface $response Empty HTTP response
     * @param NotificationsRead $notificationsread Manager for read status
     * @return ResponseInterface HTTP Response
     * @throws coding_exception
     * @throws moodle_exception
     * @throws dml_exception
     * @throws required_capability_exception
     */
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

    /**
     * Creates a tab for the inbox area
     *
     * @param string $component External or internal
     * @param int $unreadmsgs Count of unread notifications for this tab
     * @return tabobject
     * @throws coding_exception
     */
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
