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
 * Renders the admin message overview
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');

use core\di;
use local_information_center\form\notification_filter_area;
use local_information_center\form\notification_filter_form;
use local_information_center\form\notification_admin_table;
use local_information_center\notification\contracts\NotificationManager;

require_login();
$context = context_system::instance();
require_capability('local/information_center:can_view_message_control_board', $context);

// Set PAGE variables.
$PAGE->set_context($context);
$baseurl = new moodle_url('/local/information_center/pages/admin_notification_dashboard.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_url($baseurl);
$PAGE->set_title(get_string('page:message_overview', 'local_information_center'));
$PAGE->set_heading(get_string('page:message_overview', 'local_information_center'));

// Overview.
$table = new notification_admin_table($baseurl);
$table->setup();
$filterform = new notification_filter_form();
$filterform->set_filters($table);
$filterrenderer = new notification_filter_area($filterform->render());

$messagemanager = di::get(NotificationManager::class);
$messages = $table->get_data();

echo $OUTPUT->header();
echo $OUTPUT->render($filterrenderer);

foreach ($messages as $msg) {
    $table->add_notification_data($msg);
}
$table->finish_output();

echo html_writer::link(
    new moodle_url('/local/information_center/pages/edit_notification.php'),
    get_string('settings:btn_add', 'local_information_center'),
    ['class' => 'btn btn-secondary mt-3']
);
echo $OUTPUT->footer();
