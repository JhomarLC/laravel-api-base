# language: en
@auth @registration
Feature: API user registration
  As an API client
  I want to register a new account
  So that I can verify my email and later authenticate

  Background:
    Given the API accepts JSON at the "/api" prefix
    And the auth routes are under "/api/auth"

  @priority_high
  Scenario: Register with valid payload returns 201 without token
    Given no user exists with email "newuser@example.com"
    When I send "POST" to "/api/auth/register" with JSON body:
      """
      {
        "name": "Jane Doe",
        "email": "newuser@example.com",
        "password": "SecurePass1",
        "password_confirmation": "SecurePass1"
      }
      """
    Then the response status should be 201
    And the response JSON should include message "Registration successful. Please verify your email."
    And the response JSON path "data.user.email" should equal "newuser@example.com"
    And the response JSON should not contain key "token" at top level or under "data"
    And a user record should exist for "newuser@example.com"
    And an email verification OTP record should exist for "newuser@example.com"

  @priority_high
  Scenario: Register with duplicate email returns 422
    Given a user already exists with email "taken@example.com"
    When I send "POST" to "/api/auth/register" with JSON body:
      """
      {
        "name": "Other",
        "email": "taken@example.com",
        "password": "SecurePass1",
        "password_confirmation": "SecurePass1"
      }
      """
    Then the response status should be 422
    And the response should indicate validation errors for "email"

  @priority_high
  Scenario: Register with password shorter than 8 characters returns 422
    When I send "POST" to "/api/auth/register" with JSON body:
      """
      {
        "name": "Jane",
        "email": "shortpw@example.com",
        "password": "Short1",
        "password_confirmation": "Short1"
      }
      """
    Then the response status should be 422
    And the response should indicate validation errors for "password"

  @priority_high
  Scenario: Register with password confirmation mismatch returns 422
    When I send "POST" to "/api/auth/register" with JSON body:
      """
      {
        "name": "Jane",
        "email": "mismatch@example.com",
        "password": "SecurePass1",
        "password_confirmation": "SecurePass2"
      }
      """
    Then the response status should be 422

  @priority_medium
  Scenario: Register with invalid email format returns 422
    When I send "POST" to "/api/auth/register" with JSON body:
      """
      {
        "name": "Jane",
        "email": "not-an-email",
        "password": "SecurePass1",
        "password_confirmation": "SecurePass1"
      }
      """
    Then the response status should be 422
    And the response should indicate validation errors for "email"

  @priority_medium
  Scenario: Register without name returns 422
    When I send "POST" to "/api/auth/register" with JSON body:
      """
      {
        "email": "noname@example.com",
        "password": "SecurePass1",
        "password_confirmation": "SecurePass1"
      }
      """
    Then the response status should be 422
    And the response should indicate validation errors for "name"

  @priority_medium
  Scenario: Register with name longer than 255 characters returns 422
    When I send "POST" to "/api/auth/register" with JSON body with a name of 256 characters
    Then the response status should be 422

  @priority_low
  Scenario: Second registration with same email fails after first succeeds
    Given registration succeeded once for "double@example.com"
    When I send "POST" to "/api/auth/register" again with the same email
    Then the response status should be 422
