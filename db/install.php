<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Installation code for the information center
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  onCampus GmbH, 2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Prepares the categories of messages that exists
 */
function xmldb_local_information_center_install(): bool {
    global $DB;

    $categories = [
        [
            'name' => 'infos',
            'color' => '#17a2b8', // Türkis = Nachrichten/Infos.
            'priority' => 30,
            'icon' => 'fa fa-newspaper',
        ],
        [
            'name' => 'innovations',
            'color' => '#28a745', // Grün = Verbesserung.
            'priority' => 30,
            'icon' => 'fa fa-gear',
        ],
        [
            'name' => 'events',
            'color' => '#9b59b6', // Violett = Veranstaltungen.
            'priority' => 60,
            'icon' => 'fa-regular fa-calendar-plus',
        ],
        [
            'name' => 'administrative',
            'color' => '#dc3545', // Rot = wichtig/offiziell.
            'priority' => 80,
            'icon' => 'fa fa-graduation-cap',
        ],
        [
            'name' => 'maintenance',
            'color' => '#ff6600', // Orange = Warnung/Arbeiten.
            'priority' => 90,
            'icon' => 'fa fa-heartbeat',
        ],
    ];

    foreach ($categories as $category) {
        $DB->insert_record('local_information_center_categories', (object)$category);
    }

    return true;
}
