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

namespace local_information_center\notification\contracts;

/**
 * Prepares a request to call messages from the plugin
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification_query_data {
    /** @var string $select Fields to give back, where m is the message and c the category */
    public string $select = "m.*";

    /** @var string $order Fields to order the messages by */
    public string $order = "ORDER BY c.priority DESC, m.timestart DESC";

    /** @var null|int $userid Whether to filter for the access rights of a user */
    public ?int $userid = null;

    /** @var bool $filterbytime Whether to filter by the validity time of messages */
    public bool $filterbytime = false;

    /** @var null|int $category Whether to filter by the category */
    public ?int $category = null;

    /** @var null|int $limit If set limits the output to the number */
    public ?int $limit = null;

    /** @var null|int $offset Offset from the first value */
    public ?int $offset = null;

    /** @var null|string $titlesearch If set, gives only back messages that contain the search in the title */
    public ?string $titlesearch = null;

    /** @var bool|null If set, gives only messages back, that are internal | external */
    public ?bool $external = null;

    /** @var bool $notdeleted If true gives only back non deleted entries */
    public bool $notdeleted = false;
}
