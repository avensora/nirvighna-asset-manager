<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Mail\InvoiceMail;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Invoice::with('client')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(20)->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function create(): View
    {
        $clients = Client::orderBy('name')->get();
        return view('invoices.create', compact('clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'issue_date'      => 'required|date',
            'due_date'        => 'nullable|date|after_or_equal:issue_date',
            'tax_rate'        => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
            'items'           => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        $invoice = DB::transaction(function () use ($data) {
            $invoice = Invoice::create([
                'invoice_number'  => Invoice::generateNumber(),
                'client_id'       => $data['client_id'],
                'status'          => InvoiceStatus::Draft,
                'issue_date'      => $data['issue_date'],
                'due_date'        => $data['due_date'] ?? null,
                'tax_rate'        => $data['tax_rate'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'notes'           => $data['notes'] ?? null,
                'created_by'      => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'amount'      => round($item['quantity'] * $item['unit_price'], 2),
                ]);
            }

            $this->recalculateTotals($invoice);

            return $invoice;
        });

        activity()
            ->causedBy(auth()->user())
            ->performedOn($invoice)
            ->withProperties(['number' => $invoice->invoice_number])
            ->log('Created invoice');

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} created.");
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load('items', 'client');
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View|RedirectResponse
    {
        if ($invoice->status === InvoiceStatus::Paid) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Paid invoices cannot be edited.');
        }

        $clients = Client::orderBy('name')->get();
        $invoice->load('items');
        return view('invoices.edit', compact('invoice', 'clients'));
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === InvoiceStatus::Paid) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Paid invoices cannot be edited.');
        }

        $data = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'issue_date'      => 'required|date',
            'due_date'        => 'nullable|date|after_or_equal:issue_date',
            'tax_rate'        => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
            'items'           => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data, $invoice) {
            $invoice->items()->delete();

            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'amount'      => round($item['quantity'] * $item['unit_price'], 2),
                ]);
            }

            $invoice->update([
                'client_id'       => $data['client_id'],
                'issue_date'      => $data['issue_date'],
                'due_date'        => $data['due_date'] ?? null,
                'tax_rate'        => $data['tax_rate'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'notes'           => $data['notes'] ?? null,
            ]);

            $this->recalculateTotals($invoice);
        });

        activity()
            ->causedBy(auth()->user())
            ->performedOn($invoice)
            ->withProperties(['number' => $invoice->invoice_number])
            ->log('Updated invoice');

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} updated.");
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === InvoiceStatus::Paid) {
            return back()->with('error', 'Paid invoices cannot be deleted.');
        }

        $number = $invoice->invoice_number;

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['number' => $number])
            ->log('Deleted invoice');

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', "Invoice {$number} deleted.");
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load('items', 'client');
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download("{$invoice->invoice_number}.pdf");
    }

    public function sendEmail(Invoice $invoice): RedirectResponse
    {
        if (! $invoice->client->email) {
            return back()->with('error', 'This client has no email address on record.');
        }

        $invoice->load('items', 'client');

        Mail::to($invoice->client->email)->queue(new InvoiceMail($invoice));

        if ($invoice->status === InvoiceStatus::Draft) {
            $invoice->update(['status' => InvoiceStatus::Sent]);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($invoice)
            ->withProperties(['email' => $invoice->client->email])
            ->log('Sent invoice by email');

        return back()->with('success', "Invoice sent to {$invoice->client->email}.");
    }

    public function markPaid(Invoice $invoice): RedirectResponse
    {
        $invoice->update(['status' => InvoiceStatus::Paid]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($invoice)
            ->log('Marked invoice as paid');

        return back()->with('success', "Invoice {$invoice->invoice_number} marked as paid.");
    }

    public function markUnpaid(Invoice $invoice): RedirectResponse
    {
        $invoice->update(['status' => InvoiceStatus::Sent]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($invoice)
            ->log('Marked invoice as unpaid');

        return back()->with('success', "Invoice {$invoice->invoice_number} marked as unpaid.");
    }

    private function recalculateTotals(Invoice $invoice): void
    {
        $invoice->refresh()->load('items');
        $subtotal  = (float) $invoice->items->sum('amount');
        $taxAmount = round($subtotal * ((float) $invoice->tax_rate / 100), 2);
        $total     = $subtotal - (float) $invoice->discount_amount + $taxAmount;

        $invoice->update([
            'subtotal'   => $subtotal,
            'tax_amount' => $taxAmount,
            'total'      => max(0, $total),
        ]);
    }
}
