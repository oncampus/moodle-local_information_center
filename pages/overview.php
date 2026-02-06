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

/**
 * Renders the infocenter dashboard
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');

use core\di;
use core\output\html_writer;
use local_information_center\message_handle\contracts\i_message_read;
use local_information_center\output\infocenter;

require_login();

$params = [
    'query' => optional_param('query', null, PARAM_TEXT),
    'page' => optional_param('page', 0, PARAM_INT),
    'category' => optional_param('category', null, PARAM_INT),
    'component' => optional_param('component', 'external', PARAM_TEXT),
];

$url = new moodle_url('/local/information_center/pages/overview.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_title(get_string('pluginname', 'local_information_center'));
$PAGE->set_heading(get_string('overview:title', 'local_information_center'));

$redirecturl = new moodle_url('/local/information_center/pages/overview.php', $params);
$renderer = $PAGE->get_renderer('core', 'message');
$infocenter = new infocenter(
    $redirecturl,
    $params['component'],
    $params['query'],
    $params['page'],
    $params['category']
);

$tabs = [];
foreach (['internal', 'external'] as $component) {
    $text = get_string("overview:$component", 'local_information_center');
    $messageread = di::get(i_message_read::class);
    $external = $component == 'external';
    $unreadmsgs = $messageread->count_unread($USER->id, $external);
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

    $taburl = clone $url;
    $taburl->param('component', $component);
    $tabs[] = new tabobject(
        $component,
        $taburl,
        $text,
        get_string("overview:$component", 'local_information_center')
    );
}

echo $OUTPUT->header();
print_tabs([$tabs], $params['component']);
echo $renderer->render($infocenter);
echo $OUTPUT->footer();
