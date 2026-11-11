<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->latest('id')->paginate(20);
        $roles = Role::with(['permissions', 'users'])->get();
        $permissions = Permission::all()->groupBy('module');

        return view('users.index', compact('users', 'roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => strtolower(trim($request->username)),
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        AuditLog::log('create_user', $user, null, $user->toArray(), "New staff user {$user->username} registered ({$user->email})");

        return back()->with('success', "User '{$user->name}' registered successfully. They can now login with email or username.");
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|unique:roles,slug',
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name, '_');

        // Check if slug exists
        if (Role::where('slug', $slug)->exists()) {
            $slug .= '_' . time();
        }

        $role = Role::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        AuditLog::log('create_role', $role, null, $role->toArray(), "New role '{$role->name}' created with " . count($request->permissions ?? []) . " permissions");

        return back()->with('success', "Role '{$role->name}' created successfully with assigned permissions.");
    }

    public function updateRole(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        AuditLog::log('update_role', $role, null, $role->toArray(), "Role '{$role->name}' updated with permissions");

        return back()->with('success', "Role '{$role->name}' and its permissions updated successfully.");
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        AuditLog::log('toggle_user_status', $user, null, ['is_active' => $user->is_active], "User {$user->username} status toggled to " . ($user->is_active ? 'active' : 'inactive'));

        return back()->with('success', "User '{$user->name}' status updated to " . ($user->is_active ? 'Active' : 'Deactivated') . ".");
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'required|string|min:6',
        ]);

        $user->password = Hash::make($request->new_password);
        $user->failed_login_attempts = 0;
        $user->locked_until = null;
        $user->save();

        AuditLog::log('reset_password', $user, null, null, "Password reset for user {$user->username}");

        return back()->with('success', "Password for '{$user->name}' has been reset successfully.");
    }
}
