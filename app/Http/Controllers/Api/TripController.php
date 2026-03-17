<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;

class TripController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'route_id' => 'required|exists:routes,id',
                'series_id' => 'required|exists:series,id',
                'armada_id' => 'required|exists:armadas,id',
                'departure_date' => 'required|date',
                'departure_time' => 'required',
                'company_id' => 'required'
            ]);

            $trip = Trip::create($validated);

            return response()->json([
                'success' => true,
                'data' => $trip
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function bulkStore(Request $request)
    {
        try {
            $validated = $request->validate([
                'route_id' => 'required',
                'departure_date' => 'required|date',
                'company_id' => 'required',
                'trips' => 'required|array'
            ]);

            $createdTrips = [];

            foreach ($request->trips as $t) {
                $createdTrips[] = Trip::create([
                    'route_id' => $validated['route_id'],
                    'series_id' => $t['series_id'],
                    'armada_id' => $t['armada_id'],
                    'departure_date' => $validated['departure_date'],
                    'departure_time' => $t['departure_time'],
                    'company_id' => $validated['company_id'],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $createdTrips
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $data = Trip::with(['route', 'series', 'armada'])
                    ->latest()
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        try {
            Trip::findOrFail($id)->delete();

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