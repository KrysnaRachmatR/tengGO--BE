<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Armada;
use Illuminate\Support\Facades\Storage;

class ArmadaController extends Controller
{
    // 📋 GET ALL
    public function index()
    {
        $data = Armada::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

   public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required',
                'plate_number' => 'required|unique:armadas,plate_number',
                'seat_capacity' => 'required|integer',
                'status' => 'required',
                'company_id' => 'required',
            ]);

            $armada = Armada::create($validated);

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

    public function update(Request $request, $id)
    {
        try {
            $armada = Armada::findOrFail($id);

            $armada->update($request->all());

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
    public function destroy($id)
    {
        try {
            Armada::findOrFail($id)->delete();

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