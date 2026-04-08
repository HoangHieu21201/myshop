<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HolidayCouponMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $coupon;
    public $holidayName;

    public function __construct($user, $coupon, $holidayName)
    {
        $this->user = $user;
        $this->coupon = $coupon;
        $this->holidayName = $holidayName;
    }

    public function build()
    {
        $name = $this->user->fullName ?? $this->user->name ?? 'Quý khách';
        return $this->subject("SORA Boutique 🎁 Ưu đãi đặc quyền dịp $this->holidayName!")
                    ->view('emails.holiday_coupon');
    }
}