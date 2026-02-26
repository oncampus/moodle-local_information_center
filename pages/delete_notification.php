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
 * This page soft deletes notifications, and returns an error or success message.
 *
 * @author      Konrad Ebel
 * @copyright   2025, oncampus GmbH <support@oncampus.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');

use core\di;
use core\output\notification;
use local_information_center\notification\contracts\NotificationManager;

require_login();
$context = context_system::instance();
require_capability('local/information_center:delete_messages', $context);

// Set PAGE variables.
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_url(new moodle_url('/local/information_center/pages/delete_notification.php'));
$PAGE->set_title(get_string('pluginname', 'local_information_center'));
$PAGE->set_heading(get_string('form:header', 'local_information_center'));

$id = optional_param('id', 0, PARAM_INT);
$return = new moodle_url('/local/information_center/pages/admin_notification_dashboard.php');

if (!empty($id) && confirm_sesskey()) {
    $manager = di::get(NotificationManager::class);

    if ($manager->delete($id)) {
        redirect(
            $return,
            get_string('deletion_success', 'local_information_center'),
            messagetype: notification::NOTIFY_SUCCESS
        );
    }
}

redirect(
    $return,
    "Error",
    messagetype: notification::NOTIFY_ERROR
);
