<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;

class PublicTripController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'origin' => 'required|string',
            'destination' => 'required|string',
            'date' => 'required|date',
        ]);

        // 🔥 1. Resolve tenant dari domain / header
        $domain = $request->header('X-DOMAIN'); // frontend kirim header
        $apiKey = $request->header('X-API-KEY');
        $company = \App\Models\Company::where('domain', $domain)
            ->where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found or invalid API key'
            ], 401);
        }

        $trips = Trip::query()
            ->with([
                'company:id,name',
                'schedule.series:id,origin_point,destination_point',
                'armada:id,name',
                'prices.seatType:id,name,code'
            ])
            ->where('company_id', $company->id) // otomatis sesuai tenant
            ->where('status', 'open')
            ->whereDate('departure_time', $request->date)
            ->whereHas('schedule.series', function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('origin_point', 'like', "%{$request->origin}%")
                        ->where('destination_point', 'like', "%{$request->destination}%");
                })->orWhere(function ($query) use ($request) {
                    $query->where('origin_point', 'like', "%{$request->destination}%")
                        ->where('destination_point', 'like', "%{$request->origin}%");
                });
            })
            ->orderBy('departure_time', 'asc')
            ->get();

        $data = $trips->map(function ($trip) {
            $series = optional($trip->schedule)->series;

            return [
                'trip_id' => $trip->id,
                'company' => $trip->company->name ?? null,
                'armada' => $trip->armada->name ?? null,
                'origin' => $series->origin_point ?? null,
                'destination' => $series->destination_point ?? null,
                'departure_time' => $trip->departure_time,
                'arrival_time' => $trip->arrival_time,
                'seat_capacity' => $trip->seat_capacity,
                'seat_types' => $trip->prices->map(function ($price) {
                    return [
                        'type' => $price->seatType->name ?? null,
                        'code' => $price->seatType->code ?? null,
                        'price' => (int) $price->price,
                        'quota' => (int) $price->quota,
                        'available' => (int) $price->quota,
                    ];
                })->values(),
            ];
        });

        return response()->json([
            'success' => true,
            'meta' => [
                'total' => $data->count(),
                'date' => $request->date,
            ],
            'data' => $data
        ]);
    }
}