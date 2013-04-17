Feature: Server Login with Single Sign-On
  In order to also authenticate on the server and seamlessly use an instance
  As a user of the server
  I need to be able to log in on the SSO server and be authenticated on instances

  Scenario: User logs in on server and is authenticated on instance
    Given I am not authenticated on the server or the instance
     When I visit the server homepage
      And I follow "Login now"
      And I log in with "admin" and "password"
     Then I should be on the server homepage
      And I should be logged in as "admin"
     When I am on the instance homepage
      And I visit a protected resource
     Then I should be logged in as "admin"

  Scenario: User changes login on server and is authenticated on instance with new account
    Given I am not authenticated on the server or the instance
     When I visit the server homepage
      And I follow "Login now"
      And I log in with "user1" and "password"
     Then I should be logged in as "user1"
     When I am on the instance homepage
      And I visit a protected resource
     Then I should be logged in as "user1"
     When I visit the server homepage
      And I follow "Login again"
      And I log in with "admin" and "password"
     Then I should be logged in as "admin"
     When I am on the instance homepage
      And I visit a protected resource
     Then I should be logged in as "admin"
