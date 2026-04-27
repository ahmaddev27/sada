<?php

// BIL-02: token ledger audit for admins

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TokenTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminTokenAuditController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TokenTransaction::with('user:id,name,email')
            ->orderByDesc('created_at');

        if ($search = $request->string('search')->toString()) {
            $query->whereHas('user', fn ($u) => $u->where('email', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%"));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        $transactions = $query->paginate(30)->withQueryString();

        // Volume by type — last 30 days
        $volumeChart = TokenTransaction::select(
            DB::raw('DATE(created_at) as date'),
            'type',
            DB::raw('SUM(amount) as total'),
            DB::raw('COUNT(*) as count'),
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();

        $stats = [
            'total_transactions' => TokenTransaction::count(),
            'total_granted'      => (int) TokenTransaction::where('type', 'grant')->sum('amount'),
            'total_deducted'     => (int) TokenTransaction::where('type', 'deduct')->sum('amount'),
            'total_purchased'    => (int) TokenTransaction::where('type', 'purchase')->sum('amount'),
            'today_transactions' => TokenTransaction::whereDate('created_at', today())->count(),
        ];

        return Inertia::render('Admin/Tokens/Index', [
            'transactions' => $transactions,
            'filters'      => $request->only('search', 'type'),
            'stats'        => $stats,
            'volumeChart'  => $volumeChart,
        ]);
    }
}
