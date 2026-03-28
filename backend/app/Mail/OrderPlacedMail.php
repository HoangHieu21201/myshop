<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderPlacedMail extends Mailable
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
     * Xây dựng nội dung Email
     */
    public function build()
    {
        return $this->subject('Xác nhận đơn hàng #' . $this->order->order_code . ' từ SORA Jewelry')
            ->view('emails.order_placed'); // Trỏ tới file giao diện Blade bên dưới
    }
}
