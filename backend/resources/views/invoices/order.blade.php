<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Hóa đơn - {{ $order->order_code }}</title>
    <style>
        @page { margin: 30px; }
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0;
            padding: 0;
        }
        /* Header & Brand */
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0; }
        .brand { color: #9f273b; font-size: 32px; font-weight: bold; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 5px; }
        .subtitle { margin: 0; font-size: 0.85rem; color: #777; font-style: italic; letter-spacing: 1px; }
        
        .invoice-label { 
            margin-top: 20px;
            font-size: 20px; 
            font-weight: bold; 
            color: #333;
            text-transform: uppercase;
        }

        /* Customer & Order Info */
        .info-container { width: 100%; margin-bottom: 30px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { vertical-align: top; width: 50%; padding: 0; }
        
        .section-title { 
            color: #9f273b; 
            font-size: 0.75rem; 
            font-weight: bold; 
            text-transform: uppercase; 
            border-bottom: 1px solid #9f273b;
            display: inline-block;
            margin-bottom: 8px;
        }
        .info-content { font-size: 0.9rem; color: #444; }
        .info-content strong { color: #000; }

        /* Items Table */
        table.items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.items-table th { 
            background: #9f273b; 
            color: #ffffff; 
            text-align: left; 
            padding: 12px 10px; 
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        table.items-table td { 
            padding: 12px 10px; 
            border-bottom: 1px solid #eee; 
            font-size: 0.9rem; 
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }

        /* Summary Section */
        .summary-wrapper { margin-top: 20px; width: 100%; }
        .summary-table { float: right; width: 300px; border-collapse: collapse; }
        .summary-table td { padding: 5px 0; font-size: 0.9rem; }
        .summary-table .label { color: #666; }
        .summary-table .value { text-align: right; font-weight: bold; }
        
        .total-row td { 
            padding-top: 15px;
            border-top: 2px solid #333;
        }
        .total-label { font-size: 1.1rem; font-weight: bold; color: #9f273b; }
        .total-value { font-size: 1.3rem; font-weight: bold; color: #9f273b; }

        /* Footer */
        .footer { 
            margin-top: 80px; 
            text-align: center; 
            font-size: 0.8rem; 
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .signature-section { margin-top: 50px; width: 100%; }
        .signature-box { float: right; width: 200px; text-align: center; font-size: 0.9rem; }
        
        .clearfix { clear: both; }
        .discount-text { color: #28a745; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">SORA</div>
        <p class="subtitle">Premium Jewelry • Sophisticated • Elegant</p>
        <div class="invoice-label">Hóa Đơn Bán Hàng</div>
        <p style="margin: 5px 0; font-size: 0.9rem;">Mã đơn: <strong>#{{ $order->order_code }}</strong></p>
    </div>

    <div class="info-container">
        <table class="info-table">
            <tr>
                <td>
                    <div class="section-title">Thông tin khách hàng</div>
                    <div class="info-content">
                        <strong>{{ $order->customer_name }}</strong><br>
                        SĐT: {{ $order->customer_phone }}<br>
                        Email: {{ $order->customer_email ?? '---' }}<br>
                        Đ/C: {{ $order->customer_address }}
                    </div>
                </td>
                <td style="text-align: right;">
                    <div class="section-title">Chi tiết chứng từ</div>
                    <div class="info-content">
                        Ngày lập: <strong>{{ $order->created_at->format('d/m/Y H:i') }}</strong><br>
                        Hình thức: <strong>{{ strtoupper($order->payment_method) }}</strong><br>
                        Trạng thái đơn: <strong style="color: #9f273b;">{{ strtoupper($order->status) }}</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45%;">Mô tả sản phẩm</th>
                <th class="text-center" style="width: 15%;">Đơn giá</th>
                <th class="text-center" style="width: 10%;">SL</th>
                <th class="text-end" style="width: 25%;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    <div style="font-weight: bold;">{{ $item->product_name }}</div>
                    <div style="font-size: 0.75rem; color: #666;">SKU: {{ $item->variant_sku }}</div>
                </td>
                <td class="text-center">{{ number_format($item->price, 0, ',', '.') }} ₫</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-end">{{ number_format($item->total_price, 0, ',', '.') }} ₫</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-wrapper">
        <table class="summary-table">
            <tr>
                <td class="label">Tạm tính hàng:</td>
                <td class="value">{{ number_format($order->sub_total, 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td class="label">Phí vận chuyển:</td>
                <td class="value">{{ $order->shipping_fee > 0 ? number_format($order->shipping_fee, 0, ',', '.') . ' ₫' : 'Miễn phí' }}</td>
            </tr>
            
            {{-- ĐỒNG BỘ: HIỂN THỊ MÃ GIẢM GIÁ --}}
            @if($order->discount_amount > 0)
            <tr>
                <td class="label">Giảm giá ({{ $order->coupon_code ?? 'Mã KM' }}):</td>
                <td class="value discount-text">-{{ number_format($order->discount_amount, 0, ',', '.') }} ₫</td>
            </tr>
            @endif

            {{-- ĐỒNG BỘ: HIỂN THỊ ƯU ĐÃI HẠNG TV --}}
            @if(isset($order->tier_discount_amount) && $order->tier_discount_amount > 0)
            <tr>
                <td class="label">Ưu đãi Hạng TV:</td>
                <td class="value discount-text">-{{ number_format($order->tier_discount_amount, 0, ',', '.') }} ₫</td>
            </tr>
            @endif

            <tr class="total-row">
                <td class="total-label">TỔNG CỘNG:</td>
                <td class="total-value">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</td>
            </tr>
        </table>
        <div class="clearfix"></div>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <p style="margin-bottom: 60px;"><strong>Người lập hóa đơn</strong></p>
            <p><i>(Ký và ghi rõ họ tên)</i></p>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="footer">
        <p>Cảm ơn quý khách đã tin tưởng lựa chọn sản phẩm từ <strong>SORA Jewelry</strong>.</p>
        <p>Mọi thắc mắc về hóa đơn, vui lòng liên hệ Hotline: 1900.xxxx trong vòng 24h.</p>
        <p><i>Đây là hóa đơn điện tử được xuất tự động từ hệ thống quản lý ThinkHub.</i></p>
    </div>
</body>
</html>