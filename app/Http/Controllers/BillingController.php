<?php

// BIL-01→BIL-08

namespace App\Http\Controllers;

use App\Actions\Billing\CreatePaymentAction;
use App\Actions\Billing\HandlePaymentWebhookAction;
use App\Models\Invoice;
use App\Models\TokenPackage;
use App\Models\TokenTransaction;
use App\Models\User;
use App\Services\FeatureFlagService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    // BIL-01: billing overview — packages, balance, history
    public function index(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        if (! app(FeatureFlagService::class)->isEnabled('billing')) {
            return redirect()->route('dashboard')
                ->with('flash', ['error' => 'نظام الفواتير والدفع معطّل مؤقتاً.']);
        }

        /** @var User $user */
        $user = $request->user();

        $packages = TokenPackage::active()->get();

        $transactions = TokenTransaction::forUser($user->id)
            ->latest()
            ->paginate(20);

        $invoices = Invoice::where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Billing/Index', [
            'packages'     => $packages,
            'balance'      => $user->token_balance,
            'transactions' => $transactions,
            'invoices'     => $invoices,
        ]);
    }

    // BIL-01: initiate Moyasar payment session
    public function checkout(Request $request, CreatePaymentAction $action): JsonResponse
    {
        $validated = $request->validate([
            'package_id'   => ['required', 'integer', 'exists:token_packages,id'],
            'country'      => ['nullable', 'string', 'size:2'],
            'callback_url' => ['required', 'url'],
        ]);

        $package = TokenPackage::where('id', $validated['package_id'])
            ->where('is_active', true)
            ->firstOrFail();

        /** @var User $user */
        $user = $request->user();

        try {
            $result = $action->execute($user, $package, [
                'callback_url' => $validated['callback_url'],
                'country'      => $validated['country'] ?? '',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }

    // BIL-01: redirect landing after Moyasar payment
    public function callback(Request $request): RedirectResponse
    {
        $status = $request->query('status');

        if ($status === 'paid') {
            return redirect()->route('billing.index')
                ->with('flash.success', 'تمت عملية الدفع بنجاح. سيتم إضافة التوكنز خلال لحظات.');
        }

        return redirect()->route('billing.index')
            ->with('flash.error', 'لم تكتمل عملية الدفع. يرجى المحاولة مرة أخرى.');
    }

    // BIL-01: Moyasar webhook — no CSRF, must return 200
    public function webhook(Request $request, HandlePaymentWebhookAction $action): HttpResponse
    {
        $payload = $request->all();

        try {
            $action->execute($payload);
        } catch (\Throwable $e) {
            Log::error('Billing webhook processing failed', [
                'error'   => $e->getMessage(),
                'payload' => $payload,
            ]);
        }

        // Always return 200 so the gateway does not retry indefinitely
        return response()->noContent();
    }

    // BIL-07: download invoice as PDF
    public function downloadInvoice(Request $request, Invoice $invoice): HttpResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless(
            $invoice->user_id === $user->id,
            403,
            'غير مصرح لك بتنزيل هذه الفاتورة.',
        );

        $invoice->load('user');

        $pdf = Pdf::loadView('billing.invoice', [
            'invoice' => $invoice,
            'user'    => $invoice->user,
        ])->setPaper('a4');

        return $pdf->download('فاتورة-' . $invoice->invoice_number . '.pdf');
    }
}
