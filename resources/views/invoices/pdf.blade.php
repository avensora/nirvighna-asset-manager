<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #1f2937; padding: 30px; }

        .clearfix::after { content: ''; display: table; clear: both; }

        /* Header */
        .header { overflow: hidden; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #2563eb; }
        .header-brand { float: left; }
        .brand-name { font-size: 22px; font-weight: bold; color: #2563eb; }
        .brand-sub  { font-size: 11px; color: #6b7280; margin-top: 2px; }
        .header-invoice { float: right; text-align: right; }
        .invoice-label { font-size: 28px; font-weight: bold; color: #1f2937; letter-spacing: 1px; }
        .invoice-number { font-size: 13px; color: #6b7280; margin-top: 2px; }
        .invoice-status { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 10px; font-weight: bold; margin-top: 5px; }
        .status-draft { background: #f3f4f6; color: #374151; }
        .status-sent  { background: #dbeafe; color: #1d4ed8; }
        .status-paid  { background: #d1fae5; color: #065f46; }

        /* Meta row */
        .meta-row { overflow: hidden; margin-bottom: 25px; }
        .bill-to { float: left; width: 55%; }
        .invoice-dates { float: right; width: 40%; }
        .section-label { font-size: 9px; font-weight: bold; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
        .client-name { font-size: 14px; font-weight: bold; color: #111827; }
        .client-detail { font-size: 11px; color: #374151; margin-top: 2px; }
        .gstin { font-size: 10px; color: #6b7280; margin-top: 4px; font-family: monospace; }
        .date-table { width: 100%; font-size: 11px; }
        .date-table td { padding: 3px 0; }
        .date-label { color: #6b7280; font-weight: bold; }
        .date-value { text-align: right; }

        /* Items table */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #2563eb; color: white; padding: 8px 10px; text-align: left; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; }
        .items-table th.text-right { text-align: right; }
        .items-table td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        .items-table tr:nth-child(even) td { background: #f9fafb; }
        .items-table td.text-right { text-align: right; }
        .items-table td.num { font-family: monospace; }

        /* Totals */
        .totals-wrap { overflow: hidden; }
        .totals-table { float: right; width: 270px; border-collapse: collapse; }
        .totals-table td { padding: 6px 10px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        .totals-table td:last-child { text-align: right; font-family: monospace; }
        .totals-table tr.grand-total td { background: #2563eb; color: white; font-weight: bold; font-size: 13px; }

        /* Notes */
        .notes { margin-top: 30px; padding: 12px 15px; background: #f9fafb; border-left: 3px solid #2563eb; }
        .notes-label { font-size: 9px; font-weight: bold; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 5px; }

        /* Footer */
        .footer { margin-top: 40px; padding-top: 10px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>

{{-- Header --}}
<div class="header clearfix">
    <div class="header-brand">
        <div class="brand-name">{{ config('app.name') }}</div>
        <div class="brand-sub">{{ config('mail.from.address') }}</div>
    </div>
    <div class="header-invoice">
        <div class="invoice-label">INVOICE</div>
        <div class="invoice-number"># {{ $invoice->invoice_number }}</div>
        <div>
            <span class="invoice-status status-{{ $invoice->status->value }}">{{ $invoice->status->label() }}</span>
        </div>
    </div>
</div>

{{-- Bill To + Dates --}}
<div class="meta-row clearfix">
    <div class="bill-to">
        <div class="section-label">Bill To</div>
        <div class="client-name">{{ $invoice->client->name }}</div>
        @if($invoice->client->company)
            <div class="client-detail">{{ $invoice->client->company }}</div>
        @endif
        @if($invoice->client->email)
            <div class="client-detail">{{ $invoice->client->email }}</div>
        @endif
        @if($invoice->client->phone)
            <div class="client-detail">{{ $invoice->client->phone }}</div>
        @endif
        @if($invoice->client->address)
            <div class="client-detail">{{ $invoice->client->address }}</div>
        @endif
        @if($invoice->client->city || $invoice->client->state)
            <div class="client-detail">
                {{ implode(', ', array_filter([$invoice->client->city, $invoice->client->state])) }}@if($invoice->client->pincode) — {{ $invoice->client->pincode }}@endif
            </div>
        @endif
        @if($invoice->client->gstin)
            <div class="gstin">GSTIN: {{ $invoice->client->gstin }}</div>
        @endif
    </div>

    <div class="invoice-dates">
        <div class="section-label">Invoice Details</div>
        <table class="date-table">
            <tr>
                <td class="date-label">Issue Date</td>
                <td class="date-value">{{ $invoice->issue_date->format('d M Y') }}</td>
            </tr>
            @if($invoice->due_date)
            <tr>
                <td class="date-label">Due Date</td>
                <td class="date-value">{{ $invoice->due_date->format('d M Y') }}</td>
            </tr>
            @endif
        </table>
    </div>
</div>

{{-- Line Items --}}
<table class="items-table">
    <thead>
        <tr>
            <th width="5%">#</th>
            <th width="50%">Description</th>
            <th width="12%" class="text-right">Qty</th>
            <th width="16%" class="text-right">Unit Price</th>
            <th width="17%" class="text-right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->description }}</td>
            <td class="text-right num">{{ number_format((float)$item->quantity, 2) }}</td>
            <td class="text-right num">{{ format_inr((float)$item->unit_price) }}</td>
            <td class="text-right num">{{ format_inr((float)$item->amount) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Totals --}}
<div class="totals-wrap clearfix">
    <table class="totals-table">
        <tr>
            <td>Subtotal</td>
            <td>{{ format_inr((float)$invoice->subtotal) }}</td>
        </tr>
        @if((float)$invoice->discount_amount > 0)
        <tr>
            <td>Discount</td>
            <td style="color:#dc2626;">− {{ format_inr((float)$invoice->discount_amount) }}</td>
        </tr>
        @endif
        @if((float)$invoice->tax_rate > 0)
        <tr>
            <td>GST ({{ number_format((float)$invoice->tax_rate, 2) }}%)</td>
            <td>{{ format_inr((float)$invoice->tax_amount) }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td>Total</td>
            <td>{{ format_inr((float)$invoice->total) }}</td>
        </tr>
    </table>
</div>

@if($invoice->notes)
<div class="notes">
    <div class="notes-label">Notes</div>
    <div>{{ $invoice->notes }}</div>
</div>
@endif

<div class="footer">
    Generated by {{ config('app.name') }} &nbsp;·&nbsp; {{ now()->format('d M Y') }}
</div>

</body>
</html>
