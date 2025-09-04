<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile
     */
    public function show(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
               'profile_picture' => $user->profile_picture_url,
                'bio' => $user->bio,
                'linkedin_url' => $user->linkedin_url,
                'twitter_url' => $user->twitter_url,
                'interests' => $user->interests,
                'events_attended' => $user->events_attended,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ]);
    }

    /**
     * Update the authenticated user's profile
     */
    public function update(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|min:8|confirmed',
            'current_password' => 'required_with:password|current_password:sanctum',
            'bio' => 'nullable|string|max:1000',
            'linkedin_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'interests' => 'nullable|string',
            'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
            'password.confirmed' => 'Password confirmation does not match.',
            'email.unique' => 'This email is already taken.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        
        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/profile_pictures', $imageName);
            $validated['profile_picture'] = $imageName;
        }
        
        // Parse interests from JSON string
        if (isset($validated['interests'])) {
            $interests = json_decode($validated['interests'], true) ?: [];
            $validated['interests'] = $interests;
        }
        
        // Remove current_password from data to update
        unset($validated['current_password']);

        try {
            $user->updateProfile($validated);

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'profile_picture' => $user->profile_picture_url,
                    'bio' => $user->bio,
                    'linkedin_url' => $user->linkedin_url,
                    'twitter_url' => $user->twitter_url,
                    'interests' => $user->interests,
                    'events_attended' => $user->getEventsAttendedCount(),
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the authenticated user's account
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        
        // Validate password for security
        $validator = Validator::make($request->all(), [
            'password' => 'required|current_password:sanctum'
        ], [
            'password.current_password' => 'The password is incorrect.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Password verification failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user->deleteAccount();

            return response()->json([
                'message' => 'Account deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Account deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get events created by the authenticated user
     */
    public function myEvents(Request $request)
    {
        $events = $request->user()->organizedEvents()->with(['category', 'attendees'])->get();
        
        return response()->json([
            'events' => $events
        ]);
    }

    /**
     * Get events the authenticated user is attending
     */
    public function myAttendances(Request $request)
    {
        $attendances = $request->user()->getAttendances()->get();
        
        return response()->json([
            'attendances' => $attendances
        ]);
    }
}