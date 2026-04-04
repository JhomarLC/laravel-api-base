# language: en
@auth @login
Feature: API user login
  As a verified user
  I want to obtain a Sanctum API token
  So that I can call protected endpoints

  Background:
    Given the API accepts JSON at the "/api" prefix
    And the auth routes are under "/api/auth"

  @priority_high
  Scenario: Login with verified user and correct credentials returns token
    Given a registered verified user "login@example.com" with password "Password1"
    When I send "POST" to "/api/auth/login" with JSON body:
      """
      {
        "email": "login@example.com",
        "password": "Password1"
      }
      """
    Then the response status should be 200
    And the response JSON should include message "Login successful"
    And the response JSON path "data.token" should be a non-empty string
    And the response JSON path "data.user.email" should equal "login@example.com"

  @priority_high
  Scenario: Login with wrong password returns 401
    Given a registered verified user "badpass@example.com" with password "Password1"
    When I send "POST" to "/api/auth/login" with JSON body:
      """
      {
        "email": "badpass@example.com",
        "password": "WrongPass1"
      }
      """
    Then the response status should be 401
    And the response JSON should include message "Invalid credentials"

  @priority_high
  Scenario: Login with unknown email returns 401
    Given no user exists with email "missing@example.com"
    When I send "POST" to "/api/auth/login" with JSON body:
      """
      {
        "email": "missing@example.com",
        "password": "Password1"
      }
      """
    Then the response status should be 401
    And the response JSON should include message "Invalid credentials"

  @priority_high
  Scenario: Login with unverified email returns 403
    Given a registered unverified user "unverified@example.com" with password "Password1"
    When I send "POST" to "/api/auth/login" with JSON body:
      """
      {
        "email": "unverified@example.com",
        "password": "Password1"
      }
      """
    Then the response status should be 403
    And the response JSON should include message "Please verify your email before logging in."

  @priority_medium
  Scenario: Login without password returns 422
    When I send "POST" to "/api/auth/login" with JSON body:
      """
      { "email": "a@b.com" }
      """
    Then the response status should be 422

  @priority_medium
  Scenario: Login without email returns 422
    When I send "POST" to "/api/auth/login" with JSON body:
      """
      { "password": "Password1" }
      """
    Then the response status should be 422

  @priority_medium
  Scenario: Login with invalid email format returns 422
    When I send "POST" to "/api/auth/login" with JSON body:
      """
      {
        "email": "not-email",
        "password": "Password1"
      }
      """
    Then the response status should be 422

  @priority_low
  Scenario: After password reset old tokens are invalid and new login works
    Given a registered verified user "relogin@example.com" with password "Password1" and an active API token
    When the user resets password using a valid reset token to "NewPassword2"
    And I call "GET" "/api/auth/me" with the previous Bearer token
    Then the response status should be 401
    When I send "POST" to "/api/auth/login" with JSON body:
      """
      {
        "email": "relogin@example.com",
        "password": "NewPassword2"
      }
      """
    Then the response status should be 200
    And the response JSON path "data.token" should be a non-empty string
