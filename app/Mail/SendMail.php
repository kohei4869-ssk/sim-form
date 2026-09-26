<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    // Excelファイルの中身（バイナリ）とファイル名。attachExcel()を呼ばなければ null のまま＝添付なし。
    private ?string $attachmentBinary = null;
    private ?string $attachmentName = null;

    public function __construct(
        public string $mailSubject,
        public string $bodyText,
    ) {}

    /**
     * 見積もりのExcelファイルをこのメールに添付する。
     * コントローラー側で new SendMail(...)->attachExcel($binary, 'estimate.xlsx') のように使う。
     */
    public function attachExcel(string $binary, string $fileName): static
    {
        $this->attachmentBinary = $binary;
        $this->attachmentName = $fileName;

        return $this;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.send-mail',
        );
    }

    public function attachments(): array
    {
        if (!$this->attachmentBinary) {
            return [];
        }

        return [
            Attachment::fromData(fn () => $this->attachmentBinary, $this->attachmentName)
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}