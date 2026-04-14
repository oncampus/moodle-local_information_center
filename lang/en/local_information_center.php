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
 * Strings for component 'local_information_center', language 'en'
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  onCampus GmbH, 2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['action:delete'] = 'Delete';
$string['action:edit'] = 'Edit';

$string['category:administrative'] = 'Administrative';
$string['category:all'] = 'All';
$string['category:events'] = 'Events';
$string['category:infos'] = 'Infos';
$string['category:innovations'] = 'Innovations';
$string['category:maintenance'] = 'Maintenance / Malfunction';

$string['deletion_success'] = 'The notification was successfully deleted';

$string['form:after'] = 'After';
$string['form:any'] = 'Any';
$string['form:before'] = 'Before';
$string['form:category'] = 'Category';
$string['form:category_help'] = '<ul>
<li><strong>Infos <i class="fa-regular fa-newspaper"></i>.</strong> General news and announcements (Low priority)</li>
<li><strong>Events <i class="fa-regular fa-calendar-plus"></i>.</strong> Information about events, dates, or activities (Medium priority)</li>
<li><strong>Administrative <i class="fa-regular fa-graduation-cap"></i>.</strong> Official or organizationally important announcements (High priority)</li>
</ul>';
$string['form:categoryname'] = 'Category';
$string['form:contains'] = 'Contains';
$string['form:deleted'] = 'Show deleted';
$string['form:equals'] = 'Equals';
$string['form:firstname'] = 'First name';
$string['form:header'] = 'New Notification';
$string['form:lastname'] = 'Last name';
$string['form:message'] = 'Message';
$string['form:renotify'] = 'Notify again';
$string['form:title'] = 'Title';
$string['form:validation:date'] = 'The end date must be later than the start date';
$string['form:visibility'] = 'Visibility';
$string['form:visibility_help'] = 'Roles have tiered visibility.
 Higher-level roles automatically include all visibility levels of lower-level roles:
<ol>
<li><strong>Learners.</strong> can only see their own notifications.</li>
<li><strong>Teachers.</strong> see notifications for teachers and learners.</li>
<li><strong>Managers.</strong> see notifications for managers, teachers and learners.</li>
<li><strong>Administrators.</strong> see all notifications.</li>
</ol>';

$string['information_center:can_view_message_control_board'] = 'Allows the admin control center to view notifications.';
$string['information_center:delete_messages'] = 'Can delete notifications in the information center';
$string['information_center:read_admin_messages'] = 'Can see admin notifications in the information center';
$string['information_center:read_manager_messages'] = 'Can see manager notifications in the information center';
$string['information_center:read_student_messages'] = 'Can see student notifications in the information center';
$string['information_center:read_teacher_messages'] = 'Can see teacher notifications in the information center';
$string['information_center:update_or_create_messages'] = 'Can update and create notifications in the information center';

$string['navigationnode:unreadcount'] = '{$a} unread notifications';

$string['overview:external'] = 'External (BW)';
$string['overview:internal'] = 'Internal';
$string['overview:sendmessages'] = 'Manage notifications';
$string['overview:title'] = 'Notifications';

$string['page'] = 'Page';
$string['page:message_overview'] = 'Notification overview';

$string['pluginname'] = 'Information Center';

$string['privacy:metadata:local_information_center'] = 'Stores information about notifications that a user has read in the information center.';
$string['privacy:metadata:local_information_center:messageid'] = 'The ID of the notification.';
$string['privacy:metadata:local_information_center:userid'] = 'The ID of the user who read a notification.';

$string['privacy:metadata:local_information_center_messages'] = 'Stores notifications sent via the information center.';
$string['privacy:metadata:local_information_center_messages:categoryid'] = 'The ID of the category this notification belongs to.';
$string['privacy:metadata:local_information_center_messages:component'] = 'The Moodle component or plugin that generated the notification.';
$string['privacy:metadata:local_information_center_messages:fullmessage'] = 'The full body text of the notification.';
$string['privacy:metadata:local_information_center_messages:fullmessageformat'] = 'The format of the full notification (e.g., HTML or plain text).';
$string['privacy:metadata:local_information_center_messages:smallmessage'] = 'A short summary or preview of the notification.';
$string['privacy:metadata:local_information_center_messages:subject'] = 'The subject of the notification.';
$string['privacy:metadata:local_information_center_messages:timecreated'] = 'The time when the notification was created.';
$string['privacy:metadata:local_information_center_messages:timedeleted'] = 'The time when the notification was deleted, if applicable.';
$string['privacy:metadata:local_information_center_messages:timeend'] = 'The time when the notification is no longer visible.';
$string['privacy:metadata:local_information_center_messages:timemodified'] = 'The time when the notification was last modified.';
$string['privacy:metadata:local_information_center_messages:timestart'] = 'The time when the notification becomes visible.';
$string['privacy:metadata:local_information_center_messages:useridfrom'] = 'The ID of the user who sent the notification.';
$string['privacy:metadata:local_information_center_messages:visibility'] = 'To which users the notification is visible.';

$string['search'] = 'Search';

$string['settings:btn_add'] = 'Add new notification';
$string['settings:enable'] = 'Enable';
$string['settings:enable_desc'] = 'Toggles whether all notifications are enabled/disabled';

$string['table:createdby'] = 'Created by';
$string['table:createdby:extern'] = 'External';
$string['table:enddate'] = 'End date';
$string['table:enddate_help'] = 'Notifications will become invisible after this time. You can view them afterwards in the management area.';
$string['table:messagecategory'] = 'Category';
$string['table:startdate'] = 'Start date';
$string['table:title'] = 'Title';
$string['table:type'] = 'Type';
$string['table:visibility'] = 'Visibility';

$string['task:message_cleanup'] = 'Notification cleanup task';

$string['validation:category:notexist'] = 'Category {$a} does not exist';
$string['validation:timeend:aftertimestart'] = 'The end time must be later than the start time';
$string['validation:timeend:notnegative'] = 'End time cannot be negative';
$string['validation:timestart:notnegative'] = 'Start time cannot be negative';
$string['validation:useridfrom:cannotbechanged'] = 'The owner of notifications cannot be changed';
$string['validation:useridfrom:notexist'] = 'User with ID {$a} does not exist';
$string['validation:visibility:invalid'] = 'Visibility {$a} is not valid';

$string['visibility:admin'] = 'Visible to administrators';
$string['visibility:manager'] = 'Visible to managers';
$string['visibility:student'] = 'Visible to students';
$string['visibility:teacher'] = 'Visible to teachers';
