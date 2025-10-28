<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Get all settings
     */
    public function index()
    {
        $settings = Setting::all();

        return response()->json([
            'settings' => $settings->mapWithKeys(function ($setting) {
                return [$setting->key => [
                    'value' => Setting::get($setting->key),
                    'type' => $setting->type,
                    'description' => $setting->description,
                ]];
            })
        ]);
    }

    /**
     * Get a specific setting
     */
    public function show($key)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json(['error' => 'Setting not found'], 404);
        }

        return response()->json([
            'key' => $setting->key,
            'value' => Setting::get($key),
            'type' => $setting->type,
            'description' => $setting->description,
        ]);
    }

    /**
     * Update a setting
     */
    public function update(Request $request, $key)
    {
        $data = $request->validate([
            'value' => 'required',
        ]);

        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json(['error' => 'Setting not found'], 404);
        }

        Setting::set($key, $data['value'], $setting->type, $setting->description);

        return response()->json([
            'message' => 'Setting updated successfully',
            'key' => $key,
            'value' => Setting::get($key),
        ]);
    }

    /**
     * Update multiple settings at once
     */
    public function updateMultiple(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required',
        ]);

        foreach ($data['settings'] as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                Setting::set($key, $value, $setting->type, $setting->description);
            }
        }

        return response()->json([
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Upload signature image for contracts and letters
     */
    public function uploadSignature(Request $request)
    {
        $data = $request->validate([
            'signature' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Store the signature image
            $path = $request->file('signature')->store('signatures', 'public');

            // Get the full URL
            $url = asset('storage/' . $path);

            // Save to settings
            Setting::set('hr_signature_image', $path, 'string', 'HR signature image for contracts and letters');

            return response()->json([
                'message' => 'Signature uploaded successfully',
                'path' => $path,
                'url' => $url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to upload signature',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete signature image
     */
    public function deleteSignature()
    {
        try {
            $signaturePath = Setting::get('hr_signature_image');

            if ($signaturePath) {
                // Delete the file
                $fullPath = storage_path('app/public/' . $signaturePath);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }

                // Remove from settings
                Setting::where('key', 'hr_signature_image')->delete();
            }

            return response()->json([
                'message' => 'Signature deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete signature',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
