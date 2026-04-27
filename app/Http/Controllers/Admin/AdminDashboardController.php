<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\TokenTransaction;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        $totalUsers      = User::count();
        $totalWorkspaces = Workspace::count();
        $totalPosts      = Post::count();
        $totalRevenue    = TokenTransaction::where('type', 'purchase')->sum('amount');

        $newUsersToday   = User::whereDate('created_at', today())->count();
        $newUsersWeek    = User::where('created_at', '>=', now()->subDays(7))->count();

        // Daily new users — last 30 days
        $userGrowth = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count'),
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Monthly revenue — last 6 months
        $revenueChart = TokenTransaction::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw('SUM(amount) as total'),
        )
            ->where('type', 'purchase')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return Inertia::render('Admin/Dashboard', compact(
            'totalUsers', 'totalWorkspaces', 'totalPosts', 'totalRevenue',
            'newUsersToday', 'newUsersWeek', 'userGrowth', 'revenueChart',
        ));
    }
}
