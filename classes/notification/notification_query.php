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

namespace local_information_center\notification;

use coding_exception;
use core\clock;
use core\di;
use dml_exception;
use local_information_center\notification\contracts\notification_query_data;
use local_information_center\notification\contracts\visibility;

/**
 * Query object for calling messages
 *
 * @author      Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright   2025, oncampus GmbH, <support@oncampus.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification_query {
    /** @var notification_query_data Query data object */
    private notification_query_data $data;

    /**
     * Constructor.
     *
     * @param notification_query_data $data Query data object
     */
    public function __construct(
        notification_query_data $data
    ) {
        $this->data = $data;
    }

    /**
     * Returns the request sql
     *
     * @return array [sql, params]
     * @throws coding_exception Get in or equals failed
     * @throws dml_exception Database cannot be reached
     */
    public function get_sql(): array {
        $filters = $this->get_filters();
        $paging = $this->get_paging();

        $sql = "SELECT {$this->data->select}
                  FROM {local_information_center_messages} m
             LEFT JOIN {local_information_center_categories} c
                    ON m.categoryid = c.id
                 WHERE $filters[0]
                       {$this->data->order}
                       $paging[0]";

        return [$sql, $filters[1] + $paging[1]];
    }

    /**
     * Returns the where part for the sql request
     *
     * @return array [sql, params]
     * @throws coding_exception Get in or equal fails
     * @throws dml_exception Database cannot be reached
     */
    private function get_filters(): array {
        global $DB;

        $now = di::get(clock::class)->time();

        $params = [
            'category' => $this->data->category,
            'now' => $now,
            'now2' => $now,
            'userid' => $this->data->userid,
        ];

        $sqlparts = [];

        if ($params['userid'] !== null) {
            $rights = array_map(fn ($vis) => $vis->value, visibility::get_users_visibility($this->data->userid));
            $rightssql = $DB->get_in_or_equal($rights, SQL_PARAMS_NAMED, 'rights', onemptyitems: true);

            $sqlparts[] = "(m.visibility $rightssql[0] OR m.useridfrom = :userid)";
            $params += $rightssql[1];
        }

        if ($this->data->filterbytime) {
            $sqlparts[] = "(m.timestart <= :now OR m.timestart IS NULL)
                       AND (m.timeend >= :now2 OR m.timeend IS NULL)";
        }

        if ($params['category']) {
            $sqlparts[] = "c.id = :category";
        }

        if ($this->data->titlesearch !== null) {
            $sqlparts[] = $DB->sql_like('m.subject', ':search', false, false);
            $params['search'] = "%{$this->data->titlesearch}%";
        }

        if ($this->data->notdeleted) {
            $sqlparts[] = "m.timedeleted IS NULL";
        }

        if ($this->data->external !== null) {
            $sqlparts[] = $DB->sql_like('m.component', ':component', false, false, notlike: !$this->data->external);
            $params['component'] = 'external';
        }

        if (empty($sqlparts)) {
            return ["1", []];
        }

        return [implode(" AND ", $sqlparts), $params];
    }

    /**
     * Returns the limit and offset part for sql request
     *
     * @return array [sql, params]
     */
    private function get_paging(): array {
        $params = [
            'limit' => $this->data->limit,
            'offset' => $this->data->offset,
        ];

        $limitsql = '';
        if ($params['limit'] !== null) {
            $limitsql = 'LIMIT :limit';
        }
        if ($params['offset'] !== null) {
            $limitsql .= ' OFFSET :offset';
        }

        return [$limitsql, $params];
    }
}
