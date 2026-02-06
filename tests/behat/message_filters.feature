@local @local_information_center @oncampus
Feature: Messages can be seen in the message overview.
  Checks if the messages can be filtered.

  Background:
    Given I log in as "admin"
    And the following information center messages exist:
      | component | subject    | fullmessage  | fullmessageformat | categoryid | visibility | useridfrom |
      | testing   | news       | internal msg | 1                 | 1          | student    | 2          |
      | testing   | events     | internal msg | 1                 | 3          | student    | 2          |
      | testing   | my_event   | internal msg | 1                 | 3          | student    | 2          |
    And I am in the "internal" Infocenter

  Scenario: A simple internal message get shown
    Given I click on "Infos" "link"
    Then I should see "news"

  Scenario: A simple internal message get shown
    Given I click on "Events" "link"
    And I should see "events"
    And I should see "my_event"
    When I set the field "query" to "my_event"
    And I click on "button[type='submit']" "css_element"
    Then I should see "my_event"
    And I should not see "events"
