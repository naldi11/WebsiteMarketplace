<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingControllerApi extends Controller
{
    /**
     * Get all system settings
     */
    public function index()
    {
        $settings = SystemSetting::all()->pluck('value', 'key');

        return response()->json([
            'status' => 'success',
            'data' => $settings
        ]);
    }

    /**
     * Get a specific setting by key
     */
    public function show($key)
    {
        $setting = SystemSetting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'status' => 'error',
                'message' => 'Setting not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'key' => $setting->key,
                'value' => $setting->value,
                'description' => $setting->description
            ]
        ]);
    }
}
