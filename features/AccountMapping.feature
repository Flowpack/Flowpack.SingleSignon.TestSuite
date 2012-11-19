Feature: Mapping of account information
  In order to provide custom account information from the server to the client
  As a user of the instance
  I want custom account data to be transferred from the server to an instance

  Background:
    Given There is a server account:
      | identifier | admin         |
      | password   | password      |
      | roles      | Administrator |
      | firstname  | John          |
      | lastname   | Doe           |
      And There is a mapping for the party name

  Scenario: Server account information is mapped to client on authentication
    Given I am on the instance homepage
    When I log in to the secured page
    Then I should have a login name "John Doe"
