<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    /**
     * @var string $code
     */
    protected $code;

    //protected $connection = 'sqs';

    /**
     * ResetPasswordMail constructor.
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     * @throws \ReflectionException
     */
    public function build()
    {
        $code = $this->code;
        return $this->html($this->view('emails.password_reset')->with(compact('code'))->render());
    }
}
