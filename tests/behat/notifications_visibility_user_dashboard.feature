@local @local_information_center @oncampus
Feature: Notifications are restricted to specific user roles.

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

  Scenario Template: Notifications for equal or lower roles are visible
    Given the following information center messages exist:
      | subject   | fullmessage  | categoryid | visibility   | useridfrom |
      | mymessage | external msg | 1          | <visibility> | 2          |
    And I log in as "<role>"
    And I am in the "internal" Infocenter
    Then I should see "mymessage"

    Scenarios:
      | role    | visibility |
      | manager | manager    |
      | teacher | teacher    |
      | user    | student    |

  Scenario Template: Notifications for higher roles are not visible
    Given the following information center messages exist:
      | subject   | fullmessage  | categoryid | visibility   | useridfrom |
      | mymessage | external msg | 1          | <visibility> | 2          |
    And I log in as "<role>"
    And I am in the "internal" Infocenter
    Then I should not see "mymessage"

    Scenarios:
      | role    | visibility |
      | manager | admin      |
      | teacher | manager    |
      | user    | teacher    |
