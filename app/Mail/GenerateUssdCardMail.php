<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: GenerateUssdCardMail.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/27/26
 * Time: 12:57 PM
 */

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class GenerateUssdCardMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Collection $cards,
        protected $pdfOutput,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Generated USSD Voucher Cards',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.generate-ussd-card'
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfOutput, 'ussd_cards.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
