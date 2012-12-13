Feature: Global session expiration synchronization
  In order to have a consistent session experience on all instances
  As a user of an instance
  I want session expiration to be in sync for the server and all instances

  Scenario: Session expired on server, instance is expired on access to secured resource
    Given I am logged in to the secured page on the instance
     When The global session expires somehow
      And I visit the instance homepage
      And I wait for the global session touch interval
      And I click on the link "Go to secure action"
      And I visit the instance homepage
     Then I should not be authenticated

  Scenario: User is active on one instance, session is touched regularly on server
