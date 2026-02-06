@local @local_information_center @oncampus
Feature: Messages can be seen in the message overview.
  Checks if permissions are correctly handled.

  Background:
    Given the following "users" exist:
      | username |
      | manager  |
      | teacher  |
      | user     |
    And the following "system role assigns" exist:
      | user    | role    |
      | manager | manager |
    And the following "courses" exist:
      | shortname |
      | C1        |
    And the following "course enrolments" exist:
      | user    | course | role    |
      | teacher | C1     | teacher |

  Scenario Template: A simple external message get shown
    Given the following information center messages exist:
      | component | subject   | fullmessage  | fullmessageformat | categoryid | visibility   | useridfrom |
      | external  | mymessage | external msg | 1                 | 1          | <visibility> | 2          |
    And I log in as "<role>"
    And I am in the "external" Infocenter
    Then I should see "mymessage"

    Scenarios:
      | role    | visibility |
      | manager | manager    |
      | teacher | teacher    |
      | user    | student    |

  Scenario Template: The user do not have permissions to see the message
    Given the following information center messages exist:
      | component | subject   | fullmessage  | fullmessageformat | categoryid | visibility   | useridfrom |
      | external  | mymessage | external msg | 1                 | 1          | <visibility> | 2          |
    And I log in as "<role>"
    And I am in the "external" Infocenter
    Then I should not see "mymessage"

    Scenarios:
      | role    | visibility |
      | manager | admin      |
      | teacher | manager    |
      | user    | teacher    |
