<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Listar notificaciones del usuario autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()->take(50)->get()->map(function ($n) {
            $data = is_array($n->data) ? $n->data : (json_decode($n->data, true) ?? []);
            return array_merge($data, [
                'id' => $n->id,
                'leida' => !is_null($n->read_at),
                'creada_en' => $n->created_at ? $n->created_at->diffForHumans() : 'Reciente',
                'timestamp' => $n->created_at ? $n->created_at->getTimestampMs() : now()->getTimestampMs(),
            ]);
        });

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }

    /**
     * Marcar una notificación como leída
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notificación marcada como leída'
        ]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Todas las notificaciones marcadas como leídas'
        ]);
    }

    /**
     * Eliminar una notificación
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->find($id);

        if ($notification) {
            $notification->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notificación eliminada'
        ]);
    }
}
