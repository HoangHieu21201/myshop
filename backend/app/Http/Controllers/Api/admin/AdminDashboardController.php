<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; 

class AdminDashboardController extends Controller
{
    /**
     * Hàm dùng chung để lọc các đơn hàng được tính vào doanh thu.
     * Logic MỚI CHUẨN: (Đã giao HOẶC Đã thanh toán) VÀ KHÔNG NẰM TRONG (Hủy, Đã hoàn trả, Đang yêu cầu hoàn trả)
     */
    private function applyRevenueFilter($query, $tablePrefix = '')
    {
        $statusCol = $tablePrefix ? $tablePrefix . '.status' : 'status';
        $paymentStatusCol = $tablePrefix ? $tablePrefix . '.payment_status' : 'payment_status';

        return $query->where(function ($q) use ($statusCol, $paymentStatusCol) {
            // Điều kiện 1: Lấy tất cả những thằng Đã giao hoặc Đã thanh toán
            $q->where($statusCol, 'delivered')
              ->orWhere($paymentStatusCol, 'paid');
        })
        // Điều kiện 2: Trừ đi (Loại bỏ) dứt điểm những thằng bị Hủy hoặc liên quan đến Hoàn trả
        ->whereNotIn($statusCol, ['cancelled', 'returned', 'return_requested']);
    }

    /**
     * Hàm tính phần trăm tăng/giảm
     */
    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0; // Nếu kỳ trước bằng 0 mà kỳ này có => Tăng 100%
        }
        return round((($current - $previous) / $previous) * 100, 1); // Làm tròn 1 chữ số thập phân
    }

    public function index()
    {
        try {
            // 1. TỔNG QUAN
            $revenueQuery = Order::query();
            $totalRevenue = $this->applyRevenueFilter($revenueQuery)->sum('total_amount') ?? 0; 

            $newOrders = Order::whereDate('created_at', Carbon::today())->count();
            
            // Đếm toàn bộ User (Bảng users của bạn chỉ lưu khách hàng)
            $totalCustomers = User::count();
                
            // Tính tổng tồn kho từ bảng product_variants
            $inventory = Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'stock') 
                ? DB::table('product_variants')->whereNull('deleted_at')->sum('stock') 
                : 0;

            // ==========================================
            // TÍNH TOÁN % TĂNG/GIẢM SO VỚI KỲ TRƯỚC
            // ==========================================
            $now = Carbon::now();
            $thisMonthStart = $now->copy()->startOfMonth();
            $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
            $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

            // A. Tăng trưởng Doanh thu (Tháng này vs Tháng trước)
            $revenueThisMonth = $this->applyRevenueFilter(Order::query())->where('created_at', '>=', $thisMonthStart)->sum('total_amount') ?? 0;
            $revenueLastMonth = $this->applyRevenueFilter(Order::query())->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('total_amount') ?? 0;
            $revenueGrowth = $this->calculatePercentageChange($revenueThisMonth, $revenueLastMonth);

            // B. Tăng trưởng Đơn hàng (Hôm nay vs Hôm qua)
            $ordersYesterday = Order::whereDate('created_at', Carbon::yesterday())->count();
            $ordersGrowth = $this->calculatePercentageChange($newOrders, $ordersYesterday);

            // C. Tăng trưởng Khách hàng (Tháng này vs Tháng trước)
            $customersThisMonth = User::where('created_at', '>=', $thisMonthStart)->count();
            $customersLastMonth = User::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
            $customersGrowth = $this->calculatePercentageChange($customersThisMonth, $customersLastMonth);


            // 2. ĐƠN HÀNG GẦN ĐÂY
            $recentOrdersRaw = Order::with('user')->orderBy('created_at', 'desc')->take(5)->get();
            $recentOrders = $recentOrdersRaw->map(function($order) {
                return [
                    'id' => $order->id,
                    'code' => $order->order_code ?? 'ORD-' . str_pad($order->id, 4, '0', STR_PAD_LEFT), 
                    // Dùng fullName từ User model của bạn
                    'customer' => $order->user ? $order->user->fullName : ($order->customer_name ?? 'Khách lẻ'), 
                    'date' => $order->created_at->format('d/m/Y H:i'),
                    'total' => (float) ($order->total_amount ?? 0), 
                    'status' => $order->status ?? 'Mới',
                ];
            });

            // 3. SẢN PHẨM BÁN CHẠY
            $productsQuery = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereNull('orders.deleted_at') // Bỏ qua đơn hàng đã xóa mềm
                ->whereNotNull('order_items.product_id'); // Bỏ qua Combo
                
            // Áp dụng cùng 1 logic doanh thu cho Sản phẩm bán chạy
            $topProductsRaw = $this->applyRevenueFilter($productsQuery, 'orders')
                ->select('order_items.product_id', DB::raw('SUM(order_items.quantity) as total_sold'))
                ->groupBy('order_items.product_id')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get();

            $topProducts = $topProductsRaw->map(function($item) {
                // Dùng withTrashed() để tránh lỗi nếu sản phẩm đó đã bị người quản trị xóa mềm
                $product = Product::withTrashed()->find($item->product_id);
                
                // Lấy tồn kho của tất cả biến thể thuộc sản phẩm này
                $stock = 0;
                if ($product && Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'stock')) {
                    $stock = DB::table('product_variants')
                        ->where('product_id', $product->id)
                        ->whereNull('deleted_at')
                        ->sum('stock');
                }

                // Lấy thông tin lưu trữ trong order_items đề phòng product bị xóa vĩnh viễn
                $snapshot = DB::table('order_items')->where('product_id', $item->product_id)->first();

                return [
                    'id' => $item->product_id,
                    'name' => $product ? $product->name : ($snapshot->product_name ?? 'Sản phẩm đã ngừng bán'),
                    'sold' => (int) $item->total_sold,
                    'stock' => $stock,
                    'price' => $product ? ($product->promotional_price ?? $product->base_price) : ($snapshot->price ?? 0), 
                    // Dùng thumbnail_image theo đúng Model Product của bạn
                    'image' => $product && $product->thumbnail_image ? asset('storage/' . $product->thumbnail_image) : '', 
                ];
            });

            // 4. BIỂU ĐỒ 7 NGÀY
            $chartData = $this->getChartData(Carbon::today()->subDays(6), Carbon::today());

            return response()->json([
                'status' => true,
                'message' => 'Lấy dữ liệu Dashboard thành công',
                'data' => [
                    'stats' => [
                        'totalRevenue' => (float) $totalRevenue,
                        'revenueGrowth' => $revenueGrowth, // % tăng/giảm doanh thu
                        'newOrders' => $newOrders,
                        'ordersGrowth' => $ordersGrowth,   // % tăng/giảm đơn hàng mới
                        'inventory' => (int) $inventory,
                        'totalCustomers' => $totalCustomers,
                        'customersGrowth' => $customersGrowth // % tăng/giảm khách hàng
                    ],
                    'recentOrders' => $recentOrders,
                    'topProducts' => $topProducts,
                    'chartData' => $chartData
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi Code Laravel: ' . $e->getMessage() . ' (Dòng: ' . $e->getLine() . ')'
            ], 500);
        }
    }

    public function chart(Request $request)
    {
        try {
            $range = $request->input('range', '7');
            $startDate = null;
            $endDate = Carbon::today();

            if ($range === 'custom' && $request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::parse($request->input('start_date'));
                $endDate = Carbon::parse($request->input('end_date'));
            } elseif ($range === 'this_month') {
                $startDate = Carbon::today()->startOfMonth();
                $endDate = Carbon::today()->endOfMonth(); 
            } else {
                $days = (int) $range;
                $startDate = Carbon::today()->subDays($days - 1);
            }

            if ($startDate > $endDate) {
                $temp = $startDate;
                $startDate = $endDate;
                $endDate = $temp;
            }

            if ($startDate->diffInDays($endDate) > 60) {
                $startDate = $endDate->copy()->subDays(60);
            }

            $chartData = $this->getChartData($startDate, $endDate);

            return response()->json([
                'status' => true,
                'data' => $chartData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi biểu đồ Laravel: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getChartData($startDate, $endDate)
    {
        $ordersQuery = Order::where('created_at', '>=', $startDate->copy()->startOfDay())
                            ->where('created_at', '<=', $endDate->copy()->endOfDay());

        // Áp dụng cùng 1 logic doanh thu cho Biểu đồ
        $orders = $this->applyRevenueFilter($ordersQuery)->get(['created_at', 'total_amount']);

        $revenues = [];
        foreach ($orders as $order) {
            $dateKey = $order->created_at->format('Y-m-d');
            if (!isset($revenues[$dateKey])) {
                $revenues[$dateKey] = 0;
            }
            $revenues[$dateKey] += $order->total_amount;
        }

        $labels = [];
        $values = [];
        
        $currentDate = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        while ($currentDate <= $end) {
            $dateString = $currentDate->format('Y-m-d');
            $displayDate = $currentDate->format('d/m');
            
            $labels[] = $displayDate;
            $values[] = isset($revenues[$dateString]) ? (float) $revenues[$dateString] : 0;

            $currentDate->addDay();
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }
}