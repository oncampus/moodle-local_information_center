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

use renderable;

class filter_renderer implements renderable {
    const PLUGIN_NAME = 'local_information_center';

    public function render(array $params = []): string {
        global $OUTPUT;
        return $OUTPUT->render_from_template(self::PLUGIN_NAME . '/filter_button', [
            'filtersform' => $params['form'],
        ]);
    }
}
