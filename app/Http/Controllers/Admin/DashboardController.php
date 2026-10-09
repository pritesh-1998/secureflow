<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $usersPerRole = Role::withCount('users')->get();
        $totalRoles = Role::count();
        $totalPermissions = Permission::count();
        $recentLogs = AdminAuditLog::latest()->take(10)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'usersPerRole',
            'totalRoles',
            'totalPermissions',
            'recentLogs'
        ));
    }
}
