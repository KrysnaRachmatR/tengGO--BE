<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    // ➕ CREATE ROLE (per company)
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $role = Role::create([
                'name' => $validated['name'],
                'company_id' => $user->company_id
            ]);

            return response()->json([
                'success' => true,
                'data' => $role
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 📄 GET ROLES BY COMPANY
    public function index(Request $request)
    {
        $user = $request->user();

        $roles = Role::where('company_id', $user->company_id)->get();

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    // ❌ DELETE ROLE
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();

            $role = Role::where('company_id', $user->company_id)
                ->findOrFail($id);

            $role->delete();

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