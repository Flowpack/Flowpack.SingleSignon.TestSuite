Feature: Global logout synchronization
  In order to have better security when logging out
  As a user of an instance
  I want the logout to be synchronized between all instances

  Scenario: Logout in instance
    Given I am on the instance homepage
      And I am logged in to the secured page
     When I click on the link "Logout"
     Then I should be redirected to the instance
      And I should not be authenticated

  Scenario: User logs out in instance and is logged out on server
    Given I am on the instance homepage
      And I am logged in to the secured page
     When I click on the link "Logout"
      And I visit the server homepage
     Then I should not be authenticated

  Scenario: User logs out in instance and needs to log in for secured resource
    Given I am on the instance homepage
      And I am logged in to the secured page
     When I click on the link "Logout"
      And I click on the link "Go to secure action"
     Then I should see a login form

  Scenario: User logs out from server and is logged out in instance
    Given I am on the instance homepage
      And I am logged in to the secured page
     When I visit the server homepage
      And I click on the link "Logout"
      And I visit the instance homepage
     Then I should not be authenticated

  Scenario: User logs out from one instance and is logged out on other instance
    Given I am logged in to the secured page on instance1
      And I am logged in to the secured page on instance2
     When I visit the instance1 homepage
      And I click on the link "Logout"
     Then I should not be authenticated on instance1
      And I should not be authenticated on instance2
