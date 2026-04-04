# language: en
@auth @email_verification @otp
Feature: API email verification and resend OTP
  As a registered user
  I want to verify my email with a 6-digit code
  And request a new code if needed

  Background:
    Given the API accepts JSON at the "/api" prefix
    And the auth routes are under "/api/auth"

  @priority_high
  Scenario: Verify email with correct OTP returns 200
    Given a registered unverified user "verify@example.com" with a known OTP
    When I send "POST" to "/api/auth/email/verify" with JSON body:
      """
      {
        "email": "verify@example.com",
        "otp": "<known_otp>"
      }
      """
    Then the response status should be 200
    And the response JSON should include message "Email verified successfully."
    And the response JSON path "data.user.has_verified_email" should be true
    And the OTP record for "verify@example.com" should be removed

  @priority_high
  Scenario: Verify with wrong OTP returns 400
    Given a registered unverified user "wrongotp@example.com" with OTP "123456"
    When I send "POST" to "/api/auth/email/verify" with JSON body:
      """
      {
        "email": "wrongotp@example.com",
        "otp": "000000"
      }
      """
    Then the response status should be 400
    And the response JSON should include message "Invalid or expired verification code."

  @priority_high
  Scenario: Verify with expired OTP returns 400
    Given a registered unverified user "expired@example.com" with an expired OTP
    When I send "POST" to "/api/auth/email/verify" with JSON body with that user's OTP
    Then the response status should be 400
    And the response JSON should include message "Invalid or expired verification code."

  @priority_high
  Scenario: Verify when email is already verified returns 400
    Given a registered verified user "already@example.com"
    When I send "POST" to "/api/auth/email/verify" with JSON body:
      """
      {
        "email": "already@example.com",
        "otp": "123456"
      }
      """
    Then the response status should be 400
    And the response JSON should include message "Invalid or expired verification code."

  @priority_medium
  Scenario: Verify with OTP that is not six digits returns 422
    When I send "POST" to "/api/auth/email/verify" with JSON body:
      """
      {
        "email": "any@example.com",
        "otp": "12345"
      }
      """
    Then the response status should be 422

  @priority_medium
  Scenario: Verify with non-numeric OTP returns 422
    When I send "POST" to "/api/auth/email/verify" with JSON body:
      """
      {
        "email": "any@example.com",
        "otp": "abcdef"
      }
      """
    Then the response status should be 422

  @priority_medium
  Scenario: Verify without email or otp returns 422
    When I send "POST" to "/api/auth/email/verify" with JSON body "{}"
    Then the response status should be 422

  @priority_medium
  Scenario: Verify endpoint is throttled beyond 5 requests per minute
    When I send more than 5 "POST" requests to "/api/auth/email/verify" within one minute from the same client
    Then the 6th response status should be 429

  @priority_low
  Scenario: Verify for unknown email returns 400
    Given no user exists with email "unknown@example.com"
    When I send "POST" to "/api/auth/email/verify" with JSON body:
      """
      {
        "email": "unknown@example.com",
        "otp": "123456"
      }
      """
    Then the response status should be 400
    And the response JSON should include message "Invalid or expired verification code."

  # --- Resend verification ---

  @priority_high
  Scenario: Resend OTP for unverified user returns 200 and refreshes OTP
    Given a registered unverified user "resend@example.com" with an existing OTP hash
    When I send "POST" to "/api/auth/email/resend" with JSON body:
      """
      { "email": "resend@example.com" }
      """
    Then the response status should be 200
    And the response JSON should include message "If the account exists and is not yet verified, a verification code has been sent."
    And the OTP hash for "resend@example.com" should differ from the previous hash
    And a VerifyEmailOtp notification should have been sent

  @priority_medium
  Scenario: Resend for non-existent email returns same generic message
    Given no user exists with email "nobody@example.com"
    When I send "POST" to "/api/auth/email/resend" with JSON body:
      """
      { "email": "nobody@example.com" }
      """
    Then the response status should be 200
    And the response JSON should include message "If the account exists and is not yet verified, a verification code has been sent."

  @priority_medium
  Scenario: Resend for verified user returns 200 without sending a new OTP
    Given a registered verified user "verified@example.com"
    When I send "POST" to "/api/auth/email/resend" with JSON body:
      """
      { "email": "verified@example.com" }
      """
    Then the response status should be 200
    And no new VerifyEmailOtp notification should be sent

  @priority_medium
  Scenario: Resend endpoint is throttled beyond 3 requests per minute
    When I send more than 3 "POST" requests to "/api/auth/email/resend" within one minute from the same client
    Then the 4th response status should be 429

  @priority_low
  Scenario: Resend with invalid email returns 422
    When I send "POST" to "/api/auth/email/resend" with JSON body:
      """
      { "email": "not-valid" }
      """
    Then the response status should be 422
