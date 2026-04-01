<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7f6; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 2px solid #009981; padding-bottom: 15px; margin-bottom: 25px; }
        .header h2 { color: #009981; margin: 0; font-size: 24px; letter-spacing: 2px; }
        .content { background: #f8f9fc; padding: 20px; border-radius: 6px; border-left: 4px solid #009981; margin: 20px 0; }
        .content-reject { border-left-color: #dc3545; }
        .amount { font-size: 22px; color: #dc3545; font-weight: bold; }
        .note-box { font-style: italic; color: #555; background: #fff; padding: 10px 15px; border-radius: 4px; margin-top: 10px; border: 1px solid #eee; }
        .footer { margin-top: 30px; font-size: 13px; text-align: center; color: #888; border-top: 1px solid #eee; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>SORA BOUTIQUE</h2>
        </div>
        
        <p>Xin chào <strong>{{ $order->customer_name }}</strong>,</p>
        
        @if($actionType === 'propose')
            <p>SORA đã tiếp nhận và hoàn tất quá trình kiểm định sản phẩm hoàn trả thuộc đơn hàng <strong>#{{ $order->order_code }}</strong>.</p>
            
            <div class="content">
                <p style="margin-top: 0;"><strong>Thông báo Thỏa thuận & Khấu trừ:</strong></p>
                <div class="note-box">
                    "{!! nl2br(e($order->refund_note)) !!}"
                </div>
                <p style="margin-bottom: 0; margin-top: 15px;">Số tiền SORA đề xuất hoàn lại cho quý khách là: <br><span class="amount">{{ number_format($order->refund_amount, 0, ',', '.') }} VNĐ</span></p>
            </div>
            
            <p>Nếu quý khách đồng ý với thỏa thuận trên, vui lòng phản hồi lại Email này kèm theo <strong>Thông tin Số tài khoản ngân hàng</strong> để Bộ phận Kế toán của SORA tiến hành chuyển khoản nhanh nhất.</p>
        @else
            <p>SORA đã tiếp nhận và kiểm định sản phẩm hoàn trả thuộc đơn hàng <strong>#{{ $order->order_code }}</strong>. Rất tiếc, chúng tôi không thể chấp nhận yêu cầu hoàn tiền của quý khách.</p>
            
            <div class="content content-reject">
                <p style="margin-top: 0; color: #dc3545;"><strong>Lý do từ chối:</strong></p>
                <div class="note-box">
                    "{!! nl2br(e($order->refund_note)) !!}"
                </div>
            </div>
            
            <p>Sản phẩm sẽ được đóng gói cẩn thận và gửi trả lại theo địa chỉ ban đầu của quý khách trong thời gian sớm nhất.</p>
        @endif

        <p style="margin-top: 30px;">Trân trọng,<br><strong>Đội ngũ Chăm sóc khách hàng SORA</strong></p>
        
        <div class="footer">
            Đây là email tự động từ hệ thống SORA Boutique. Mọi thắc mắc vui lòng liên hệ Hotline: 1900 xxxx.
        </div>
    </div>
</body>
</html>