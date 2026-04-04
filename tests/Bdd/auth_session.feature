# language: en
@auth @session
Feature: API authenticated session (me, logout)
  As an authenticated client
  I want to read my profile and revoke tokens

  Background:
    Given the API accepts JSON at the "/api" prefix
    And the auth routes are under "/api/auth"

  @priority_high
  Scenario: Me with valid Bearer token returns user resource
    Given a registered verified user "me@example.com" with password "Password1" and an active API token
    When I send "GET" to "/api/auth/me" with header "Authorization: Bearer <token>"
    Then the response status should be 200
    And the response JSON should include message "User authenticated"
    And the response JSON path "data.user.email" should equal "me@example.com"
    And the response JSON path "data.user" should include keys "email_verified_at" and "has_verified_email"
    And the response JSON path "data.user.has_verified_email" should be true

  @priority_high
  Scenario: Me without token returns 401
    When I send "GET" to "/api/auth/me" without an Authorization header
    Then the response status should be 401

  @priority_high
  Scenario: Me with invalid Bearer token returns 401
    When I send "GET" to "/api/auth/me" with header "Authorization: Bearer invalid-token"
    Then the response status should be 401

  @priority_medium
  Scenario: Logout invalidates current token
    Given a registered verified user "out@example.com" with password "Password1" and an active API token
    When I send "POST" to "/api/auth/logout" with header "Authorization: Bearer <token>"
    Then the response status should be 200
    And the response JSON should include message "Logout successful"
    When I send "GET" to "/api/auth/me" with the same Bearer token
    Then the response status should be 401

  @priority_medium
  Scenario: Logout all revokes every token for the user
    Given a registered verified user "allout@example.com" with password "Password1" and two active API tokens
    When I send "POST" to "/api/auth/logout-all" with header "Authorization: Bearer <first_token>"
    Then the response status should be 200
    And the response JSON should include message "Logout all successful"
    When I send "GET" to "/api/auth/me" with the second Bearer token
    Then the response status should be 401

  @priority_low
  Scenario: Logout without auth returns 401
    When I send "POST" to "/api/auth/logout" without an Authorization header
    Then the response status should be 401

  @priority_low
  Scenario: Logout all without auth returns 401
    When I send "POST" to "/api/auth/logout-all" without an Authorization header
    Then the response status should be 401
