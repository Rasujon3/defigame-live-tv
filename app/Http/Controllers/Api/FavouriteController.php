<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favourite;
use App\Models\Tv;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FavouriteController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'statusCode' => 422,
                'message' => 'The given data was invalid',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            $favourites = Favourite::where('user_id', $request->user_id)
                ->latest()
                ->get('tv_id');

            if (empty($favourites)) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 404,
                    'message' => 'No favourites found',
                    'data' => []
                ], 404);
            }

            $data = Tv::whereIn('id', $favourites->pluck('tv_id'))->get();

            return response()->json([
                'status' => true,
                'statusCode' => 200,
                'message' => 'Favourite list retrieved successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            // Log the error
            Log::error('Error in retrieving Favourite: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'message' => 'Something went wrong!!!',
                'data' => []
            ], 500);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'channel_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'statusCode' => 422,
                'message' => 'The given data was invalid',
                'data' => $validator->errors()
            ], 422);
        }
        try {
            // Check if already exists
            $checkExist = Favourite::where('user_id', $request->user_id)
                ->where('channel_id', $request->channel_id)
                ->exists();

            if ($checkExist) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 409,
                    'message' => 'Already in favourite',
                    'data' => []
                ], 409);
            }

            $tv = Tv::where('channel_id', $request->channel_id)->first();

            if (!$tv) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 404,
                    'message' => 'TV channel not found',
                    'data' => []
                ], 404);
            }

            $favourite = Favourite::firstOrCreate([
                'user_id' => $request->user_id,
                'channel_id' => $request->channel_id,
                'tv_id' => $tv->id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Added to favourite',
                'data' => $favourite
            ]);
        } catch (Exception $e) {
            // Log the error
            Log::error('Error in storing Favourite: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'message' => 'Something went wrong!!!',
                'data' => []
            ], 500);
        }
    }

    /**
     * Remove from favourite
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'channel_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'statusCode' => 422,
                'message' => 'The given data was invalid',
                'data' => $validator->errors()
            ], 422);
        }
        try {
            // Check if already exists
            $checkExist = Favourite::where('user_id', $request->user_id)
                ->where('channel_id', $request->channel_id)
                ->exists();

            if (!$checkExist) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 409,
                    'message' => 'Not found in favourite',
                    'data' => []
                ], 409);
            }

            Favourite::where('user_id', $request->user_id)
                ->where('channel_id', $request->channel_id)
                ->delete();

            return response()->json([
                'status' => true,
                'statusCode' => 200,
                'message' => 'Removed from favourite',
                'data' => []
            ]);
        } catch (Exception $e) {
            // Log the error
            Log::error('Error in deleting Favourite: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'message' => 'Something went wrong!!!',
                'data' => []
            ], 500);
        }
    }
    public function addRemoveFavourite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'channel_id' => 'required|string',
            'param' => 'required|in:add,remove',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'statusCode' => 422,
                'message' => 'The given data was invalid',
                'data' => $validator->errors()
            ], 422);
        }
        try {
            if ($request->param === 'add') {
                // Call store method
                return $this->store($request);
            } else {
                // Call destroy method
                return $this->destroy($request);
            }
        } catch (Exception $e) {
            // Log the error
            Log::error('Error in addRemoveFavourite: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'message' => 'Something went wrong!!!',
                'data' => []
            ], 500);
        }
    }
}
