<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Notification page where notfications are created and edited.
 * - Uses a form to get the data
 *
 * @author      Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright   2025, oncampus GmbH <support@oncampus.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Load in Moodle config.
require('../../../config.php');
require_once($CFG->libdir . '/tablelib.php');

use core\di;
use local_information_center\form\edit_notification_form;
use local_information_center\notification\contracts\NotificationManager;
use local_information_center\notification\contracts\NotificationsRead;
use local_information_center\notification\contracts\notification;

require_login();
$context = context_system::instance();
require_capability('local/information_center:update_or_create_messages', $context);

$id = optional_param('id', null, PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_url(new moodle_url('/local/information_center/pages/edit_notification.php', $id ? ['id' => $id] : []));
$PAGE->set_title(get_string('pluginname', 'local_information_center'));
$PAGE->set_heading(get_string('form:header', 'local_information_center'));

// Instantiate the myform form from within the plugin.
$mform = new edit_notification_form();
$messagemanager = di::get(NotificationManager::class);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/information_center/pages/admin_notification_dashboard.php'));
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
    $message->component = 'tool_oc_notification';

    $errors = $messagemanager->validate($message);
    if (!empty($errors)) {
        var_export($errors);
        return;
    }

    $messagemanager->add_or_update($message);

    if ($fromform->renotify == 1) {
        $readmng = di::get(NotificationsRead::class);
        $readmng->reset_readcount($message->id);
    }

    redirect(new moodle_url('/local/information_center/pages/admin_notification_dashboard.php'));
}

if ($id && $id != -1) {
    $message = $messagemanager->get($id);
    $mform->set_notification_data($message);
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
