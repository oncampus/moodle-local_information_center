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

use core\output\renderer_base;
use renderable;
use templatable;

/**
 * UI Component for rendering the message filter area,
 * to search messages
 *
 * @author      Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright   2025, oncampus GmbH, <support@oncampus.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification_filter_area implements renderable, templatable {
    /** @var string HTML-Moodleform to render in the template */
    private string $form;

    /**
     * Constructor
     *
     * @param string $form HTML-Moodleform to render
     */
    public function __construct(string $form) {
        $this->form = $form;
    }

    /**
     * Export values for template
     *
     * @param renderer_base $output Renderer to use
     * @return string[] Template parameters
     */
    public function export_for_template(renderer_base $output): array {
        return [
            'filtersform' => $this->form,
        ];
    }
}
