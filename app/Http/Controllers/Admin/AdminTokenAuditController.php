<?php

// BIL-02: token ledger audit for admins

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TokenTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            'total_granted'      => (int) TokenTransaction::where('type', 'bonus')->sum('amount'),
            'total_deducted'     => (int) TokenTransaction::where('type', 'deduction')->sum('amount'),
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

    public function export(): StreamedResponse
    {
        $filename = 'tokens-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['#', 'المستخدم', 'البريد', 'النوع', 'الكمية', 'الرصيد بعد', 'الوصف', 'التاريخ']);

            TokenTransaction::with('user:id,name,email')->orderByDesc('created_at')->cursor()
                ->each(function (TokenTransaction $tx) use ($out) {
                    fputcsv($out, [
                        $tx->id,
                        $tx->user->name ?? '',
                        $tx->user->email ?? '',
                        $tx->type,
                        $tx->amount,
                        $tx->balance_after,
                        $tx->description ?? '',
                        $tx->created_at->format('Y-m-d H:i'),
                    ]);
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
