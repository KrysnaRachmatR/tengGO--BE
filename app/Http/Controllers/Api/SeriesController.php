<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Series\StoreSeriesRequest;
use App\Http\Requests\Series\UpdateSeriesRequest;
use App\Http\Resources\SeriesResource;
use App\Models\Route;
use App\Models\Series;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeriesController extends Controller
{
    // GET /api/v1/routes/{route}/series
    public function index(Request $request, int $routeId): JsonResponse
    {
        $route = Route::findOrFail($routeId);

        $series = Series::where('route_id', $route->id)
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            }))
            ->with('route')
            ->orderBy('departure_time')
            ->paginate(15);

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => [
                'items' => SeriesResource::collection($series->items()),
                'meta'  => [
                    'current_page' => $series->currentPage(),
                    'per_page'     => $series->perPage(),
                    'total'        => $series->total(),
                ],
            ],
        ]);
    }

    // POST /api/v1/routes/{route}/series
    public function store(StoreSeriesRequest $request, int $routeId): JsonResponse
    {
        $route = Route::findOrFail($routeId);

        // Pastikan route milik PO yang sama
        abort_unless($route->po_id === $request->user()->po_id, 403, 'Akses ditolak.');

        $series = Series::create([
            ...$request->validated(),
            'route_id' => $route->id,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Seri berhasil ditambahkan.',
            'data'    => new SeriesResource($series->load('route')),
        ], 201);
    }

    // GET /api/v1/series/{series}
    public function show(int $seriesId): JsonResponse
    {
        $series = Series::with('route')->findOrFail($seriesId);

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => new SeriesResource($series),
        ]);
    }

    // PUT /api/v1/series/{series}
    public function update(UpdateSeriesRequest $request, int $seriesId): JsonResponse
    {
        $series = Series::findOrFail($seriesId);
        $series->update($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Seri berhasil diperbarui.',
            'data'    => new SeriesResource($series->fresh('route')),
        ]);
    }

    // DELETE /api/v1/series/{series}
    public function destroy(Request $request, int $seriesId): JsonResponse
    {
        abort_unless($request->user()->isAdminPo(), 403, 'Akses ditolak.');

        $series = Series::findOrFail($seriesId);

        // Tidak bisa hapus seri yang punya trip yang belum selesai
        $hasActiveTripS = $series->trips()
            ->whereIn('status', ['scheduled', 'running'])
            ->exists();

        if ($hasActiveTripS) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Seri tidak bisa dihapus karena masih memiliki trip yang berjalan.',
                'data'    => null,
            ], 422);
        }

        $series->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Seri berhasil dihapus.',
            'data'    => null,
        ]);
    }
}