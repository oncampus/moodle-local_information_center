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

namespace local_information_center\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use coding_exception;
use core\di;
use dml_exception;
use local_information_center\notification\contracts\NotificationCategory;
use local_information_center\notification\contracts\notification;
use local_information_center\notification\contracts\visibility;
use moodleform;

/**
 * Let the user creates notifications for local_information_center
 *
 * @author      Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright   2025, oncampus GmbH, <support@oncampus.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_notification_form extends moodleform {
    /**
     * Defines the fields:
     * - (Id)
     * - Title / Subject
     * - Notification (HTML Format without Files included)
     * - Category
     * - Visibility
     * - Startdate
     * - Enddate
     */
    public function definition(): void {
        // A reference to the form is stored in $this->form.
        // A common convention is to store it in a variable, such as `$mform`.
        $mform = $this->_form; // Don't forget the underscore!

        // Title.
        $mform->addElement('text', 'title', get_string('form:title', 'local_information_center'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required');

        // Notification.
        $editoropt = [
            'enable_filemanagement' => false,
            'maxfiles' => 0,
            'noclean' => false,
            'trusttext' => false,
        ];
        $mform->addElement('editor', 'message', get_string('form:message', 'local_information_center'), $editoropt);
        $mform->setType('message', PARAM_RAW);
        $mform->addRule('message', null, 'required');

        // Type.
        $mform->addElement('select', 'category', get_string('form:category', "local_information_center"), $this->get_categories());
        $mform->addHelpButton('category', 'form:category', 'local_information_center');

        // Target.
        $groups = $this->get_visibility();
        $mform->addElement('select', 'visibility', get_string('form:visibility', "local_information_center"), $groups);
        $mform->addHelpButton('visibility', 'form:visibility', 'local_information_center');

        // Date selectors.
        $mform->addElement('date_time_selector', 'startdate', get_string('table:startdate', 'local_information_center'));
        $mform->addElement('date_time_selector', 'enddate', get_string('table:enddate', 'local_information_center'));
        $mform->addHelpButton('enddate', 'table:enddate', 'local_information_center');

        // Schools.
        $id = $this->optional_param('id', false, PARAM_INT);
        if ($id === false || $id == -1) {
            $mform->addElement('hidden', 'renotify');
            $mform->setType('renotify', PARAM_INT);
            $mform->setDefault('renotify', 0);
        } else {
            $radioarray = [];
            $radioarray[] = $mform->createElement('radio', 'renotify', '', get_string('yes'), 1);
            $radioarray[] = $mform->createElement('radio', 'renotify', '', get_string('no'), 0);
            $mform->addGroup(
                $radioarray,
                'renotify_group',
                get_string('form:renotify', 'local_information_center'),
                ['<br />'],
                false
            );
        }

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', -1);

        // When ready, add your action buttons.
        $this->add_action_buttons(false, get_string('savechanges'));
    }

    /**
     * Get categories, that are allowed to be sent locally
     *
     * @return array List of notification type categories, as id => displayname
     * @throws dml_exception Database cannot be reached
     * @throws coding_exception Could not fetch language string
     */
    public static function get_categories(): array {
        $allowedcategories = ['infos', 'events', 'administrative'];
        $categorymanager = di::get(NotificationCategory::class);
        $categories = $categorymanager->get_all();

        $categoryselect = [];
        foreach ($categories as $category) {
            // Filter out categories not allowed for local sending.
            if (!in_array($category->name, $allowedcategories)) {
                continue;
            }

            $categoryselect[$category->id] = $category->out;
        }

        return $categoryselect;
    }

    /**
     * Formats the visibility for a selection menu.
     *
     * @return array [option => lang string]
     * @throws coding_exception Lang string could not be fetched
     */
    public static function get_visibility(): array {
        $menu = [];

        foreach (visibility::cases() as $case) {
            $menu[$case->value] = get_string('visibility:' . $case->value, 'local_information_center');
        }

        return $menu;
    }

    /**
     * Sets the data of a notification in this form
     *
     * @param notification $notification Existing notification data
     */
    public function set_notification_data(notification $notification): void {
        $this->set_data([
            'id' => $notification->id,
            'title' => $notification->subject,
            'message' => [
                'text' => $notification->fullmessage,
                'format' => $notification->fullmessageformat,
            ],
            'category' => $notification->categoryid,
            'visibility' => $notification->visibility,
            'startdate' => $notification->timestart,
            'enddate' => $notification->timeend,
        ]);
    }

    /**
     * Validates the times of the form
     *
     * @param array $data Parameters of the form
     * @param array $files Files of the form (not exist)
     * @return array Errors with the parameter as key
     * @throws coding_exception Error if lanuage string cannot be found
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if ($data['enddate'] <= $data['startdate']) {
            $errors['enddate'] = get_string('form:validation:date', 'local_information_center');
        }

        return $errors;
    }
}
