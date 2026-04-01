<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class AdminNewOrderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Truyền dữ liệu đơn hàng vào Mail
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Xây dựng nội dung Email cho Admin
     */
    public function build()
    {
        // Thêm tiền tố [SORA SYSTEM] để Admin dễ nhận biết
        return $this->subject('[SORA SYSTEM] Đơn hàng mới: #' . $this->order->order_code)
                    ->view('emails.admin_new_order'); 
    }
}