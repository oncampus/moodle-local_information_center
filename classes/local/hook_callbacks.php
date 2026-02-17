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

namespace local_information_center\local;

use core\clock;
use core\di;
use core\hook\di_configuration;
use core\hook\navigation\primary_extend;
use core\output\html_writer;
use local_information_center\message_handle\contracts\i_message_category;
use local_information_center\message_handle\contracts\i_message_manager;
use local_information_center\message_handle\contracts\i_message_read;
use local_information_center\message_handle\message_category;
use local_information_center\message_handle\message_manager;
use local_information_center\message_handle\message_read;
use moodle_database;
use moodle_url;
use navigation_node;

/**
 * Hook callbacks for the plugin local_information_center
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Defined DI Configurations
     *
     * @param di_configuration $config DI Hook
     * @return void
     */
    public static function di_configuration(di_configuration $config): void {
        $config->add_definition(
            id: i_message_read::class,
            definition: function (
                moodle_database $db,
                clock $clock,
            ): i_message_read {
                return new message_read($db, $clock);
            }
        );

        $config->add_definition(
            id: i_message_category::class,
            definition: function (
                moodle_database $db,
            ): i_message_category {
                return new message_category($db);
            }
        );

        $config->add_definition(
            id: i_message_manager::class,
            definition: function (
                clock $clock,
                moodle_database $db,
            ): i_message_manager {
                return new message_manager($clock, $db);
            }
        );
    }

    /**
     * Adds a tab to the main navigation to come to the information center
     *
     * @param primary_extend $hook the primary_extend hook object
     */
    public static function extend_primary_navigation(primary_extend $hook): void {
        global $USER;

        // Data: unread count for current user.
        $readmng = di::get(i_message_read::class);
        $unread = $readmng->count_unread($USER->id);

        // Badge (only when there are unread items).
        $badge = '';
        if ($unread > 0) {
            $badge = html_writer::span(
                $unread,
                'infocenter-count-container',
                ['aria-label' => get_string('navigationnode:unreadcount', 'local_information_center', $unread)]
            );
        }

        // Bell icon wrapped so we can absolutely-position the badge relative to it.
        $icon = html_writer::tag('i', '', ['class' => 'ml-1 fa-solid fa-bell', 'aria-hidden' => 'true']);
        $iconwithbadge = html_writer::span($icon . $badge, 'position-relative d-inline-block');

        // Final label text.
        $label = get_string('overview:title', 'local_information_center') . $iconwithbadge;

        // Target URL.
        $url = new moodle_url('/local/information_center/pages/overview.php');

        // Create and add node.
        $node = navigation_node::create($label, $url);
        $hook->get_primaryview()->add_node($node);
    }
}
