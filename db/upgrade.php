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

use core\di;

/**
 * Upgrade code for the information center
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  onCampus GmbH, 2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function xmldb_local_information_center_upgrade($oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026041400) {
        upgrade_fa_icons();
        upgrade_plugin_savepoint(true, 2026041400, 'local', 'information_center');
    }

    return true;
}

function upgrade_fa_icons(): void {
    $categories = [
        'infos' => 'fa-newspaper',
        'innovations' => 'fa-gear',
        'events' => 'fa-calendar-plus',
        'administrative' => 'fa-graduation-cap',
        'maintenance' => 'fa-heartbeat',
    ];

    $db = di::get(moodle_database::class);
    foreach ($categories as $name => $icon) {
        $db->set_field(
           'local_information_center_categories',
           'icon',
           $icon,
           ['name' => $name]
        );
    }
}
