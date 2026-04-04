# language: en
@auth @password_reset
Feature: API forgot and reset password
  As a user who forgot their password
  I want to request a reset link and set a new password

  Background:
    Given the API accepts JSON at the "/api" prefix
    And the auth routes are under "/api/auth"

  @priority_high
  Scenario: Forgot password for existing user returns 200 and sends notification
    Given a registered verified user "forgot@example.com"
    When I send "POST" to "/api/auth/password/forgot" with JSON body:
      """
      { "email": "forgot@example.com" }
      """
    Then the response status should be 200
    And the response JSON should include message "If the account exists, a password reset link has been sent to your email."
    And a ResetPassword notification should be sent to "forgot@example.com"

  @priority_medium
  Scenario: Forgot password for unknown email returns same message
    Given no user exists with email "ghost@example.com"
    When I send "POST" to "/api/auth/password/forgot" with JSON body:
      """
      { "email": "ghost@example.com" }
      """
    Then the response status should be 200
    And the response JSON should include message "If the account exists, a password reset link has been sent to your email."

  @priority_medium
  Scenario: Forgot password with invalid email returns 422
    When I send "POST" to "/api/auth/password/forgot" with JSON body:
      """
      { "email": "invalid" }
      """
    Then the response status should be 422

  @priority_medium
  Scenario: Forgot password is throttled beyond 3 requests per minute
    When I send more than 3 "POST" requests to "/api/auth/password/forgot" within one minute from the same client
    Then the 4th response status should be 429

  @priority_low
  Scenario: Repeated forgot password within limit returns 200
    Given a registered verified user "repeat@example.com"
    When I send "POST" to "/api/auth/password/forgot" twice for "repeat@example.com" within limits
    Then both responses should have status 200

  @priority_high
  Scenario: Reset password with valid token succeeds and allows login with new password
    Given a registered verified user "resetok@example.com" with password "OldPassword1"
    And a valid password reset token for "resetok@example.com"
    When I send "POST" to "/api/auth/password/reset" with JSON body:
      """
      {
        "email": "resetok@example.com",
        "token": "<reset_token>",
        "password": "NewPassword2",
        "password_confirmation": "NewPassword2"
      }
      """
    Then the response status should be 200
    And the response JSON should include message "Password has been reset successfully."
    When I send "POST" to "/api/auth/login" with JSON body:
      """
      {
        "email": "resetok@example.com",
        "password": "NewPassword2"
      }
      """
    Then the response status should be 200

  @priority_high
  Scenario: Reset password revokes all Sanctum tokens
    Given a registered verified user "tokrev@example.com" with password "Password1" and an active API token
    And a valid password reset token for "tokrev@example.com"
    When I send "POST" to "/api/auth/password/reset" with a valid new password payload
    And I call "GET" "/api/auth/me" with the previous Bearer token
    Then the response status should be 401

  @priority_medium
  Scenario: Reset password with invalid token returns 400
    When I send "POST" to "/api/auth/password/reset" with JSON body:
      """
      {
        "email": "any@example.com",
        "token": "invalid-token",
        "password": "NewPassword2",
        "password_confirmation": "NewPassword2"
      }
      """
    Then the response status should be 400
    And the response JSON should include message "Invalid or expired reset token."

  @priority_medium
  Scenario: Reset password with weak password returns 422
    Given a registered verified user "weak@example.com"
    And a valid password reset token for "weak@example.com"
    When I send "POST" to "/api/auth/password/reset" with JSON body:
      """
      {
        "email": "weak@example.com",
        "token": "<reset_token>",
        "password": "lowercase1",
        "password_confirmation": "lowercase1"
      }
      """
    Then the response status should be 422

  @priority_medium
  Scenario: Reset password with confirmation mismatch returns 422
    Given a registered verified user "nomatch@example.com"
    And a valid password reset token for "nomatch@example.com"
    When I send "POST" to "/api/auth/password/reset" with mismatched password_confirmation
    Then the response status should be 422

  @priority_medium
  Scenario: Reset password is throttled beyond 5 requests per minute
    When I send more than 5 "POST" requests to "/api/auth/password/reset" within one minute from the same client
    Then the 6th response status should be 429

  @priority_low
  Scenario: Reusing reset token after success returns 400
    Given a successful password reset for "reuse@example.com"
    When I send "POST" to "/api/auth/password/reset" again with the same token
    Then the response status should be 400
