<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class UserController extends Controller
{
    // ➕ CREATE USER
    public function store(Request $request)
    {
        try {
            $auth = $request->user();

            // ❗ hanya admin atau super admin
            if (!$auth->is_super_admin && !$auth->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
            ]);

            // 🔥 kalau super admin → wajib kirim company_id
            $companyId = $auth->is_super_admin
                ? $request->company_id
                : $auth->company_id;

            if ($auth->is_super_admin && !$companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'company_id required for super admin'
                ], 422);
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'company_id' => $companyId,
                'is_super_admin' => false
            ]);

            return response()->json([
                'success' => true,
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 📄 GET USERS
    public function index(Request $request)
    {
        $user = $request->user();

        $data = User::with('roles')
            ->where('company_id', $user->company_id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    // 🔥 ASSIGN ROLE
    public function assignRole(Request $request, $id)
    {
        try {
            $auth = $request->user();

            $validated = $request->validate([
                'role_id' => 'required|exists:roles,id',
            ]);

            $user = User::where('company_id', $auth->company_id)
                ->findOrFail($id);

            $role = Role::where('company_id', $auth->company_id)
                ->findOrFail($validated['role_id']);

            // prevent duplicate
            if (!$user->roles()->where('role_id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Role assigned'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ❌ REMOVE ROLE
    public function removeRole(Request $request, $id)
    {
        try {
            $auth = $request->user();

            $validated = $request->validate([
                'role_id' => 'required|exists:roles,id',
            ]);

            $user = User::where('company_id', $auth->company_id)
                ->findOrFail($id);

            $user->roles()->detach($validated['role_id']);

            return response()->json([
                'success' => true,
                'message' => 'Role removed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}