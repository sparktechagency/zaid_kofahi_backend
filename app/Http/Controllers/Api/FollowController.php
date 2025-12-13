<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\Follow;
use App\Models\Refund;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FollowController extends Controller
{
    public function followUnfollowToggle(Request $request, $id)
    {
        try {
            $userId = Auth::id();
            $followerId = $id;

            if ($followerId == Auth::id()) {
                throw ValidationException::withMessages([
                    'message' => "You can't follow yourself.",
                ]);
            }

            $existingFollow = Follow::where('user_id', $userId)
                ->where('follower_id', $followerId)
                ->first();

            if ($existingFollow) {

                $existingFollow->delete();

                return response()->json([
                    'status' => 'unfollowed',
                    'message' => 'Successfully unfollowed this user.'
                ]);

            } else {
                Follow::create([
                    'user_id' => $userId,
                    'follower_id' => $followerId
                ]);

                return response()->json([
                    'status' => 'followed',
                    'message' => 'Successfully followed this user.'
                ]);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'The requested model was not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
