@local @local_culrollover
Feature: <Name>
    In order to test ...
    As a teacher
    I need to ...

  Background:
    Given the following "courses" exist:
        | fullname | shortname | category | groupmode |
        | Course 1 | C1 | 0 | 1 |
        | Course 2 | C2 | 0 | 2 |
    And the following "users" exist:
        | username | firstname | lastname | email |
        | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "course enrolments" exist:
        | user | course | role |
        | teacher1 | C1 | editingteacher |
        | teacher1 | C2 | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Assignment" to section "1" and I fill the form with:
        | Assignment name | Ass 1 in Course 1 |
        | Description | Submit your work |
    And I add a "Assignment" to section "1" and I fill the form with:
        | Assignment name | Ass 2 in Course 1 |
        | Description | Submit your work |
    And I am on "Course 2" course homepage
    And I add a "Assignment" to section "1" and I fill the form with:
        | Assignment name | Ass 3 in Course 2 |
        | Description | Submit your work |
    And I log out

  @javascript
  Scenario: Set up a rollover of Course 1 to Course 2
    Given I log in as "teacher1"
    And I am on the "local_culrollover > Rollover tool" page
    Then I should see "Module Rollover Tool 'make it break'"
