<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Series;

class ScheduleController extends Controller
{
    // ➕ OPEN SINGLE
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'series_id' => 'required|exists:series,id',
                'date' => 'required|date',
            ]);

            // 🔒 validasi series milik company
            $series = Series::where('company_id', $user->company_id)
                ->where('is_active', true)
                ->findOrFail($validated['series_id']);

            // 🚨 cegah duplicate
            $exists = Schedule::where('series_id', $series->id)
                ->whereDate('date', $validated['date'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule already exists'
                ], 422);
            }

            $schedule = Schedule::create([
                'company_id' => $user->company_id,
                'series_id' => $series->id,
                'date' => $validated['date'],
                'is_open' => true
            ]);

            return response()->json([
                'success' => true,
                'data' => $schedule
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 🔥 BULK OPEN (INI YANG SERING DIPAKAI ADMIN)
    public function bulkStore(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'series_ids' => 'required|array',
                'date' => 'required|date',
            ]);

            $created = [];

            foreach ($validated['series_ids'] as $series_id) {

                $series = Series::where('company_id', $user->company_id)
                    ->where('is_active', true)
                    ->find($series_id);

                if (!$series) continue;

                // cek duplicate
                $exists = Schedule::where('series_id', $series->id)
                    ->whereDate('date', $validated['date'])
                    ->exists();

                if ($exists) continue;

                $created[] = Schedule::create([
                    'company_id' => $user->company_id,
                    'series_id' => $series->id,
                    'date' => $validated['date'],
                    'is_open' => true
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $created
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 📅 GET BY DATE (🔥 PALING PENTING)
    public function byDate(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'date' => 'required|date'
            ]);

            $data = Schedule::with([
                    'series.route',
                    'trips.armada'
                ])
                ->where('company_id', $user->company_id)
                ->whereDate('date', $validated['date'])
                ->orderBy('id')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 🔍 DETAIL
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();

            $schedule = Schedule::with([
                    'series.route',
                    'trips.armada'
                ])
                ->where('company_id', $user->company_id)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $schedule
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ❌ DELETE / CLOSE SCHEDULE
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();

            $schedule = Schedule::where('company_id', $user->company_id)
                ->findOrFail($id);

            // ⚠️ optional: cek kalau sudah ada trip
            if ($schedule->trips()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete, already has trips'
                ], 422);
            }

            $schedule->delete();

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