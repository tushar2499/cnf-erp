<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $chevronEmployees = DB::table('chevron_employees')->count();

        $stats = [
            'users'             => User::count(),
            'companies'         => Company::count(),
            'roles'             => Role::count(),
            'permissions'       => Permission::count(),
            'chevron_employees' => $chevronEmployees,
            'employees'         => $chevronEmployees
                                 + DB::table('nas_freights_employees')->count()
                                 + DB::table('nas_trading_employees')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
