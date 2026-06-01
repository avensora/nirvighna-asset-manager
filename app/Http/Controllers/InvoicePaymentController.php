<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\MonthClosing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoicePaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === InvoiceStatus::Paid) {
            return back()->with('error', 'This invoice is already fully paid.');
        }

        $paymentDate = $request->input('payment_date');
        if ($paymentDate) {
            $date = \Carbon\Carbon::parse($paymentDate);
            if (MonthClosing::isClosed($date->year, $date->month)) {
                return back()->with('error', "Cannot record a payment in a closed period ({$date->format('M Y')}).");
            }
        }

        $amountDue = $invoice->amountDue();

        $data = $request->validate([
            'amount'         => ['required', 'numeric', 'min:0.01', 'max:' . $amountDue],
            'payment_date'   => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference'      => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        $data['invoice_id']  = $invoice->id;
        $data['recorded_by'] = auth()->id();

        InvoicePayment::create($data);

        $this->syncInvoiceStatus($invoice);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($invoice)
            ->log("Recorded payment of ₹{$data['amount']} on invoice {$invoice->invoice_number}");

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function destroy(InvoicePayment $payment): RedirectResponse
    {
        $invoice = $payment->invoice;

        $payment->delete();

        $this->syncInvoiceStatus($invoice);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($invoice)
            ->log("Deleted payment of ₹{$payment->amount} from invoice {$invoice->invoice_number}");

        return back()->with('success', 'Payment removed.');
    }

    private function syncInvoiceStatus(Invoice $invoice): void
    {
        $invoice->refresh();
        $paid  = $invoice->amountPaid();
        $total = (float) $invoice->total;

        if ($paid <= 0) {
            $status = InvoiceStatus::Sent;
        } elseif ($paid >= $total) {
            $status = InvoiceStatus::Paid;
        } else {
            $status = InvoiceStatus::Partial;
        }

        $invoice->update(['status' => $status]);
    }
}
