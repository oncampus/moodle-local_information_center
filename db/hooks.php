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

/**
 * Defines the hooks for the plugin.
 *
 * Used to add the information center to the main navigation.
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  onCampus GmbH, 2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\hook\di_configuration;
use core\hook\navigation\primary_extend;
use local_information_center\local\hook_callbacks;


defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => primary_extend::class,
        'callback' => [hook_callbacks::class, 'extend_primary_navigation'],
    ],
    [
        'hook' => di_configuration::class,
        'callback' => [hook_callbacks::class, 'di_configuration'],
    ],
];
