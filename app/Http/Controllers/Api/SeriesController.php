<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Series;
use App\Models\Route;

class SeriesController extends Controller
{
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
                'route_id' => 'required|exists:routes,id',
                'name' => 'required|string|max:255',
                'departure_time' => 'required|date_format:H:i:s',
                'origin_point' => 'required|string|max:255',
                'destination_point' => 'required|string|max:255',
            ]);

            // 🔒 Pastikan route milik company yang sama
            $route = Route::where('company_id', $user->company_id)
                          ->findOrFail($validated['route_id']);

            $series = Series::create([
                'company_id' => $user->company_id,
                'route_id' => $route->id,
                'name' => $validated['name'],
                'departure_time' => $validated['departure_time'],
                'origin_point' => $validated['origin_point'],
                'destination_point' => $validated['destination_point'],
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'data' => $series
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 📄 GET ALL (by company)
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Series::with('route')
            ->where('company_id', $user->company_id);

        // optional filter
        if ($request->has('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->active);
        }

        $data = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    // 🔍 DETAIL
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();

            $series = Series::with('route')
                ->where('company_id', $user->company_id)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $series
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

            $series = Series::where('company_id', $user->company_id)
                ->findOrFail($id);

            $validated = $request->validate([
                'route_id' => 'required|exists:routes,id',
                'name' => 'required|string|max:255',
                'departure_time' => 'required|date_format:H:i:s',
                'origin_point' => 'required|string|max:255',
                'destination_point' => 'required|string|max:255',
            ]);

            // 🔒 Validasi route milik company
            $route = Route::where('company_id', $user->company_id)
                ->findOrFail($validated['route_id']);

            $series->update([
                'route_id' => $route->id,
                'name' => $validated['name'],
                'departure_time' => $validated['departure_time'],
                'origin_point' => $validated['origin_point'],
                'destination_point' => $validated['destination_point'],
            ]);

            return response()->json([
                'success' => true,
                'data' => $series
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 🔁 TOGGLE ACTIVE (lebih aman daripada delete)
    public function toggle(Request $request, $id)
    {
        try {
            $user = $request->user();

            $series = Series::where('company_id', $user->company_id)
                ->findOrFail($id);

            $series->update([
                'is_active' => !$series->is_active
            ]);

            return response()->json([
                'success' => true,
                'data' => $series
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ❌ DELETE (optional, biasanya gak dipakai)
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();

            $series = Series::where('company_id', $user->company_id)
                ->findOrFail($id);

            $series->delete();

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