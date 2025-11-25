<?php

namespace App\Http\Controllers;

use App\Services\DeviceActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeviceActivityController extends Controller
{
    protected $activityService;

    public function __construct(DeviceActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Get device activity for a specific day
     * 
     * GET /api/device-activity/{deviceId}?day=1
     */
    public function getActivity(Request $request, int $deviceId): JsonResponse
    {
        // Prevent direct browser access - only allow AJAX requests
        if (!$request->expectsJson() && !$request->ajax()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $dayNumber = $request->query('day', 1);
        
        // Validate day number
        if (!in_array($dayNumber, [1, 2])) {
            return response()->json([
                'error' => 'Invalid day number. Must be 1 (today) or 2 (yesterday).'
            ], 400);
        }
        
        $activity = $this->activityService->getDeviceActivity($deviceId, $dayNumber);
        
        if (!$activity) {
            return response()->json([
                'error' => 'Activity data not found for this device and day.'
            ], 404);
        }
        
        return response()->json($activity);
    }

    /**
     * Get device activity for both days
     * For today: samples indicate "recorded" (1) or "not recorded" (0), use live device status
     * For yesterday: samples contain historical status snapshot
     * 
     * @param int $deviceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBothDays(Request $request, int $deviceId): JsonResponse
    {
        // Prevent direct browser access - only allow AJAX requests
        if (!$request->expectsJson() && !$request->ajax()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $today = $this->activityService->getDeviceActivity($deviceId, 1);
        $yesterday = $this->activityService->getDeviceActivity($deviceId, 2);
        
        // For today (day 1), samples now contain actual historical status
        // Calculate current sample index on server side to avoid timezone issues
        if ($today) {
            $now = \Carbon\Carbon::now();
            $minutesSinceMidnight = ($now->hour * 60) + $now->minute;
            $today['current_sample_index'] = floor($minutesSinceMidnight / 5);
            $today['is_live'] = true;
        }
        
        // For yesterday (day 2), samples contain the historical status
        if ($yesterday) {
            $yesterday['is_live'] = false;
        }
        
        return response()->json([
            'today' => $today,
            'yesterday' => $yesterday,
        ]);
    }
}
