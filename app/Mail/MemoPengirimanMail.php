<?php

namespace App\Mail;

use App\Models\MemoPengiriman;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemoPengirimanMail extends Mailable
{
    use Queueable, SerializesModels;

    public MemoPengiriman $memo;
    public string $pdfBinary;
    public string $pdfFilename;

    /**
     * NOTE: Mailable ini TIDAK di-queue (tidak implements ShouldQueue),
     * jadi dikirim secara sinkron via Mail::send() — aman untuk membawa
     * $pdfBinary sebagai property biasa tanpa masalah serialisasi queue.
     */
    public function __construct(MemoPengiriman $memo, string $pdfBinary, string $pdfFilename)
    {
        $this->memo        = $memo;
        $this->pdfBinary   = $pdfBinary;
        $this->pdfFilename = $pdfFilename;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Memo Pengiriman Barang — ' . $this->memo->nomor_memo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.memo-pengiriman',
            with: [
                'memo' => $this->memo,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfBinary, $this->pdfFilename)
                ->withMime('application/pdf'),
        ];
    }
}