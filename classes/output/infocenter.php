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

use coding_exception;
use context;
use context_system;
use core\clock;
use core\di;
use core\output\renderer_base;
use dml_exception;
use Exception;
use local_information_center\notification\contracts\notification;
use local_information_center\notification\contracts\NotificationCategory;
use local_information_center\notification\contracts\NotificationManager;
use local_information_center\notification\contracts\NotificationsRead;
use local_information_center\notification\contracts\notification_query_data;
use moodle_url;
use renderable;
use stdClass;
use templatable;
use ValueError;

/**
 * Displays the messages for users in the Infocenter
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH <support@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class infocenter implements renderable, templatable {
    /** @var NotificationManager Message manager */
    private NotificationManager $messagemanager;
    /** @var moodle_url Base url for the page */
    private moodle_url $url;
    /** @var moodle_url Notification managing url */
    private moodle_url $manageurl;
    /** @var string|null Text input of the user search */
    private ?string $search;
    /** @var int Page, that should be rendered */
    private int $page;
    /** @var int Maximum messages per page */
    private int $pagesize;
    /** @var int|null Message category to filter for */
    private ?int $category;
    /** @var notification_query_data Search request object */
    private notification_query_data $searchrequest;
    /** @var string Message component to filter for */
    private string $component;

    /**
     * Constructor
     *
     * @param moodle_url $url Pages base url
     * @param moodle_url $manageurl Notification managing url
     * @param string $component Whether to search for 'external' or 'internal' messages
     * @param string|null $search User input in search field
     * @param int $page Page ID
     * @param int|null $category Category to search for, or null if filter not active
     * @param int $pagesize Messages per page
     */
    public function __construct(
        moodle_url $url,
        moodle_url $manageurl,
        string $component,
        ?string $search = null,
        int $page = 0,
        ?int $category = null,
        int $pagesize = 10,
    ) {
        global $USER;

        if ($page < 0) {
            throw new ValueError("Cannot open negative pages");
        }

        $this->messagemanager = di::get(NotificationManager::class);
        $this->url = $url;
        $this->search = $search;
        $this->page = $page;
        $this->category = $category;
        $this->pagesize = $pagesize;
        $this->component = $component;
        $this->manageurl = $manageurl;

        $searchrequest = new notification_query_data();
        $searchrequest->userid = $USER->id;
        $searchrequest->titlesearch = $search;
        $searchrequest->category = $category;
        $searchrequest->offset = $page * $pagesize;
        $searchrequest->limit = $pagesize;
        $searchrequest->filterbytime = true;
        $searchrequest->notdeleted = true;
        $searchrequest->external = $component == 'external';
        $this->searchrequest = $searchrequest;
    }

    /**
     * Export the message overview
     *
     * @param renderer_base $output Base renderer
     * @return array Exported parameters [paging, categories, messages]
     * @throws coding_exception Language strings could not be fetched
     * @throws dml_exception Database connection failed
     */
    public function export_for_template(renderer_base $output): array {
        $notifications = $this->messagemanager->get_with_request($this->searchrequest);
        $notificationsout = array_map(fn ($msg) => self::export_notification($msg), $notifications);
        $notificationsout = array_values($notificationsout);

        $pagination = $this->prepare_paging($this->page, $this->url);

        $context = context_system::instance();
        if (!$context instanceof context) {
            throw new Exception("Cannot load system context");
        }

        $hasmanagecap = has_capability('local/information_center:update_or_create_messages', $context);
        $shortcuttarget = $hasmanagecap ? $this->manageurl : null;

        return [
                'categories' => $this->prepare_categories($this->url),
                'notifications' => $notificationsout,
                'shortcuturl' => $shortcuttarget?->out(false),
                'search' => $this->search,
                'component' => $this->component,
                'category' => $this->category,
                'pagination' => $pagination,
            ];
    }

    /**
     * Exports one notification for being output in the info center
     *
     * @param notification $notification Notification
     * @return array Array of parameters for the template
     * @throws coding_exception Failed to fetch lang string
     */
    private static function export_notification(notification $notification): array {
        global $USER;

        $secondsago = di::get(clock::class)->time() - $notification->get_time_visible();

        $readmng = di::get(NotificationsRead::class);
        $isread = $readmng->is_read($notification->uuid, $USER->id);

        $categorymng = di::get(NotificationCategory::class);
        $category = $categorymng->get($notification->categoryid);

        $data = [
            'title' => clean_param($notification->subject, PARAM_TEXT),
            'message' => $notification->get_message_body(),
            'sended_time_ago' => get_string('ago', 'message', format_time($secondsago)),
            'unreadmarker' => !$isread,
            'iconbgcolor' => $category->color,
            'icon' => $category->get_icon(),
        ];

        if (!$isread) {
            $readmng->set_read($notification->uuid, $USER->id);
        }
        return $data;
    }

    /**
     * Exports the paging parameters for the template
     *
     * @param int $currentpage Current page we are on
     * @param moodle_url $url Url we are currently on
     * @return string Rendered html for pagination
     */
    private function prepare_paging(int $currentpage, moodle_url $url): string {
        global $OUTPUT;
        $msgcount = $this->messagemanager->count_with_request($this->searchrequest);

        return $OUTPUT->paging_bar(
            $msgcount,
            $currentpage,
            $this->pagesize,
            $url
        );
    }

    /**
     * Exports the categories for the template
     *
     * @param moodle_url $url URL we are currently on
     * @return array Exported parameters
     * @throws coding_exception Categories could not be fetched
     * @throws dml_exception Categories could not be fetched
     */
    private function prepare_categories(moodle_url $url): array {
        $usedcategories = $this->messagemanager->get_categories_with_messages(
            $this->searchrequest->external ? 'external' : 'local_information_center',
        );

        $output = [];

        $alllink = new moodle_url($url);
        $alllink->remove_params('category');
        $output[] = [
            'name' => get_string('category:all', 'local_information_center'),
            'disabled' => !$this->category,
            'link' => $alllink->out(false),
        ];

        $categorymanager = di::get(NotificationCategory::class);
        foreach ($usedcategories as $categoryid) {
            $category = $categorymanager->get($categoryid);
            if (!$category) {
                continue;
            }

            $link = new moodle_url($url);
            $link->param('category', $category->id);

            $output[] = [
                'name' => $category->get_label(),
                'disabled' => $category->id == $this->category,
                'link' => $link->out(false),
            ];
        }

        return $output;
    }
}
