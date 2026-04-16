<?php

namespace local_information_center\route\controller;

use moodle_url;

class paths {
    const BASE_URL = '/local_information_center';
    const ADMIN_DASHBOARD = '/notifications';
    const USER_DASHBOARD = '/inbox';

    public static function admin_dashboard(): moodle_url {
        return new moodle_url(self::BASE_URL . self::ADMIN_DASHBOARD);
    }

    public static function user_dashboard(?string $component = null): moodle_url {
        $baseurl = self::BASE_URL . self::USER_DASHBOARD;
        if ($component) {
            $baseurl .= "/$component";
        }
        return new moodle_url($baseurl);
    }

    public static function edit_notification(?string $uuid = null): moodle_url {
        $baseurl = self::BASE_URL . self::ADMIN_DASHBOARD . "/edit";
        if ($uuid) {
            $baseurl .= "/$uuid";
        }
        return new moodle_url($baseurl);
    }

    public static function delete_notification(string $uuid): moodle_url {
        $baseurl = self::BASE_URL . self::ADMIN_DASHBOARD . "/delete";
        if ($uuid) {
            $baseurl .= "/$uuid";
        }
        return new moodle_url($baseurl, ['sesskey' => sesskey()]);
    }
}
