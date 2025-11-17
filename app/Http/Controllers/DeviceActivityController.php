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
     * Get activity data for a specific device
     * 
     * GET /api/device-activity/{deviceId}?day=1
     */
    public function getActivity(Request $request, int $deviceId): JsonResponse
    {
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
    public function getBothDays(int $deviceId): JsonResponse
    {
        $today = $this->activityService->getDeviceActivity($deviceId, 1);
        $yesterday = $this->activityService->getDeviceActivity($deviceId, 2);
        
        // For today (day 1), get current live device status
        // Samples just indicate which time slots have been recorded
        if ($today) {
            $device = \App\Models\Devices::find($deviceId);
            if ($device) {
                $today['current_status'] = $device->status; // online/offline
                $today['is_live'] = true;
                
                // Calculate current sample index on server side to avoid timezone issues
                $now = \Carbon\Carbon::now();
                $minutesSinceMidnight = ($now->hour * 60) + $now->minute;
                $today['current_sample_index'] = floor($minutesSinceMidnight / 5);
            }
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
