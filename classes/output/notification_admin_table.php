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

namespace local_information_center\output;

use coding_exception;
use core\di;
use core\exception\moodle_exception;
use core\output\html_writer;
use dml_exception;
use flexible_table;
use local_information_center\notification\contracts\notification;
use local_information_center\route\controller\paths;
use moodle_database;
use moodle_url;
use stdClass;

/**
 * Table of all notifications, with edit, delete and resend buttons
 *
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2025, oncampus GmbH, <support@oncampus.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification_admin_table extends flexible_table implements FilterableTable {
    /** @var array Applied filters with field and way */
    private array $filters = [];
    /** @var array Applied filter values */
    private array $filterparams = [];

    /**
     * Define the table structure
     *
     * @param string $baseurl Base URL of the page
     * @throws coding_exception Cannot load language string
     */
    public function __construct($baseurl) {
        $this->define_baseurl($baseurl);
        $this->define_columns(['subject', 'timestart', 'timeend', 'firstname', 'categoryname', 'edit', 'delete']);
        $this->define_headers([
            get_string('table:title', 'local_information_center'),
            get_string('table:startdate', 'local_information_center'),
            get_string('table:enddate', 'local_information_center'),
            get_string('table:createdby', 'local_information_center'),
            get_string('table:messagecategory', 'local_information_center'),
            '',
            '',
        ]);

        $this->sortable(true, 'm.timestart', SORT_DESC);
        $this->no_sorting('edit');
        $this->no_sorting('delete');
        parent::__construct('message_status');
    }

    /**
     * Applies an filter to the notifications in the table
     *
     * @param string $field Database field
     * @param mixed $value Value to compare with
     * @param string $comparator Way to compare value with database value
     * @return void
     */
    public function add_filter(
        string $field,
        mixed $value,
        string $comparator = "="
    ): void {
        if ($value === null) {
            $this->filters[] = "$field $comparator";
        } else {
            $this->filters[] = "$field $comparator ?";
            $this->filterparams[] = $value;
        }
    }

    /**
     * Fetches the notification data from the database (Does not load it in the table!!!)
     *
     * @return array notification data as array
     * @throws dml_exception Cannot connect to the database
     */
    public function get_data(): array {
        $notificationorder = "";
        if ($order = $this->get_sql_sort()) {
            $notificationorder = "ORDER BY $order";
        }

        $notificationfilters = "";
        if (!empty($this->filters)) {
            $notificationfilters .= " WHERE ";
            $notificationfilters .= implode(' AND ', $this->filters);
        }

        $sql = ("
            SELECT m.uuid AS uuid,
                   m.subject AS subject,
                   m.timestart AS timestart,
                   m.timeend AS timeend,
                   m.component AS component,
                   m.useridfrom AS useridfrom,
                   m.timedeleted AS timedeleted,
                   u.firstname AS firstname,
                   u.lastname AS lastname,
                   u.firstnamephonetic AS firstnamephonetic,
                   u.lastnamephonetic AS lastnamephonetic,
                   u.middlename AS middlename,
                   u.alternatename AS alternatename,
                   mc.name AS categoryname
            FROM {local_information_center_messages} m
            LEFT JOIN {user} u
                ON m.useridfrom = u.id
            LEFT JOIN {local_information_center_categories} mc
                ON m.categoryid = mc.id
            $notificationfilters
            $notificationorder
        ");

        $db = di::get(moodle_database::class);
        return $db->get_records_sql($sql, $this->filterparams);
    }

    /**
     * Adds a notification to the shown entries
     *
     * @param stdClass $notification
     * @return void
     * @throws moodle_exception
     * @throws coding_exception
     */
    public function add_notification_data(stdClass $notification): void {
        global $USER;

        $edit = "";
        $delete = "";
        if ($notification->useridfrom == $USER->id && $notification->timedeleted === null) {
            $editurl = paths::edit_notification($notification->uuid);
            $edit = action_icon::make($editurl, 'fa-edit', 'edit');

            $deleteurl = paths::delete_notification($notification->uuid);
            $delete = action_icon::make($deleteurl, 'fa-trash', 'delete');
        }

        $userurl = new moodle_url('/user/profile.php', ['id' => $notification->useridfrom]);
        $name = fullname($notification);
        if ($notification->component != 'local_information_center') {
            $name = get_string('table:createdby:extern', 'local_information_center') . $notification->component . " ($name)";
        }
        $userlink = html_writer::link($userurl, $name);

        $this->add_data([
            $notification->subject,
            date('d.m.o', $notification->timestart),
            date('d.m.o', $notification->timeend),
            $userlink,
            get_string("category:$notification->categoryname", 'local_information_center'),
            $edit,
            $delete,
        ]);
    }
}
