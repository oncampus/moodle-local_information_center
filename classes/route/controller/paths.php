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

namespace local_information_center\route\controller;

use core\exception\moodle_exception;
use moodle_url;

/**
 * All paths of the plugin
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2026, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class paths {
    /** @var string Base URL of this plugin */
    public const BASE_URL = '/local_information_center';
    /** @var string Admin dashboard URL (Warning without base url) */
    public const ADMIN_DASHBOARD = '/notifications';
    /** @var string User dashboard URL (Warning without base url) */
    public const USER_DASHBOARD = '/inbox';

    /**
     * Get admin dashboard for managing notifications
     *
     * @return moodle_url URL
     * @throws moodle_exception
     */
    public static function admin_dashboard(): moodle_url {
        return new moodle_url(self::BASE_URL . self::ADMIN_DASHBOARD);
    }

    /**
     * Get notification inbox url
     *
     * @param string|null $component External or internal (by default external)
     * @return moodle_url URL
     * @throws moodle_exception
     */
    public static function user_dashboard(?string $component = null): moodle_url {
        $baseurl = self::BASE_URL . self::USER_DASHBOARD;
        if ($component) {
            $baseurl .= "/$component";
        }
        return new moodle_url($baseurl);
    }

    /**
     * Get edit / create notification url
     *
     * @param string|null $uuid UUID of notification, null for creation
     * @return moodle_url URL
     * @throws moodle_exception
     */
    public static function edit_notification(?string $uuid = null): moodle_url {
        $baseurl = self::BASE_URL . self::ADMIN_DASHBOARD . "/edit";
        if ($uuid) {
            $baseurl .= "/$uuid";
        }
        return new moodle_url($baseurl);
    }

    /**
     * Get delete notification url
     *
     * @param string $uuid UUID of notification
     * @return moodle_url URL
     * @throws moodle_exception
     */
    public static function delete_notification(string $uuid): moodle_url {
        $baseurl = self::BASE_URL . self::ADMIN_DASHBOARD . "/delete";
        if ($uuid) {
            $baseurl .= "/$uuid";
        }
        return new moodle_url($baseurl, ['sesskey' => sesskey()]);
    }
}
