<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>فاتورة {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Tajawal', 'Arial Unicode MS', Arial, sans-serif;
            font-size: 13px;
            color: #0E1512;
            direction: rtl;
            background: #fff;
        }

        .page {
            padding: 40px 50px;
            max-width: 800px;
            margin: 0 auto;
        }

        /* ── Header ─────────────────────────────────── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0F6F5C;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .brand-name {
            font-size: 32px;
            font-weight: 700;
            color: #0F6F5C;
            letter-spacing: -0.5px;
        }

        .brand-sub {
            font-size: 11px;
            color: #5E6A64;
            margin-top: 2px;
        }

        .invoice-title-block {
            text-align: left;
        }

        .invoice-title {
            font-size: 20px;
            font-weight: 600;
            color: #0E1512;
        }

        .invoice-number {
            font-size: 13px;
            color: #5E6A64;
            margin-top: 4px;
        }

        /* ── Meta row ───────────────────────────────── */
        .meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .meta-block h4 {
            font-size: 11px;
            font-weight: 600;
            color: #5E6A64;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .meta-block p {
            font-size: 13px;
            color: #0E1512;
            line-height: 1.6;
        }

        /* ── Items table ────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead tr {
            background: #0F6F5C;
            color: #fff;
        }

        thead th {
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 600;
            text-align: right;
        }

        tbody tr {
            border-bottom: 1px solid #E5EBE8;
        }

        tbody td {
            padding: 12px 14px;
            font-size: 13px;
            color: #0E1512;
        }

        /* ── Totals ─────────────────────────────────── */
        .totals {
            width: 280px;
            margin-right: auto;
            margin-left: 0;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
            color: #0E1512;
            border-bottom: 1px dashed #E5EBE8;
        }

        .totals-row:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 15px;
            padding-top: 10px;
            color: #0F6F5C;
        }

        .totals-label { color: #5E6A64; }

        /* ── Status badge ───────────────────────────── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-paid   { background: #D1FAE5; color: #065F46; }
        .badge-pending{ background: #FEF3C7; color: #92400E; }

        /* ── Footer ─────────────────────────────────── */
        .footer {
            margin-top: 50px;
            padding-top: 16px;
            border-top: 1px solid #E5EBE8;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-note {
            font-size: 11px;
            color: #5E6A64;
            line-height: 1.5;
        }

        .zatca-badge {
            font-size: 10px;
            font-weight: 600;
            color: #0F6F5C;
            border: 1px solid #0F6F5C;
            border-radius: 4px;
            padding: 3px 8px;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ── Header ─────────────────────────────────── --}}
    <div class="header">
        <div>
            <div class="brand-name">صدى</div>
            <div class="brand-sub">منصة التسويق الرقمي بالذكاء الاصطناعي</div>
        </div>
        <div class="invoice-title-block">
            <div class="invoice-title">فاتورة ضريبية</div>
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            <div style="margin-top:6px;">
                <span class="badge {{ $invoice->isPaid() ? 'badge-paid' : 'badge-pending' }}">
                    {{ $invoice->isPaid() ? 'مدفوعة' : 'معلقة' }}
                </span>
            </div>
        </div>
    </div>

    {{-- ── Meta ─────────────────────────────────────── --}}
    <div class="meta-row">
        <div class="meta-block">
            <h4>فاتورة إلى</h4>
            <p>{{ $user->name }}</p>
            <p>{{ $user->email }}</p>
            @if($invoice->country)
            <p>{{ $invoice->country }}</p>
            @endif
        </div>
        <div class="meta-block" style="text-align:left;">
            <h4>تفاصيل الفاتورة</h4>
            <p>التاريخ: {{ $invoice->created_at->format('Y/m/d') }}</p>
            @if($invoice->paid_at)
            <p>تاريخ الدفع: {{ $invoice->paid_at->format('Y/m/d') }}</p>
            @endif
            <p>العملة: {{ $invoice->currency }}</p>
            @if($invoice->payment_gateway)
            <p>بوابة الدفع: {{ ucfirst($invoice->payment_gateway) }}</p>
            @endif
        </div>
    </div>

    {{-- ── Items table ──────────────────────────────── --}}
    <table>
        <thead>
            <tr>
                <th>الوصف</th>
                <th>الكمية</th>
                <th>السعر</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>توكنز رقمية — صدى</td>
                <td>{{ number_format($invoice->tokens_purchased) }} توكن</td>
                <td>{{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ── Totals ───────────────────────────────────── --}}
    <div class="totals">
        <div class="totals-row">
            <span class="totals-label">المجموع الفرعي</span>
            <span>{{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}</span>
        </div>
        @if($invoice->vat_rate > 0)
        <div class="totals-row">
            <span class="totals-label">ضريبة القيمة المضافة ({{ number_format($invoice->vat_rate, 0) }}%)</span>
            <span>{{ number_format($invoice->vat_amount, 2) }} {{ $invoice->currency }}</span>
        </div>
        @endif
        <div class="totals-row">
            <span>الإجمالي</span>
            <span>{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</span>
        </div>
    </div>

    {{-- ── Footer ───────────────────────────────────── --}}
    <div class="footer">
        <div class="footer-note">
            <p>شكراً لاختيارك منصة صدى.</p>
            @if($invoice->country === 'SA')
            <p>هذه الفاتورة متوافقة مع متطلبات هيئة الزكاة والضريبة والجمارك (ZATCA).</p>
            @endif
        </div>
        @if($invoice->country === 'SA')
        <div class="zatca-badge">ZATCA Compliant</div>
        @endif
    </div>

</div>
</body>
</html>
