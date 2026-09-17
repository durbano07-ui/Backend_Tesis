<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Show the password reset form.
     */
    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email) {
            return view('auth.reset-error', [
                'message' => 'Token o email no proporcionado'
            ]);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Process the password reset.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Verify the token
        $broker = app('auth.password.broker');
        $user = User::where('email', $request->email)->first();

        if (!$user || !$broker->tokenExists($user, $request->token)) {
            return view('auth.reset-error', [
                'message' => 'Token inválido o expirado. Solicita uno nuevo.'
            ]);
        }

        // Reset the password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the used token
        $broker->deleteToken($user);

        // Invalidate all tokens
        $user->tokens()->delete();

        return view('auth.reset-success');
    }
}
