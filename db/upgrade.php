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
use core\uuid;

/**
 * Upgrade code for the information center
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  onCampus GmbH, 2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Updates the plugin to newer versions
 *
 * @param int $oldversion Current plugin version
 * @return bool True on success
 * @throws downgrade_exception
 * @throws moodle_exception
 * @throws upgrade_exception
 */
function xmldb_local_information_center_upgrade($oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026041400) {
        upgrade_fa_icons();
        upgrade_plugin_savepoint(true, 2026041400, 'local', 'information_center');
    }

    if ($oldversion < 2026041600) {
        add_uuid_to_notifications($dbman);
        upgrade_plugin_savepoint(true, 2026041600, 'local', 'information_center');
    }

    if ($oldversion < 2026041601) {
        adapt_is_read_table_to_uuid($dbman);
        upgrade_plugin_savepoint(true, 2026041601, 'local', 'information_center');
    }

    return true;
}

/**
 * Replaces the Notification-ID in the read table by the Notification-UUID
 *
 * @param database_manager $dbman
 * @return void
 * @throws ddl_exception
 * @throws ddl_field_missing_exception
 * @throws ddl_table_missing_exception
 * @throws dml_exception
 */
function adapt_is_read_table_to_uuid(database_manager $dbman): void {
    $table = new xmldb_table('local_information_center');

    $field = new xmldb_field(
        'messageuuid',
        XMLDB_TYPE_CHAR,
        '36',
        null,
        null,
        null,
        null
    );

    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    $db = di::get(moodle_database::class);
    $readstatuslist = $db->get_records(
        'local_information_center',
        fields: 'id, messageid'
    );

    $translationlist = $db->get_records_menu(
        'local_information_center_messages',
        fields: 'id, uuid'
    );
    foreach ($readstatuslist as $readstatus) {
        $readstatus->messageuuid = $translationlist[$readstatus->messageid] ?? null;
        if ($readstatus->messageuuid) {
            $db->update_record('local_information_center', $readstatus);
        } else {
            $db->delete_records('local_information_center', ['id' => $readstatus->id]);
        }
    }

    $index = new xmldb_index(
        'mes_ix',
        XMLDB_INDEX_NOTUNIQUE,
        ['messageid']
    );

    if ($dbman->index_exists($table, $index)) {
        $dbman->drop_index($table, $index);
    }

    $oldfield = new xmldb_field('messageid');
    if ($dbman->field_exists($table, $oldfield)) {
        $dbman->drop_field($table, $oldfield);
    }
}

/**
 * Replaces external IDs by notification UUIDs
 *
 * @param database_manager $dbman
 * @return void
 * @throws ddl_exception
 * @throws ddl_table_missing_exception
 * @throws dml_exception
 */
function add_uuid_to_notifications(database_manager $dbman): void {
    $db = di::get(moodle_database::class);

    // Add uuid field.
    $table = new xmldb_table('local_information_center_messages');
    $field = new xmldb_field(
        'uuid',
        XMLDB_TYPE_CHAR,
        '36',
        null,
        XMLDB_NOTNULL,
        null,
        'NOT_INITIALIZED'
    );
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    // Migrate old external ids into uuid field.
    $oldtable = new xmldb_table('local_information_center_external_ids');
    if ($dbman->table_exists($oldtable)) {
        $externalids = $db->get_records_menu(
            'local_information_center_external_ids',
            fields: "messageid, externalid"
        );
        $notifications = $db->get_fieldset(
            'local_information_center_messages',
            'id'
        );

        foreach ($notifications as $notificationid) {
            $db->update_record(
                'local_information_center_messages',
                [
                    'id' => $notificationid,
                    'uuid' => $externalids[$notificationid] ?? uuid::generate(),
                ]
            );
        }

        $dbman->drop_table($oldtable);
    }
}

/**
 * Replaces the font awesome icons by new icons.
 *
 * @return void
 * @throws dml_exception
 */
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
