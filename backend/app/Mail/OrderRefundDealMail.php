<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderRefundDealMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $actionType;

    public function __construct(Order $order, $actionType)
    {
        $this->order = $order;
        $this->actionType = $actionType; 
    }

    public function build()
    {
        $subject = $this->actionType === 'propose' 
            ? "SORA - Thông báo Thỏa thuận hoàn tiền đơn hàng #{$this->order->order_code}"
            : "SORA - Từ chối yêu cầu hoàn tiền đơn hàng #{$this->order->order_code}";

        return $this->subject($subject)
                    ->view('emails.order_refund_deal');
    }
}