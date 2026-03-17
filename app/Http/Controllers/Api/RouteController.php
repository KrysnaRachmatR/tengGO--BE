<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;

class RouteController extends Controller
{
     public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'origin' => 'required',
                'destination' => 'required',
                'company_id' => 'required'
            ]);

            $route = Route::create($validated);

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

    public function index()
    {
        $data = Route::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $route = Route::findOrFail($id);

            $route->update($request->all());

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

    public function destroy($id)
    {
        try {
            Route::findOrFail($id)->delete();

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
