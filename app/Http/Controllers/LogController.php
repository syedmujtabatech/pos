<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function activityLogs(Request $request)
    {
        $user = auth()->user();
        if (!$user->isOwner() && !$user->isManager() && !$user->hasPermission('logs.activity')) {
            abort(403, 'Unauthorized access to Audit Logs.');
        }

        $query = AuditLog::with('user.role')->latest('id');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('notes', 'like', $term)
                  ->orWhere('ip_address', 'like', $term)
                  ->orWhere('model_type', 'like', $term);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(25)->appends($request->query());

        // Key stats
        $todayCount = AuditLog::whereDate('created_at', today())->count();
        $sensitiveCount = AuditLog::whereDate('created_at', today())
            ->whereIn('action', ['void', 'delete', 'toggle_user_status', 'reset_password', 'bounce_cheque'])
            ->count();
        $activeUsersToday = AuditLog::whereDate('created_at', today())
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        $users = User::orderBy('name')->get(['id', 'name', 'username']);
        $actionTypes = AuditLog::select('action')->distinct()->pluck('action');

        return view('admin.activity_logs', compact('logs', 'users', 'actionTypes', 'todayCount', 'sensitiveCount', 'activeUsersToday'));
    }

    public function loginHistory(Request $request)
    {
        $user = auth()->user();
        if (!$user->isOwner() && !$user->isManager() && !$user->hasPermission('logs.login')) {
            abort(403, 'Unauthorized access to Login History.');
        }

        $query = LoginHistory::with('user.role')->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('username', 'like', $term)
                  ->orWhere('ip_address', 'like', $term)
                  ->orWhere('user_agent', 'like', $term);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $history = $query->paginate(25)->appends($request->query());

        // Stats
        $todayLogins = LoginHistory::whereDate('created_at', today())->count();
        $successfulToday = LoginHistory::whereDate('created_at', today())->where('status', 'success')->count();
        $failedToday = LoginHistory::whereDate('created_at', today())->where('status', 'failed')->count();
        $lockedAccounts = User::whereNotNull('locked_until')->where('locked_until', '>', now())->count();

        $users = User::orderBy('name')->get(['id', 'name', 'username']);

        return view('admin.login_history', compact('history', 'users', 'todayLogins', 'successfulToday', 'failedToday', 'lockedAccounts'));
    }
}
