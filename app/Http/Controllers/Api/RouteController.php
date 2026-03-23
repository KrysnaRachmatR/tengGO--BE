<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Route\StoreRouteRequest;
use App\Http\Requests\Route\UpdateRouteRequest;
use App\Http\Resources\RouteResource;
use App\Models\Route;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    // GET /api/v1/routes
    public function index(Request $request): JsonResponse
    {
        $routes = Route::query()
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('origin', 'like', "%{$request->search}%")
                  ->orWhere('destination', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%");
            }))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->withCount('series')
            ->orderBy('origin')
            ->paginate(15);

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => [
                'items' => RouteResource::collection($routes->items()),
                'meta'  => [
                    'current_page' => $routes->currentPage(),
                    'per_page'     => $routes->perPage(),
                    'total'        => $routes->total(),
                ],
            ],
        ]);
    }

    // POST /api/v1/routes
    public function store(StoreRouteRequest $request): JsonResponse
    {
        $route = Route::create($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Rute berhasil ditambahkan.',
            'data'    => new RouteResource($route),
        ], 201);
    }

    // GET /api/v1/routes/{route}
    public function show(int $routeId): JsonResponse
    {
        $route = Route::withCount('series')
                      ->with('activeSeries')
                      ->findOrFail($routeId);

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => new RouteResource($route),
        ]);
    }

    // PUT /api/v1/routes/{route}
    public function update(UpdateRouteRequest $request, int $routeId): JsonResponse
    {
        $route = Route::findOrFail($routeId);
        $route->update($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Rute berhasil diperbarui.',
            'data'    => new RouteResource($route->fresh()),
        ]);
    }

    // DELETE /api/v1/routes/{route}
    public function destroy(Request $request, int $routeId): JsonResponse
    {
        abort_unless($request->user()->isAdminPo(), 403, 'Akses ditolak.');

        $route = Route::findOrFail($routeId);

        // Tidak bisa hapus rute yang masih punya seri aktif
        if ($route->activeSeries()->exists()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Rute tidak bisa dihapus karena masih memiliki seri aktif.',
                'data'    => null,
            ], 422);
        }

        $route->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Rute berhasil dihapus.',
            'data'    => null,
        ]);
    }
}