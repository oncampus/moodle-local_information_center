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

namespace local_information_center\route\api\schemes;

use core\param;
use core\router\schema\example;
use core\router\schema\parameters\query_parameter;
use core\router\schema\referenced_object;

/**
 * Sesskey query parameter.
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2026, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sesskey_query_parameter extends query_parameter implements referenced_object {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            name: 'sesskey',
            type: param::ALPHANUMEXT,
            required: true,
            example: new example(
                'current',
                value: sesskey()
            )
        );
    }
}
