<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
    .header { margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    .header h1 { font-size: 18px; font-weight: bold; }
    .header p { font-size: 11px; color: #666; margin-top: 3px; }
    .summary { display: flex; gap: 20px; margin-bottom: 16px; }
    .summary-box { border: 1px solid #ddd; border-radius: 4px; padding: 8px 14px; min-width: 160px; }
    .summary-box .label { font-size: 10px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
    .summary-box .value { font-size: 14px; font-weight: bold; margin-top: 2px; }
    .text-success { color: #1a7a4a; }
    .text-danger  { color: #c0392b; }
    .text-primary { color: #2563eb; }
    table { width: 100%; border-collapse: collapse; }
    thead th { background: #f3f4f6; border-bottom: 2px solid #ddd; padding: 7px 8px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.4px; color: #555; }
    tbody td { padding: 6px 8px; border-bottom: 1px solid #eee; vertical-align: top; }
    tbody tr:last-child td { border-bottom: none; }
    .text-right { text-align: right; }
    .badge { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 10px; font-weight: 600; }
    .badge-income  { background: #d1fae5; color: #065f46; }
    .badge-expense { background: #fee2e2; color: #991b1b; }
    .footer { margin-top: 20px; font-size: 10px; color: #aaa; text-align: right; }
</style>
</head>
<body>

<div class="header">
    <h1>Transaction Statement</h1>
    <p>Generated {{ now()->format('d M Y, h:i A') }}</p>
</div>

<table style="width:auto; border-collapse:collapse; margin-bottom:16px;">
    <tr>
        <td style="padding:5px 16px 5px 0; border:none;">
            <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Total Income</div>
            <div style="font-size:15px;font-weight:bold;color:#1a7a4a;">{{ format_inr((float)$totals['income']) }}</div>
        </td>
        <td style="padding:5px 16px; border-left:1px solid #ddd;">
            <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Total Expenses</div>
            <div style="font-size:15px;font-weight:bold;color:#c0392b;">{{ format_inr((float)$totals['expense']) }}</div>
        </td>
        <td style="padding:5px 0 5px 16px; border-left:1px solid #ddd;">
            @php $net = (float)$totals['income'] - (float)$totals['expense']; @endphp
            <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Net</div>
            <div style="font-size:15px;font-weight:bold;color:{{ $net >= 0 ? '#2563eb' : '#c0392b' }};">
                {{ $net < 0 ? '−' : '' }}{{ format_inr(abs($net)) }}
            </div>
        </td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Category</th>
            <th>Description</th>
            <th>Reference</th>
            <th class="text-right">Amount (INR)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($transactions as $txn)
        <tr>
            <td style="white-space:nowrap;">{{ $txn->date->format('d M Y') }}</td>
            <td>
                <span class="badge {{ $txn->type === \App\Enums\TransactionType::Income ? 'badge-income' : 'badge-expense' }}">
                    {{ $txn->type->label() }}
                </span>
            </td>
            <td>{{ $txn->category }}</td>
            <td style="color:#666;">{{ $txn->description ?? '' }}</td>
            <td style="color:#888;">{{ $txn->reference ?? '' }}</td>
            <td class="text-right" style="font-weight:600; white-space:nowrap; color:{{ $txn->type === \App\Enums\TransactionType::Income ? '#1a7a4a' : '#c0392b' }}">
                {{ $txn->type === \App\Enums\TransactionType::Expense ? '−' : '' }}{{ format_inr((float)$txn->amount) }}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" style="text-align:center; color:#aaa; padding:20px;">No transactions found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">{{ $transactions->count() }} record(s) &nbsp;|&nbsp; Nirvighna</div>

</body>
</html>
