Feature: Mapping of account information
  In order to provide custom account information from the server to the client
  As a user of the instance
  I want custom account data to be transferred from the server to an instance

  Background:
    Given There is a server user:
      | username  | jdoe                                             |
      | password  | jdoe1234                                         |
      | role      | Flowpack.SingleSignOn.DemoInstance:Administrator |
      | firstname | John                                             |
      | lastname  | Doe                                              |
      | company   | Acme                                             |
    And there is a mapping from server to instance users

  @fixtures
  Scenario: Server account information is mapped to client on authentication
    Given I am on the instance homepage
    When I log in to the secured page with "jdoe" and "jdoe1234"
    Then I should have a login name "John Doe"
