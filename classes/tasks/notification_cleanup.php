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

namespace local_information_center\tasks;

use coding_exception;
use core\clock;
use core\di;
use core\task\scheduled_task;
use dml_exception;
use moodle_database;

/**
 * Deletes all softdeleted messages older than 30 days
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  onCampus GmbH, 2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification_cleanup extends scheduled_task {
    /**
     * Returns the task name
     *
     * @return string task name
     * @throws coding_exception language string could not be fetched
     */
    public function get_name(): string {
        return get_string('task:message_cleanup', 'local_information_center');
    }

    /**
     * Delete all entries older than 30 days
     *
     * @throws dml_exception Could not reach the database
     */
    public function execute(): void {
        $deletetime = di::get(clock::class)->now()->modify('-30 days');
        $db = di::get(moodle_database::class);
        mtrace("Delete all entries before " . $deletetime->format("Y-m-d H:i:s"));

        $mids = $db->get_fieldset_select(
            'local_information_center_messages',
            'uuid',
            'timedeleted IS NOT NULL
            AND timedeleted <= :threshold',
            ['threshold' => $deletetime->getTimestamp()]
        );

        mtrace("Delete messages with ids " . implode(', ', $mids));

        [$messageinsql, $messageinparams] = $db->get_in_or_equal($mids, onemptyitems: true);
        $db->delete_records_select(
            'local_information_center',
            "messageuuid $messageinsql",
            $messageinparams
        );
        $db->delete_records_select(
            'local_information_center_messages',
            "uuid $messageinsql",
            $messageinparams
        );
    }
}
