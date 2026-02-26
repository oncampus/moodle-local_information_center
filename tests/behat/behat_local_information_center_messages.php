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

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use core\di;
use local_information_center\notification\contracts\NotificationManager;
use local_information_center\notification\contracts\notification;

/**
 * Creates, edits or deletes messages for behat tests
 *
 * @author     Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright  2025, onCampus GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_information_center_messages extends behat_base {
    /**
     * Creates messages from an list, each message needs the following infos:
     * - categoryid
     * - fullmessage
     * - fullmessageformat
     * - subject
     * - component
     *
     * @Given /^the following information center messages exist:$/
     * @param TableNode $table
     * @throws ExpectationException
     * @throws dml_exception
     */
    public function given_the_following_messages_exist(TableNode $table): void {
        foreach ($table as $row) {
            $message = notification::from_stdClass((object) $row);
            $result = di::get(NotificationManager::class)->add_or_update($message);

            if ($result === false) {
                throw new ExpectationException("Could not update message with id " . $row['id'], $this->getSession());
            }

            if (!is_int($result)) {
                $validationerrors = var_export($result, true);
                throw new ExpectationException("Could not create/update message: " . $validationerrors, $this->getSession());
            }
        }
    }

    /**
     * Goto the Infocenter page
     *
     * @When /^I am in the "(?P<component>internal|external)" Infocenter/
     * @param string $component Defines the component internal or external
     */
    public function when_i_am_on_the_infocenter_page(string $component): void {
        $url = new moodle_url("/local/information_center/pages/user_notification_dashboard.php", ['component' => $component]);
        $this->getSession()->visit($this->locate_path($url->out()));
    }
}
