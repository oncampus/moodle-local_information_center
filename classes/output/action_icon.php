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

use core\output\html_writer;
use moodle_url;

/**
 * Creates an action item html component
 *
 * @author      Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright   2025, oncampus GmbH, <support@oncampus.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class action_icon {
    /**
     * Helper functions to show icons in the settings menu (delete and edit)
     *
     * @param moodle_url $url URL to do the action
     * @param string $icon Icon class to render (see font awesome or other supported libs)
     * @param string $name Name of the icon, to get the title (action:$name)
     * @return string String to render the icon
     */
    public static function make(moodle_url $url, string $icon, string $name): string {
        $title = get_string("action:$name", 'local_information_center');

        return html_writer::link(
            $url,
            html_writer::tag(
                'i',
                '',
                ['class' => "icon fa-solid $icon", 'title' => $title, 'role' => 'img']
            )
        );
    }
}
