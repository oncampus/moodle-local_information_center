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

namespace local_information_center\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

use coding_exception;
use core\di;
use dml_exception;
use local_information_center\notification\contracts\NotificationCategory;
use moodleform;

/**
 * Defines all filters can be applied to search messages
 * in a corresponding form
 *
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2025, oncampus GmbH, <support@oncampus.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification_filter_form extends moodleform {
    /** @var string Name of this plugin */
    private const PLUGIN_NAME = 'local_information_center';

    /**
     * Form definition
     *
     * @throws coding_exception Error loading the config or cache
     * @throws dml_exception Database Connection Error
     */
    public function definition(): void {
        // A reference to the form is stored in $this->form.
        // A common convention is to store it in a variable, such as `$mform`.
        $mform = $this->_form; // Don't forget the underscore!

        $timeoptions = [
            '' => get_string('form:any', self::PLUGIN_NAME),
            '<' => get_string('form:before', self::PLUGIN_NAME),
            '>' => get_string('form:after', self::PLUGIN_NAME),
        ];

        $textoptions = [
            '' => get_string('form:any', self::PLUGIN_NAME),
            'contains' => get_string('form:contains', self::PLUGIN_NAME),
            'eq' => get_string('form:equals', self::PLUGIN_NAME),
        ];

        $selectoptions = [
            '' => get_string('form:any', self::PLUGIN_NAME),
            'eq' => get_string('form:equals', self::PLUGIN_NAME),
        ];

        $mform->addElement('select', 'subject_ftype', get_string('form:title', self::PLUGIN_NAME), $textoptions);
        $mform->addElement('text', 'subject');
        $mform->setType('subject', PARAM_TEXT);
        $mform->hideIf('subject', 'subject_ftype', 'eq', '');

        $mform->addElement('select', 'timestart_ftype', get_string('table:startdate', self::PLUGIN_NAME), $timeoptions);
        $mform->addElement('date_selector', 'timestart');
        $mform->hideIf('timestart', 'timestart_ftype', 'eq', '');

        $mform->addElement('select', 'timeend_ftype', get_string('table:enddate', self::PLUGIN_NAME), $timeoptions);
        $mform->addElement('date_selector', 'timeend');
        $mform->hideIf('timeend', 'timeend_ftype', 'eq', '');

        $mform->addElement('select', 'firstname_ftype', get_string('form:firstname', self::PLUGIN_NAME), $textoptions);
        $mform->addElement('text', 'firstname');
        $mform->setType('firstname', PARAM_TEXT);
        $mform->hideIf('firstname', 'firstname_ftype', 'eq', '');

        $mform->addElement('select', 'lastname_ftype', get_string('form:lastname', self::PLUGIN_NAME), $textoptions);
        $mform->addElement('text', 'lastname');
        $mform->setType('lastname', PARAM_TEXT);
        $mform->hideIf('lastname', 'lastname_ftype', 'eq', '');

        $mform->addElement('select', 'categoryid_ftype', get_string('form:categoryname', self::PLUGIN_NAME), $selectoptions);

        $categorymanager = di::get(NotificationCategory::class);
        $categories = $categorymanager->get_options();
        $mform->addElement('select', 'categoryid', '', $categories);
        $mform->hideIf('categoryid', 'categoryid_ftype', 'eq', '');

        $mform->addElement('hidden', 'timedeleted_ftype');
        $mform->setDefault('timedeleted_ftype', 'isset');
        $mform->setType('timedeleted_ftype', PARAM_TEXT);
        $mform->addElement('checkbox', 'timedeleted', get_string('form:deleted', self::PLUGIN_NAME));
        $mform->setDefault('timedeleted', 0);

        $this->add_action_buttons(false);
    }

    /**
     * Sets the configured filters from this from to a filterable table
     *
     * @param FilterableTable $table Apply the filters to this table
     * @return void
     */
    public function set_filters(FilterableTable $table): void {
        $data = (array) $this->get_data();
        if (!array_key_exists('timedeleted', $data)) {
            $data['timedeleted_ftype'] = 'isset';
            $data['timedeleted'] = 0;
        }

        foreach ($data as $key => $value) {
            if (str_ends_with($key, 'ftype') || $key == 'submitbutton') {
                continue;
            }

            $comparator = $data["{$key}_ftype"];
            switch ($comparator) {
                case '':
                    break;
                case '<':
                case '>':
                    $table->add_filter($key, $value, $comparator);
                    break;
                case 'contains':
                    $table->add_filter($key, "%$value%", "LIKE");
                    break;
                case 'eq':
                    $table->add_filter($key, "$value");
                    break;
                case 'isset':
                    if ($value) {
                        $table->add_filter($key, null, 'IS NOT NULL');
                    } else {
                        $table->add_filter($key, null, 'IS NULL');
                    }
                    break;
            }
        }
    }
}
