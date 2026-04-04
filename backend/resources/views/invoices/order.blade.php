<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn - {{ $order->order_code }}</title>
    <style>
        @page { margin: 20px; }
        body { font-family: 'DejaVu Sans', sans-serif; line-height: 1.6; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 4px solid #9f273b; padding-bottom: 20px; }
        .brand { color: #9f273b; font-size: 32px; font-weight: bold; letter-spacing: 2px; }
        .info { display: flex; justify-content: space-between; margin: 25px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px 10px; text-align: left; }
        th { background: #f8f9fa; font-weight: 600; }
        .text-end { text-align: right; }
        .total { font-size: 18px; font-weight: bold; text-align: right; }
        .footer { margin-top: 60px; text-align: center; font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="brand">SORA </h1>
        <p>Trang sức cao cấp • Tinh tế • Sang trọng</p>
        <h2 style="color:#9f273b;">HÓA ĐƠN BÁN HÀNG</h2>
        <p><strong>Mã đơn hàng:</strong> {{ $order->order_code }}</p>
    </div>

    <div class="info">
        <div>
            <strong>Khách hàng:</strong><br>
            {{ $order->customer_name }}<br>
            {{ $order->customer_phone }}<br>
            {{ $order->customer_email ?? 'Không có email' }}<br>
            {{ $order->customer_address }}
        </div>
        <div style="text-align: right;">
            <strong>Ngày đặt:</strong><br>
            {{ $order->created_at->format('d/m/Y H:i') }}<br><br>
            <strong>Trạng thái:</strong><br>
            <span style="color:#9f273b;">{{ ucfirst($order->status) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th class="text-end">Đơn giá</th>
                <th class="text-end">Số lượng</th>
                <th class="text-end">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td class="text-end">{{ number_format($item->price) }} ₫</td>
                <td class="text-end">{{ $item->quantity }}</td>
                <td class="text-end">{{ number_format($item->total_price) }} ₫</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <p>Tạm tính: <span style="float:right;">{{ number_format($order->sub_total) }} ₫</span></p>
        @if($order->discount_amount > 0)
        <p>Giảm giá: <span style="float:right;color:#28a745;">-{{ number_format($order->discount_amount) }} ₫</span></p>
        @endif
        <p>Phí vận chuyển: <span style="float:right;">{{ $order->shipping_fee > 0 ? number_format($order->shipping_fee) . ' ₫' : 'Miễn phí' }}</span></p>
        <hr>
        <h3>TỔNG THANH TOÁN: <span style="color:#9f273b;">{{ number_format($order->total_amount) }} ₫</span></h3>
    </div>

    <div class="footer">
        <p>Cảm ơn quý khách đã tin tưởng và ủng hộ <strong>SORA Jewelry</strong>!</p>
        <p>Hóa đơn này được xuất tự động từ hệ thống và có giá trị pháp lý.</p>
    </div>
</body>
</html>