<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    // 📋 GET ALL (by company)
    public function index(Request $request)
    {
        $user = $request->user();

        $data = Route::where('company_id', $user->company_id)
                     ->latest()
                     ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    // ➕ CREATE
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user || !$user->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'origin' => 'required|string|max:255',
                'destination' => 'required|string|max:255',
            ]);

            $route = Route::create([
                'origin' => $validated['origin'],
                'destination' => $validated['destination'],
                'company_id' => $user->company_id
            ]);

            return response()->json([
                'success' => true,
                'data' => $route
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ✏️ UPDATE
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();

            $route = Route::where('company_id', $user->company_id)
                          ->findOrFail($id);

            $validated = $request->validate([
                'origin' => 'required|string|max:255',
                'destination' => 'required|string|max:255',
            ]);

            $route->update($validated);

            return response()->json([
                'success' => true,
                'data' => $route
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 🗑️ DELETE
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();

            $route = Route::where('company_id', $user->company_id)
                          ->findOrFail($id);

            $route->delete();

            return response()->json([
                'success' => true,
                'message' => 'Deleted'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}