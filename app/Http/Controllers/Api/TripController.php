<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\TripSeat;
use App\Models\Seat;
use App\Models\Schedule;
use App\Models\Armada;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    // ➕ CREATE SINGLE
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'armada_id' => 'required|exists:armadas,id',
            'departure_time' => 'required|date',
            'arrival_time' => 'nullable|date|after:departure_time',
        ]);

        DB::beginTransaction();

        try {
            $schedule = Schedule::where('company_id', $user->company_id)
                ->findOrFail($validated['schedule_id']);

            $armada = Armada::where('company_id', $user->company_id)
                ->findOrFail($validated['armada_id']);

            $conflict = Trip::where('company_id', $user->company_id)
                ->where('armada_id', $armada->id)
                ->where(function ($q) use ($validated) {
                    $q->whereBetween('departure_time', [
                            $validated['departure_time'],
                            $validated['arrival_time'] ?? $validated['departure_time']
                        ])
                    ->orWhereBetween('arrival_time', [
                            $validated['departure_time'],
                            $validated['arrival_time'] ?? $validated['departure_time']
                        ]);
                })
                ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Armada conflict on selected time range'
                ], 422);
            }

            $trip = Trip::create([
                'company_id' => $user->company_id,
                'schedule_id' => $schedule->id,
                'armada_id' => $armada->id,
                'departure_time' => $validated['departure_time'],
                'arrival_time' => $validated['arrival_time'] ?? null,
                'seat_capacity' => $armada->seat_capacity,
                'status' => 'open'
            ]);

            // 🔥 WAJIB
            $this->generateSeats($trip);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $trip
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 🔥 CREATE MULTIPLE (BULK)
    public function bulkStore(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'departure_time' => 'required|date',
            'arrival_time' => 'nullable|date|after:departure_time',
            'armadas' => 'required|array',
            'armadas.*' => 'exists:armadas,id'
        ]);

        $schedule = Schedule::where('company_id', $user->company_id)
            ->findOrFail($validated['schedule_id']);

        $created = [];

        DB::beginTransaction();

        try {
            foreach ($validated['armadas'] as $armada_id) {

                $armada = Armada::where('company_id', $user->company_id)
                    ->findOrFail($armada_id);

                $conflict = Trip::where('company_id', $user->company_id)
                    ->where('armada_id', $armada->id)
                    ->where(function ($q) use ($validated) {
                        $q->whereBetween('departure_time', [
                                $validated['departure_time'],
                                $validated['arrival_time'] ?? $validated['departure_time']
                            ])
                          ->orWhereBetween('arrival_time', [
                                $validated['departure_time'],
                                $validated['arrival_time'] ?? $validated['departure_time']
                            ]);
                    })
                    ->exists();

                if ($conflict) continue;

                $created[] = Trip::create([
                    'company_id' => $user->company_id,
                    'schedule_id' => $schedule->id,
                    'armada_id' => $armada->id,
                    'departure_time' => $validated['departure_time'],
                    'arrival_time' => $validated['arrival_time'] ?? null,
                    'seat_capacity' => $armada->seat_capacity,
                    'status' => 'open'
                ]);

                $trip = Trip::create([
                    'company_id' => $user->company_id,
                    'schedule_id' => $schedule->id,
                    'armada_id' => $armada->id,
                    'departure_time' => $validated['departure_time'],
                    'arrival_time' => $validated['arrival_time'] ?? null,
                    'seat_capacity' => $armada->seat_capacity,
                    'status' => 'open'
                ]);

                $this->generateSeats($trip);

                $created[] = $trip;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'total_created' => count($created),
                'data' => $created
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 📋 GET ALL
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Trip::with([
            'armada:id,name',
            'schedule.series:id,origin_point,destination_point'
        ])
        ->where('company_id', $user->company_id);

        if ($request->filled('date')) {
            $query->whereDate('departure_time', $request->date);
        }

        $data = $query->latest()->get();

        return response()->json([
            'success' => true,
            'total' => $data->count(),
            'data' => $data
        ]);
    }

    // 🔄 UPDATE STATUS
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();

        $validated = $request->validate([
            'status' => 'required|in:open,on_trip,completed,cancelled,closed'
        ]);

        $trip = Trip::where('company_id', $user->company_id)
            ->findOrFail($id);

        $trip->update($validated);

        return response()->json([
            'success' => true,
            'data' => $trip
        ]);
    }

    // ❌ DELETE
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $trip = Trip::where('company_id', $user->company_id)
            ->findOrFail($id);

        $trip->delete();

        return response()->json([
            'success' => true,
            'message' => 'Trip deleted'
        ]);
    }

    private function generateSeats($trip)
    {
        // ambil layout kursi dari armada
        $armadaSeats = Seat::where('armada_id', $trip->armada_id)
            ->with('seatType')
            ->get();

        // ambil harga dari trip_prices
        $prices = $trip->prices->keyBy('seat_type_id');

        $data = [];

        foreach ($armadaSeats as $seat) {

            $price = $prices[$seat->seat_type_id] ?? null;

            $data[] = [
                'trip_id' => $trip->id,
                'seat_number' => $seat->seat_number, // A1, B2
                'seat_type' => $seat->seatType->name,
                'price' => $price->price ?? 0,
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        TripSeat::insert($data);
    }
}