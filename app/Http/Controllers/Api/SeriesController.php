<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Series;
use Illuminate\Support\Facades\Storage;

class SeriesController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required',
                'company_id' => 'required'
            ]);

            $series = Series::create($validated);

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

    public function index()
    {
        $data = Series::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $series = Series::findOrFail($id);

            $series->update($request->all());

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

    public function destroy($id)
    {
        try {
            Series::findOrFail($id)->delete();

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