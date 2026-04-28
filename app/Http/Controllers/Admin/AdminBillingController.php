<?php

// BIL-01→BIL-08 (admin view) — billing & revenue overview

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\TokenPackage;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminBillingController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Invoice::with('user:id,name,email')
            ->orderByDesc('created_at');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('gateway')) {
            $query->where('payment_gateway', $request->string('gateway')->toString());
        }

        $invoices = $query->paginate(25)->withQueryString();

        // Revenue stats
        $totalRevenue = Invoice::where('status', 'paid')->sum('total_amount');
        $thisMonth    = Invoice::where('status', 'paid')
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('total_amount');
        $thisYear = Invoice::where('status', 'paid')
            ->whereYear('paid_at', now()->year)
            ->sum('total_amount');

        // Monthly revenue — last 6 months
        $monthExpr   = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', paid_at)"
            : "DATE_FORMAT(paid_at, '%Y-%m')";
        $revenueChart = Invoice::select(
            DB::raw("{$monthExpr} as month"),
            DB::raw('SUM(total_amount) as total'),
            DB::raw('COUNT(*) as count'),
        )
            ->where('status', 'paid')
            ->where('paid_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $stats = [
            'total_revenue'    => (float) $totalRevenue,
            'this_month'       => (float) $thisMonth,
            'this_year'        => (float) $thisYear,
            'total_invoices'   => Invoice::count(),
            'paid_invoices'    => Invoice::where('status', 'paid')->count(),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
        ];

        $packages = TokenPackage::orderBy('sort_order')->get();

        return Inertia::render('Admin/Billing/Index', [
            'invoices'     => $invoices,
            'filters'      => $request->only('search', 'status', 'gateway'),
            'stats'        => $stats,
            'revenueChart' => $revenueChart,
            'packages'     => $packages,
        ]);
    }

    public function export(): StreamedResponse
    {
        $filename = 'billing-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM for Excel Arabic support
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['رقم الفاتورة', 'المستخدم', 'البريد', 'المبلغ', 'ضريبة', 'الإجمالي', 'العملة', 'الرصيد', 'البوابة', 'الحالة', 'تاريخ الدفع', 'تاريخ الإنشاء']);

            Invoice::with('user:id,name,email')->orderByDesc('created_at')->cursor()
                ->each(function (Invoice $inv) use ($out) {
                    fputcsv($out, [
                        $inv->invoice_number,
                        $inv->user->name ?? '',
                        $inv->user->email ?? '',
                        $inv->amount,
                        $inv->vat_amount,
                        $inv->total_amount,
                        $inv->currency,
                        $inv->tokens_purchased,
                        $inv->payment_gateway,
                        $inv->status,
                        $inv->paid_at ? \Carbon\Carbon::parse($inv->paid_at)->format('Y-m-d H:i') : '',
                        \Carbon\Carbon::parse($inv->created_at)->format('Y-m-d H:i'),
                    ]);
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
