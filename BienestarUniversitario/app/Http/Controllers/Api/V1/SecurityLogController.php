<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\SecurityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecurityLogController extends Controller
{
    /**
     * Display a listing of security logs.
     * GET /api/v1/security-logs
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasRole('administrador')) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $query = SecurityLog::with('user');

        // Filter by IP address
        if ($request->has('ip_address')) {
            $query->where('ip_address', $request->input('ip_address'));
        }

        // Filter by event type
        if ($request->has('event_type')) {
            $query->where('event_type', $request->input('event_type'));
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }

        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }

        $perPage = $request->input('per_page', 20);
        $logs = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Get currently blocked/locked IPs.
     * GET /api/v1/security-logs/blocked-ips
     */
    public function blockedIps(): JsonResponse
    {
        $user = request()->user();

        if (!$user->hasRole('administrador')) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        // Get currently locked login attempts
        $lockedAttempts = LoginAttempt::locked()
            ->select('email', 'ip_address', 'locked_until', 'created_at')
            ->orderByDesc('locked_until')
            ->get()
            ->unique(fn($item) => $item->ip_address);

        return response()->json([
            'locked_ips' => $lockedAttempts->values(),
        ]);
    }
}
