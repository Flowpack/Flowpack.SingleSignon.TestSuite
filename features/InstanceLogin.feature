Feature: Instance Login with Single Sign-On
  In order to access a secured resource on an instance (some web application)
  As a user of the instance
  I need to be able to log in using my central user account on the SSO server

  Background:
    Given I am not authenticated on the server or the instance

  Scenario: Protected resource on instance redirects to server login
    Given I am on the instance homepage
    When I click on the link "Go to secure action"
    Then I should be redirected to the server
      And I should see a login form

  @wip
  Scenario: Login on server with correct credentials redirects to original URI
    Given I am on the instance homepage
      And I click on the link "Go to secure action"
    When I fill in "Username" with "admin"
      And I fill in "Password" with "password"
      And I press "Login"
    Then I should be redirected to the instance
      And the URI should not contain SSO parameters

  Scenario: Login forwards account information to instance
    Given I am on the instance homepage
      And I click on the link "Go to secure action"
    When I fill in "Username" with "admin"
      And I fill in "Password" with "password"
      And I press "Login"
    Then I should be redirected to the instance
      And I should be logged in as "admin"
      And I should have the role "Administrator"

  Scenario: Expired session on instance on callback redirect
    Given I am on the instance homepage
      And I click on the link "Go to secure action"
      And I wait so long that my session on the instance expires
    When I fill in "Username" with "admin"
      And I fill in "Password" with "password"
      And I press "Login"
    Then I should be redirected to the instance
      And I should be logged in as "admin"
      And I have the correct session cookie on the server

  Scenario: Protected resource with existing local session does not redirect to server
    Given I am on the instance homepage
      And I click on the link "Go to secure action"
    When I fill in "Username" with "admin"
      And I fill in "Password" with "password"
      And I press "Login"
      And I visit a protected resource
    Then I should not be redirected