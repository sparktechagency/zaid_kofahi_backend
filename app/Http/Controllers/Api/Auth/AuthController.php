<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\OtpVerifyRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Models\Profile;
use App\Models\User;
use App\Services\Auth\AuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function register(RegisterRequest $request)
    {
        try {
            $user = $this->authService->register($request->validated());
            return $this->sendResponse(
                [],
                'Register successfully, OTP send you email, please verify your account.',
                true,
                201
            );
        } catch (Exception $e) {
            return $this->sendError('Registration Failed', ['error' => $e->getMessage()]);
        }
    }
    public function login(LoginRequest $request)
    {
        try {
            $data = $request->validated();
            $response = $this->authService->login($data);
            if (!$response['success']) {
                return $this->sendError($response['message'], [], $response['code']);
            }
            return $this->sendResponse([
                'token' => $response['token'],
                'token_type' => $response['token_type'],
                'expires_in' => $response['expires_in'],
                'user' => $response['user'],
            ], 'Login successful');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }
    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:PLAYER,ORGANIZER',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'google_id' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:20480' // 20 MB MAX
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 400);
        }

        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            $socialMatch = $request->has('google_id') && $existingUser->google_id === $request->google_id;

            if ($socialMatch) {

                Auth::login($existingUser);

                $tokenExpiry = Carbon::now()->addDays(7);
                $customClaims = ['exp' => $tokenExpiry->timestamp];
                $token = JWTAuth::customClaims($customClaims)->fromUser($existingUser);

                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'token' => $token,
                    'user_role' => $existingUser->role
                ], 200);
            } elseif (is_null($existingUser->google_id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'User already exists. Please sign in manually.'
                ], 422);
            } else {
                $existingUser->update([
                    'google_id' => $request->google_id ?? $existingUser->google_id,
                ]);

                Auth::login($existingUser);

                $tokenExpiry = Carbon::now()->addDays(7);
                $customClaims = ['exp' => $tokenExpiry->timestamp];
                $token = JWTAuth::customClaims($customClaims)->fromUser($existingUser);

                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'token' => $token,
                    'user_role' => $existingUser->role
                ], 200);
            }
        }

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filepath = $file->storeAs('avatars', $filename, 'public');
            $avatarPath = '/storage/' . $filepath;
        }

        $user = User::create([
            'role' => $request->role,
            'full_name' => $request->full_name,
            'user_name' => $request->full_name . '@' . rand(000, 999),
            'email' => $request->email,
            'password' => Hash::make(Str::random(16)),
            'avatar' => $avatarPath ?? null,
            'google_id' => $request->google_id ?? null,
            'status' => 'active',
        ]);

        Profile::create([
            'user_id' => $user->id,
        ]);

        Auth::login($user);

        $tokenExpiry = Carbon::now()->addDays(7);
        $customClaims = ['exp' => $tokenExpiry->timestamp];
        $token = JWTAuth::customClaims($customClaims)->fromUser($user);

        return response()->json([
            'status' => true,
            'message' => 'User registered and logged in successfully.',
            'token' => $token,
            'user_role' => $user->role
        ], 200);
    }
    public function verifyOtp(OtpVerifyRequest $request)
    {
        try {
            $otp = $request->validated()['otp'];
            $result = $this->authService->verifyOtp($otp);
            if (!$result['success']) {
                return $this->sendError($result['message'], [], $result['code']);
            }
            $user = $result['user'];
            $tokenExpiry = now()->addDays(7);
            $customClaims = ['exp' => $tokenExpiry->timestamp];
            $token = JWTAuth::customClaims($customClaims)->fromUser($user);
            return $this->sendResponse([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $tokenExpiry->toDateTimeString(),
                'user' => $user
            ], 'OTP verified successfully.');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong.', [], 500);
        }
    }
    public function resendOtp(ResendOtpRequest $request)
    {
        try {
            $email = $request->validated()['email'];
            $result = $this->authService->resendOtp($email);
            if (!$result['success']) {
                return $this->sendError($result['message'], [], $result['code']);
            }
            return $this->sendResponse($result, 'OTP resent to your email.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }
    public function forgotPassword(ResendOtpRequest $request)
    {
        try {
            $email = $request->validated()['email'];
            $result = $this->authService->forgotPassword($email);
            if (!$result['success']) {
                return $this->sendError($result['message'], [], $result['code']);
            }
            return $this->sendResponse($result, 'OTP sent to your email.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $password = $request->validated()['password'];
            $result = $this->authService->changePassword($password);
            if (!$result['success']) {
                return $this->sendError($result['message'], [], $result['code']);
            }
            return $this->sendResponse([], 'Password changed successfully.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }
    public function updatePassword(UpdatePasswordRequest $request)
    {
        try {
            $current_password = $request->validated()['current_password'];
            $password = $request->validated()['password'];
            $result = $this->authService->updatePassword($current_password, $password);
            if (!$result['success']) {
                return $this->sendError($result['message'], [], $result['code']);
            }
            return $this->sendResponse([], 'Password updated successfully.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }
    public function getProfile(Request $request)
    {
        try {
            $result = $this->authService->getProfile($request->user_id);
            if (!$result['success']) {
                return $this->sendError($result['message'], [], $result['code']);
            }
            return $this->sendResponse(['user' => $result['data'],], 'Your profile');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }
    public function logout()
    {
        try {
            $result = $this->authService->logout();
            if (!$result['success']) {
                return $this->sendError($result['message'], [], $result['code'] ?? 500);
            }
            return $this->sendResponse([], $result['message']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }
    public function checkToken(Request $request)
    {
        try {
            $user = JWTAuth::setToken($request->token)->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Token is valid',
                'data' => $user
            ]);

        } catch (TokenExpiredException $e) {
            return response()->json(['status' => false, 'message' => 'Token expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['status' => false, 'message' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['status' => false, 'message' => 'Token not provided'], 400);
        }
    }
    public function deleteAccount(Request $request)
    {
        if (Auth::user()->role != ['PLAYER', 'ORGANIZER']) {
            return response()->json([
                'status' => true,
                'message' => 'The ' . Str::lower(Auth::user()->role) . ' admin account cannot be deleted!'
            ]);
        }

        Auth::user()->delete();
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'status' => true,
            'message' => 'Account deleted successfully'
        ]);
    }
}
