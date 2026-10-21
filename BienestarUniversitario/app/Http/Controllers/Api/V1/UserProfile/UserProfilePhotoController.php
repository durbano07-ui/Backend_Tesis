<?php

namespace App\Http\Controllers\Api\V1\UserProfile;

use App\Http\Controllers\Controller;
use App\Models\FotoUsuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserProfilePhotoController extends Controller
{
    public function show(): JsonResponse
    {
        $user = Auth::user();
        $foto = $user->foto;

        if (!$foto) {
            return response()->json([
                'data' => null,
                'message' => 'No photo found',
            ]);
        }

        return response()->json([
            'data' => [
                'id' => $foto->id,
                'url' => asset('storage/fotos/' . basename($foto->direccion_imagen)),
            ],
            'message' => 'Photo retrieved',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            // OWASP ASVS V12.1.1 / V12.2.1: tamaño máximo y solo formatos de imagen rasterizada
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user = Auth::user();

        $file = $request->file('foto');
        // OWASP ASVS V12.3.1: el nombre y la extensión los decide el servidor (según el tipo MIME real),
        // nunca el nombre de archivo que envía el cliente
        $filename = Str::uuid() . '.' . $file->extension();
        $path = 'fotos/' . $filename;

        Storage::disk('public')->putFileAs('fotos', $file, $filename);

        $foto = FotoUsuario::updateOrCreate(
            ['id_usuario' => $user->id],
            ['direccion_imagen' => $path]
        );

        return response()->json([
            'data' => [
                'id' => $foto->id,
                'url' => asset('storage/fotos/' . $filename),
            ],
            'message' => 'Photo uploaded successfully',
        ], 201);
    }
}
