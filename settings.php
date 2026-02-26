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
 * Settings from oc_notification Plugin
 * - Pages to the message dashboard
 *
 * @author      Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright   2025, oncampus GmbH, <support@oncampus.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if (!$hassiteconfig) {
    return;
}

// Add needed Pages.
$ADMIN->add('root', new admin_category('local_information_center', get_string('pluginname', 'local_information_center')));
$settings = new admin_externalpage(
    'local_information_center_admin_notification_dashboard',
    get_string('page:message_overview', 'local_information_center'),
    new moodle_url('/local/information_center/pages/admin_notification_dashboard.php'),
);
$ADMIN->add('local_information_center', $settings);

if (!$ADMIN->fulltree) {
    return;
}
