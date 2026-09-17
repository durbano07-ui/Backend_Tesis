<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginAttemptController extends Controller
{
    /**
     * Display a listing of login attempts.
     * GET /api/v1/login-attempts
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasRole('administrador')) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $query = LoginAttempt::query();

        // Filter by email
        if ($request->has('email')) {
            $query->where('email', $request->input('email'));
        }

        // Filter by IP address
        if ($request->has('ip_address')) {
            $query->where('ip_address', $request->input('ip_address'));
        }

        // Filter by success status
        if ($request->has('success')) {
            $query->where('success', $request->boolean('success'));
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }

        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }

        $perPage = $request->input('per_page', 20);
        $attempts = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json($attempts);
    }
}
