<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function build()
    {
        $resetUrl = route('password.reset', ['token' => $this->token]) . '?email=' . urlencode($this->email);
        
        return $this->subject('Đặt lại mật khẩu - MovieHub')
                    ->view('emails.password-reset', [
                        'token' => $this->token,
                        'email' => $this->email,
                        'resetUrl' => $resetUrl,
                    ]);
    }
}
