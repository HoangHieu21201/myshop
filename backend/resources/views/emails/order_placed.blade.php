<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f4f4; padding: 20px; color: #333; line-height: 1.6; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        .header { background-color: #ffffff; text-align: center; padding: 30px 20px; border-bottom: 4px solid #9f273b; }
        .header h1 { margin: 0; font-size: 28px; letter-spacing: 2px; color: #9f273b; font-weight: bold; text-transform: uppercase; }
        
        .body { padding: 30px; }
        .body h2 { color: #111; font-size: 20px; margin-top: 0; }
        
        .order-info { background-color: #fcfcfc; padding: 20px; border-radius: 6px; margin-bottom: 25px; border-left: 4px solid #e7ce7d; font-size: 14px; color: #555; }
        .order-info strong { color: #333; display: inline-block; width: 140px; }
        
        .table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .table th, .table td { padding: 12px 10px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        .table th { background-color: #fcfaf8; color: #9f273b; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e7ce7d; }
        .table td { font-size: 14px; vertical-align: top; }
        
        .product-name { font-weight: bold; color: #333; margin-bottom: 4px; display: block; }
        .variant-sku { color: #888; font-size: 12px; }
        
        .summary-row td { padding: 8px 10px; border-bottom: none; font-size: 14px; }
        .total-row td { font-weight: bold; color: #9f273b; font-size: 18px; padding-top: 15px; border-top: 2px solid #f0f0f0; }
        
        .footer { text-align: center; padding: 25px 20px; font-size: 12px; color: #888; background-color: #fcfcfc; border-top: 1px solid #eeeeee; }
        .footer p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>SORA JEWELRY</h1>
        </div>
        
        <div class="body">
            <h2>Cảm ơn bạn đã tin chọn SORA!</h2>
            <p>Xin chào <strong>{{ $order->customer_name }}</strong>,</p>
            <p>Chúng tôi đã nhận được đơn đặt hàng của bạn và đang tiến hành chuẩn bị những kiệt tác trang sức. Dưới đây là thông tin chi tiết đơn hàng:</p>
            
            <div class="order-info">
                <strong>Mã đơn hàng:</strong> <span style="font-family: monospace; font-size: 15px; font-weight: bold; color: #9f273b;">{{ $order->order_code }}</span><br>
                <strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}<br>
                <strong>Phương thức TT:</strong> {{ $order->payment_method === 'cod' ? 'Thanh toán khi nhận hàng (COD)' : 'Thanh toán qua Ví MoMo / ATM' }}<br>
                <strong>Trạng thái TT:</strong> <span style="font-weight: bold; color: {{ $order->payment_status === 'paid' ? '#28a745' : '#e0a800' }}">{{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán' }}</span><br>
                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #eee;">
                    <strong>Người nhận:</strong> {{ $order->customer_name }} ({{ $order->customer_phone }})<br>
                    <strong>Giao đến:</strong> {{ $order->customer_address }}
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th style="text-align: center;">SL</th>
                        <th style="text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            <span class="product-name">{{ $item->product_name }}</span>
                            <span class="variant-sku">SKU: {{ $item->variant_sku }}</span>
                        </td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right; font-weight: 500;">{{ number_format($item->total_price, 0, ',', '.') }}đ</td>
                    </tr>
                    @endforeach
                    
                    <tr class="summary-row">
                        <td colspan="2" style="text-align: right; padding-top: 20px;">Tạm tính:</td>
                        <td style="text-align: right; padding-top: 20px;">{{ number_format($order->sub_total, 0, ',', '.') }}đ</td>
                    </tr>
                    @if($order->shipping_fee > 0)
                    <tr class="summary-row">
                        <td colspan="2" style="text-align: right;">Phí giao hàng an toàn:</td>
                        <td style="text-align: right;">{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</td>
                    </tr>
                    @else
                    <tr class="summary-row">
                        <td colspan="2" style="text-align: right;">Phí giao hàng an toàn:</td>
                        <td style="text-align: right; color: #28a745; font-weight: bold;">Miễn phí</td>
                    </tr>
                    @endif
                    @if($order->discount_amount > 0)
                    <tr class="summary-row">
                        <td colspan="2" style="text-align: right;">Ưu đãi ({{ $order->coupon_code }}):</td>
                        <td style="text-align: right; color: #28a745;">-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td colspan="2" style="text-align: right;">Tổng thanh toán:</td>
                        <td style="text-align: right;">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                    </tr>
                </tbody>
            </table>

            <p style="font-size: 13px; color: #666; font-style: italic;">
                * SORA sẽ sớm liên hệ với bạn để xác nhận lại thời gian và địa điểm giao hàng nhằm đảm bảo an toàn tuyệt đối cho món đồ trang sức của bạn.
            </p>
        </div>
        
        <div class="footer">
            <p>Trân trọng,</p>
            <p style="font-weight: bold; color: #9f273b; font-size: 14px;">Đội ngũ SORA Jewelry</p>
            <p style="margin-top: 15px;">&copy; {{ date('Y') }} SORA Jewelry. Biểu tượng của sự tinh tế.</p>
        </div>
    </div>
</body>
</html>