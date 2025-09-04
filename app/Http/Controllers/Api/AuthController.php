<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $user = \App\Models\User::where('email', $request->email)->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.']
                ]);
            }

            if (!Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.']
                ]);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            // Get profile picture URL - FIXED METHOD CALL
            $profilePictureUrl = $user->getProfilePictureUrl();

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'profile_picture' => $profilePictureUrl, // FIXED: Use proper method
                    'events_attended' => $user->getEventsAttendedCount(),
                    'bio' => $user->bio,
                    'interests' => $user->interests ?? []
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('API Login error: ' . $e->getMessage(), [
                'email' => $request->email,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again.'
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'sometimes|in:member,organizer,admin',
                'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
                'bio' => 'nullable|string|max:1000',
                'linkedin_url' => 'nullable|url',
                'twitter_url' => 'nullable|url',
                'interests' => 'nullable|string'
            ]);

            // Handle profile picture upload - FIXED
            $profilePicturePath = null;
            if ($request->hasFile('profile_picture')) {
                $image = $request->file('profile_picture');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('profile_pictures', $imageName, 'public');
                $profilePicturePath = $imageName; // Store just the filename
            }

            // Parse interests from JSON string - FIXED
            $interests = [];
            if ($request->has('interests') && !empty($request->interests)) {
                $interests = is_string($request->interests) 
                    ? json_decode($request->interests, true) 
                    : $request->interests;
                $interests = $interests ?: [];
            }

            $user = \App\Models\User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => $validatedData['role'] ?? 'member', // Default to member
                'profile_picture' => $profilePicturePath,
                'bio' => $validatedData['bio'] ?? null,
                'linkedin_url' => $validatedData['linkedin_url'] ?? null,
                'twitter_url' => $validatedData['twitter_url'] ?? null,
                'interests' => $interests,
                'events_attended' => 0
            ]);

            $token = $user->createToken('api-token')->plainTextToken;

            // Get profile picture URL - FIXED METHOD CALL
            $profilePictureUrl = $user->getProfilePictureUrl();

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'profile_picture' => $profilePictureUrl, // FIXED: Use proper method
                    'events_attended' => $user->getEventsAttendedCount(),
                    'bio' => $user->bio,
                    'interests' => $user->interests ?? []
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('API Registration error: ' . $e->getMessage(), [
                'email' => $request->email ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('API Logout error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Logout failed'
            ], 500);
        }
    }

    /**
     * Get current user profile (API)
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get profile picture URL - FIXED METHOD CALL
            $profilePictureUrl = $user->getProfilePictureUrl();

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'profile_picture' => $profilePictureUrl, // FIXED: Use proper method
                    'events_attended' => $user->getEventsAttendedCount(),
                    'bio' => $user->bio,
                    'interests' => $user->interests ?? [],
                    'linkedin_url' => $user->linkedin_url,
                    'twitter_url' => $user->twitter_url
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Me error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get user data'
            ], 500);
        }
    }
}
