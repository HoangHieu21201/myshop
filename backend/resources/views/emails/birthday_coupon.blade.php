<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #fcfaf8; padding: 20px; color: #333; margin: 0; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        
        .header { background: linear-gradient(135deg, #9f273b 0%, #cc1e2e 100%); text-align: center; padding: 40px 20px; color: #fff; }
        .header h1 { margin: 0; font-size: 32px; letter-spacing: 4px; font-family: 'Times New Roman', serif; text-transform: uppercase; color: #e7ce7d; }
        .header p { margin: 10px 0 0 0; font-size: 18px; letter-spacing: 2px; text-transform: uppercase; }
        
        .body { padding: 40px 30px; text-align: center; }
        .body h2 { color: #9f273b; font-size: 24px; margin-top: 0; font-family: 'Times New Roman', serif; }
        .body p { font-size: 16px; line-height: 1.6; color: #555; }
        
        .coupon-box { margin: 30px auto; max-width: 400px; background-color: #fdfbf7; border: 2px dashed #e7ce7d; padding: 25px; border-radius: 12px; }
        .coupon-box .discount { font-size: 36px; font-weight: bold; color: #9f273b; margin: 0; font-family: 'Times New Roman', serif; }
        .coupon-box .code { font-size: 22px; letter-spacing: 3px; font-weight: bold; color: #111; margin: 15px 0; padding: 10px; background: #fff; border: 1px solid #eee; display: inline-block; border-radius: 4px; }
        .coupon-box .expiry { font-size: 13px; color: #888; margin: 0; }
        
        .btn-wrapper { margin-top: 30px; }
        .btn { display: inline-block; padding: 15px 35px; background-color: #9f273b; color: #ffffff !important; text-decoration: none; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; font-size: 14px; transition: background 0.3s; }
        
        .footer { text-align: center; padding: 25px 20px; font-size: 13px; color: #999; background-color: #fafafa; border-top: 1px solid #f0f0f0; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>SORA</h1>
            <p>Happy Birthday</p>
        </div>
        
        <div class="body">
            <h2>Gửi {{ $user->fullName ?? $user->name }},</h2>
            <p>Nhân ngày đặc biệt nhất trong năm của bạn, SORA Jewelry xin gửi những lời chúc tốt đẹp nhất. Chúc bạn luôn rạng rỡ, hạnh phúc và tỏa sáng như những viên kim cương vĩnh cửu.</p>
            <p>Để đánh dấu ngày kỷ niệm này, chúng tôi dành tặng riêng bạn một món quà đặc quyền:</p>
            
            <div class="coupon-box">
                <p class="discount">GIẢM {{ $coupon->value }}%</p>
                <p style="margin: 5px 0 10px 0; font-size: 14px; color: #666;">Cho mọi đơn hàng trang sức tại SORA</p>
                <div class="code">{{ $coupon->code }}</div>
                <p class="expiry">Mã có hiệu lực đến hết ngày: <strong>{{ \Carbon\Carbon::parse($coupon->expires_at)->format('d/m/Y') }}</strong></p>
            </div>

            <div class="btn-wrapper">
                <!-- Thay đổi đường dẫn Frontend cho đúng -->
                <a href="http://localhost:5173/shop" class="btn">Mua Sắm Ngay</a>
            </div>
            
            <p style="margin-top: 30px; font-size: 14px; color: #777;">*Mã ưu đãi này chỉ dành riêng cho tài khoản của bạn và có thể áp dụng 1 lần duy nhất.</p>
        </div>
        
        <div class="footer">
            <p>SORA JEWELRY - Đẳng cấp trang sức sang trọng</p>
            <p>Mọi thắc mắc xin vui lòng liên hệ Hotline: 1900 xxxx</p>
        </div>
    </div>
</body>
</html>