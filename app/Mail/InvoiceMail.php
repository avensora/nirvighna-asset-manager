<?php

namespace App\Mail;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice {$this->invoice->invoice_number} — " . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.invoice',
        );
    }

    public function attachments(): array
    {
        $invoice = $this->invoice->loadMissing('items', 'client');
        $pdf     = Pdf::loadView('invoices.pdf', compact('invoice'));

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                "{$invoice->invoice_number}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
