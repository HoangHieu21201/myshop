<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BirthdayCouponMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $coupon;

    public function __construct($user, $coupon)
    {
        $this->user = $user;
        $this->coupon = $coupon;
    }

    public function build()
    {
        $name = $this->user->fullName ?? $this->user->name ?? 'Bạn';
        
        return $this->subject("SORA Jewelry 🎁 Chúc mừng sinh nhật $name!")
                    ->view('emails.birthday_coupon');
    }
}