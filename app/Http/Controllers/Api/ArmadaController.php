<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Armada;

class ArmadaController extends Controller
{
    // 📋 GET ALL (by company)
    public function index(Request $request)
    {
        $user = $request->user();

        $data = Armada::where('company_id', $user->company_id)
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
                'name' => 'required|string|max:255',
                'plate_number' => 'required|string|max:50',
                'seat_capacity' => 'required|integer|min:1',
                'status' => 'required|in:active,maintenance',
            ]);

            // 🔥 unique per company (bukan global)
            $exists = Armada::where('company_id', $user->company_id)
                            ->where('plate_number', $validated['plate_number'])
                            ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plate number already exists in your company'
                ], 422);
            }

            $armada = Armada::create([
                'name' => $validated['name'],
                'plate_number' => $validated['plate_number'],
                'seat_capacity' => $validated['seat_capacity'],
                'status' => $validated['status'],
                'company_id' => $user->company_id
            ]);

            return response()->json([
                'success' => true,
                'data' => $armada
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

            $armada = Armada::where('company_id', $user->company_id)
                            ->findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'plate_number' => 'required|string|max:50',
                'seat_capacity' => 'required|integer|min:1',
                'status' => 'required|in:active,maintenance',
            ]);

            // cek plate_number unik (kecuali dirinya sendiri)
            $exists = Armada::where('company_id', $user->company_id)
                            ->where('plate_number', $validated['plate_number'])
                            ->where('id', '!=', $id)
                            ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plate number already exists in your company'
                ], 422);
            }

            $armada->update($validated);

            return response()->json([
                'success' => true,
                'data' => $armada
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

            $armada = Armada::where('company_id', $user->company_id)
                            ->findOrFail($id);

            $armada->delete();

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