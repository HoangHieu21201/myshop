<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Coupon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\BirthdayCouponMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendBirthdayCoupons extends Command
{
    // Tên lệnh để chạy trong terminal (VD: php artisan sora:birthday-mail)
    protected $signature = 'sora:birthday-mail';

    protected $description = 'Quét danh sách người dùng có sinh nhật hôm nay, tạo mã giảm giá và gửi Email chúc mừng';

    public function handle()
    {
        $today = Carbon::today();
        $this->info("Bắt đầu quét sinh nhật cho ngày: " . $today->format('d/m/Y'));

        // LƯU Ý: Giả sử bảng users của sếp có cột 'date_of_birth' hoặc 'dob'.
        // Hãy đổi tên cột 'date_of_birth' bên dưới cho khớp với Database của sếp nhé!
        $birthdayUsers = User::whereMonth('date_of_birth', $today->month)
                             ->whereDay('date_of_birth', $today->day)
                             ->where('status', 'active') // Chỉ gửi cho tài khoản đang hoạt động
                             ->get();

        if ($birthdayUsers->isEmpty()) {
            $this->info('Hôm nay không có ai sinh nhật cả.');
            return 0;
        }

        $count = 0;
        foreach ($birthdayUsers as $user) {
            if (empty($user->email)) continue;

            try {
                // 1. Tự động sinh ra 1 Mã Coupon độc quyền cho người này
                $couponCode = 'HPBD' . $today->format('y') . strtoupper(Str::random(4)) . $user->id;
                
                $coupon = Coupon::create([
                    'code'           => $couponCode,
                    'name'           => 'Quà sinh nhật của ' . ($user->fullName ?? $user->name),
                    'type'           => 'percentage', // Giảm theo %
                    'value'          => 15,           // Tặng khách 15%
                    'min_spend'      => 0,            // Không yêu cầu đơn tối thiểu
                    'usage_limit'    => 1,            // Chỉ dùng được 1 lần
                    'usage_count'    => 0,
                    'status'         => 'active',
                    'expires_at'     => $today->copy()->addDays(14), // Hạn sử dụng 14 ngày kể từ sinh nhật
                ]);

                // 2. Ném Email vào Hàng đợi (Queue) để gửi ngầm, không làm nghẽn tiến trình
                Mail::to($user->email)->queue(new BirthdayCouponMail($user, $coupon));
                
                $count++;
                $this->info("Đã lên lịch gửi mail cho: {$user->email} - Mã: {$couponCode}");

            } catch (\Exception $e) {
                Log::error("Lỗi gửi mail sinh nhật cho User ID {$user->id}: " . $e->getMessage());
                $this->error("Lỗi gửi mail cho: {$user->email}");
            }
        }

        $this->info("Hoàn tất! Đã tạo và xếp hàng đợi gửi {$count} email chúc mừng sinh nhật.");
        return 0;
    }
}