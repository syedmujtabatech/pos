<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if ($user) {
            // Check if locked
            if ($user->locked_until && $user->locked_until->isFuture()) {
                LoginHistory::create([
                    'user_id' => $user->id,
                    'username' => $request->username,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status' => 'locked',
                ]);

                $diff = $user->locked_until->diffInMinutes(now()) + 1;
                return back()->withErrors([
                    'username' => "Account is locked due to multiple failed attempts. Please try again after {$diff} minutes.",
                ]);
            }

            // Check if inactive
            if (!$user->is_active) {
                return back()->withErrors([
                    'username' => 'This account has been deactivated. Please contact the administrator.',
                ]);
            }

            // Verify password
            if (Hash::check($request->password, $user->password)) {
                // Reset failed attempts
                $user->failed_login_attempts = 0;
                $user->locked_until = null;
                $user->last_login_at = now();
                $user->save();

                Auth::login($user, $request->boolean('remember'));

                LoginHistory::create([
                    'user_id' => $user->id,
                    'username' => $request->username,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status' => 'success',
                ]);

                AuditLog::log('login', $user, null, null, 'User logged into system');

                return redirect()->intended(route('dashboard'));
            }

            // Increment failed attempts
            $user->increment('failed_login_attempts');
            if ($user->failed_login_attempts >= 5) {
                $user->locked_until = now()->addMinutes(15);
                $user->save();
            }

            LoginHistory::create([
                'user_id' => $user->id,
                'username' => $request->username,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'failed',
            ]);
        } else {
            LoginHistory::create([
                'user_id' => null,
                'username' => $request->username,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'failed',
            ]);
        }

        return back()->withErrors([
            'username' => 'Invalid username or password.',
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLog::log('logout', Auth::user(), null, null, 'User logged out');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
