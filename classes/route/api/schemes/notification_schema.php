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
use core\router\schema\objects\scalar_type;
use core\router\schema\objects\schema_object;

class notification_schema extends schema_object {
    public function __construct() {
        parent::__construct(
            content: [
                'categoryid' => new scalar_type(param::INT, true),
                'fullmessage' => new scalar_type(param::RAW, true),
                'fullmessageformat' => new scalar_type(param::INT, true),
                'smallmessage' => new scalar_type(param::RAW, true),
                'visibility' => new scalar_type(param::TEXT, true),
                'subject' => new scalar_type(param::TEXT, true),
                'timestart' => new scalar_type(param::INT),
                'timeend' => new scalar_type(param::INT),
                'timedeleted' => new scalar_type(param::INT),
            ],
        );
    }
}
