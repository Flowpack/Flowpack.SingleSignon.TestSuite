Feature: Single-Sign-On
  In order to access a secured resource on a web application (instance)
  As an instance user
  I need to be able to log in using my central user account

  Background:
    Given I am not authenticated on the server or the instance

  Scenario: Redirect to server login for authentication
    Given I am on the instance homepage
    When I click on the link "Go to secure action"
    Then I should be redirected to the server
      And I should see a login form

  Scenario: Login on server with correct credentials redirects to original instance URL
    Given I am on the instance homepage
      And I click on the link "Go to secure action"
    When I fill in "Username" with "admin"
      And I fill in "Password" with "password"
      And I press "Login"
    Then I should be redirected to the instance
      And I should see "Success, it's secure"
