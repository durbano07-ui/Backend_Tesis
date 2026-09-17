<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $roles = Role::where('guard_name', 'sanctum')->get(['id', 'name', 'guard_name']);

        return response()->json([
            'data' => $roles,
        ]);
    }
}
