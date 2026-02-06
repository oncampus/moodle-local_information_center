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
 * Defines services and external functions of this plugin
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>, Jonas Reuter <jonas.reuter@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_information_center\external\create_message;

$functions['local_information_center_create_message'] = [
    'classname'    => create_message::class,
    'methodname'   => 'execute',
    'description'  => 'Send a message to specified users.',
    'type'         => 'write',
    'ajax'         => false,
    'capabilities' => 'local/information_center:update_or_create_messages',
];

$services['Information center write API'] = [
    'functions' => [
        'local_information_center_create_message',
    ],
    'enabled' => 1,
    'restrictedusers' => 0,
    'shortname' => 'local_information_center_write_api',
];
