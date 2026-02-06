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

namespace local_information_center\form;

use core\di;
use core\output\html_writer;
use flexible_table;
use moodle_database;
use moodle_url;
use stdClass;

class message_table extends flexible_table implements filterable_table {
    /** @var string EDIT_LINK Link to the edit page for messages */
    const EDIT_LINK = '/local/information_center/pages/edit_notification.php';
    /** @var string EDIT_LINK Link to the deletion page for messages */
    const DELETE_LINK = '/local/information_center/pages/delete_notification.php';

    private array $filters = [];
    private array $filterparams = [];

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

    public function add_filter(
        $field,
        $value,
        $comparator = "="
    ): void {
        if ($value === null) {
            $this->filters[] = "$field $comparator";
        } else {
            $this->filters[] = "$field $comparator ?";
            $this->filterparams[] = $value;
        }
    }

    public function get_data(): array {
        $messageorder = "";
        if ($order = $this->get_sql_sort()) {
            $messageorder = "ORDER BY $order";
        }

        $messagefilters = "";
        if (!empty($this->filters)) {
            $messagefilters .= " WHERE ";
            $messagefilters .= implode(' AND ', $this->filters);
        }

        $sql = ("
            SELECT m.id AS id,
                   m.subject AS subject,
                   m.timestart AS timestart,
                   m.timeend AS timeend,
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
            $messagefilters
            $messageorder
        ");

        $db = di::get(moodle_database::class);
        return $db->get_records_sql($sql, $this->filterparams);
    }

    public function add_message_data(stdClass $message): void {
        global $USER;

        $edit = "";
        $delete = "";
        if ($message->useridfrom == $USER->id && $message->timedeleted === null) {
            $msgid = $message->id;

            $editurl = new moodle_url(self::EDIT_LINK, ['id' => $msgid]);
            $edit = action_icon::make($editurl, 'fa-edit', 'edit');

            $deleteurl = new moodle_url(self::DELETE_LINK, ['id' => $msgid, 'sesskey' => sesskey()]);
            $delete = action_icon::make($deleteurl, 'fa-trash', 'delete');
        }

        $userurl = new moodle_url('/user/profile.php', ['id' => $message->useridfrom]);
        $name = fullname($message);
        if ($message->useridfrom == 2) {
            $name = get_string('table:createdby:extern', 'local_information_center') . " ($name)";
        }
        $userlink = html_writer::link($userurl, $name);

        $this->add_data([
            $message->subject,
            date('d.m.o', $message->timestart),
            date('d.m.o', $message->timeend),
            $userlink,
            get_string("category:$message->categoryname", 'local_information_center'),
            $edit,
            $delete,
        ]);
    }
}
