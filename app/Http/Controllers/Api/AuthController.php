<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\VerifyEmailOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        private EmailVerificationService $emailVerificationService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        $this->emailVerificationService->sendOtp($user);

        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
            'data' => [
                'user' => new UserResource($user),
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (!$user || !Hash::check($request->validated('password'), $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Please verify your email before logging in.',
            ], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ]
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'User authenticated',
            'data' => [
                'user' => new UserResource($request->user()),
            ]
        ], 200);
    }


    public function verifyEmail(VerifyEmailOtpRequest $request): JsonResponse
    {
        $user = $this->emailVerificationService->verify(
            $request->validated('email'),
            $request->validated('otp')
        );

        if (! $user) {
            return response()->json([
                'message' => 'Invalid or expired verification code.',
            ], 400);
        }

        return response()->json([
            'message' => 'Email verified successfully.',
            'data' => [
                'user' => new UserResource($user->fresh()),
            ],
        ], 200);
    }

    public function resendVerification(ResendOtpRequest $request): JsonResponse
    {
        $this->emailVerificationService->resend($request->validated('email'));

        return response()->json([
            'message' => 'If the account exists and is not yet verified, a verification code has been sent.',
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ], 200);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout all successful',
        ], 200);
    }


}
