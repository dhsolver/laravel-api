<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Http\Requests\ChangeEmailRequest;

class ChangeEmailActivation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Get the change email request object.
     *
     * @var ChangeEmailRequest
     */
    public $request;

    /**
     * Create a new message instance.
     *
     * @param ChangeEmailRequest $request
     * @return void
     */
    public function __construct(ChangeEmailRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Change Email Request Activation')
            ->markdown('mail.change-email-request');
    }
}
