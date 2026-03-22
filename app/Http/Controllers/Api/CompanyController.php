<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    // ➕ CREATE TENANT (SUPER ADMIN ONLY)
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user || !$user->is_super_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'domain' => [
                    'required',
                    'string',
                    'max:255',
                    'unique:companies,domain',
                    'regex:/^[a-z0-9\.\-:]+$/'
                ],
                'logo' => 'nullable|string',
                'primary_color' => 'nullable|string|max:20',
                'secondary_color' => 'nullable|string|max:20',
            ]);

            // normalize domain (anti typo case)
            $domain = strtolower(trim($validated['domain']));

            $company = Company::create([
                'name' => $validated['name'],
                'domain' => $domain,
                'logo' => $validated['logo'] ?? null,
                'primary_color' => $validated['primary_color'] ?? null,
                'secondary_color' => $validated['secondary_color'] ?? null,
                'api_key' => Str::random(40),
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tenant created successfully',
                'data' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'domain' => $company->domain,
                    'api_key' => $company->api_key, // ⚠️ hanya muncul saat create
                    'is_active' => $company->is_active,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tenant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 📄 GET ALL TENANTS
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->is_super_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = Company::query();

        // optional filter
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $companies = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $companies->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'domain' => $company->domain,
                    'logo' => $company->logo,
                    'is_active' => $company->is_active,
                    'created_at' => $company->created_at,
                ];
            })
        ]);
    }
}