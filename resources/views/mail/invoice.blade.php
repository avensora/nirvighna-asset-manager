@component('mail::message')
# Invoice {{ $invoice->invoice_number }}

Dear {{ $invoice->client->name }},

Please find attached **Invoice {{ $invoice->invoice_number }}** for **{{ format_inr((float)$invoice->total) }}**.

@if($invoice->due_date)
**Due Date:** {{ $invoice->due_date->format('d M Y') }}
@endif

---

@component('mail::table')
| # | Description | Qty | Unit Price | Amount |
|---|:------------|----:|-----------:|-------:|
@foreach($invoice->items as $i => $item)
| {{ $i + 1 }} | {{ $item->description }} | {{ number_format((float)$item->quantity, 2) }} | {{ format_inr((float)$item->unit_price) }} | {{ format_inr((float)$item->amount) }} |
@endforeach
@endcomponent

| | |
|-|-:|
| Subtotal | {{ format_inr((float)$invoice->subtotal) }} |
@if((float)$invoice->discount_amount > 0)
| Discount | − {{ format_inr((float)$invoice->discount_amount) }} |
@endif
@if((float)$invoice->tax_rate > 0)
| GST ({{ number_format((float)$invoice->tax_rate, 2) }}%) | {{ format_inr((float)$invoice->tax_amount) }} |
@endif
| **Total** | **{{ format_inr((float)$invoice->total) }}** |

@if($invoice->notes)
---
**Notes:** {{ $invoice->notes }}
@endif

The invoice PDF is attached to this email.

Thanks for your business,<br>
{{ config('app.name') }}
@endcomponent
