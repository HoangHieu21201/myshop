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
    // CÁCH MỚI: Tính doanh thu cho tất cả đơn hàng, NGOẠI TRỪ các đơn đã bị Hủy/Thất bại
    private $excludedStatuses = ['Đã hủy', 'Hủy', 'cancelled', 'canceled', 'failed', 'Thất bại'];

    public function index()
    {
        try {
            // 1. TỔNG QUAN
            // Sum 'total_amount' của các đơn hàng không bị hủy
            $totalRevenue = Order::whereNotIn('status', $this->excludedStatuses)->sum('total_amount') ?? 0; 
            $newOrders = Order::whereDate('created_at', Carbon::today())->count();
            
            // Đếm toàn bộ User (Bảng users của em chỉ lưu khách hàng)
            $totalCustomers = User::count();
                
            // Tính tổng tồn kho từ bảng product_variants
            $inventory = Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'stock') 
                ? DB::table('product_variants')->whereNull('deleted_at')->sum('stock') 
                : 0;

            // 2. ĐƠN HÀNG GẦN ĐÂY
            $recentOrdersRaw = Order::with('user')->orderBy('created_at', 'desc')->take(5)->get();
            $recentOrders = $recentOrdersRaw->map(function($order) {
                return [
                    'id' => $order->id,
                    'code' => $order->order_code ?? 'ORD-' . str_pad($order->id, 4, '0', STR_PAD_LEFT), 
                    // Dùng fullName từ User model của em
                    'customer' => $order->user ? $order->user->fullName : ($order->customer_name ?? 'Khách lẻ'), 
                    'date' => $order->created_at->format('d/m/Y H:i'),
                    'total' => (float) ($order->total_amount ?? 0), 
                    'status' => $order->status ?? 'Mới',
                ];
            });

            // 3. SẢN PHẨM BÁN CHẠY
            $topProductsRaw = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereNull('orders.deleted_at') // Bỏ qua đơn hàng đã xóa mềm
                ->whereNotIn('orders.status', $this->excludedStatuses) // Bỏ qua đơn bị hủy
                ->whereNotNull('order_items.product_id') // CHỐNG LỖI: Bỏ qua các item là Combo (ko có product_id)
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
                    // Dùng thumbnail_image theo đúng Model Product của em
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
                        'newOrders' => $newOrders,
                        'inventory' => (int) $inventory,
                        'totalCustomers' => $totalCustomers
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
        // Xử lý thống kê an toàn bằng PHP Collection để tránh lỗi SQL Group By ngày tháng
        $orders = Order::whereNotIn('status', $this->excludedStatuses)
            ->where('created_at', '>=', $startDate->copy()->startOfDay())
            ->where('created_at', '<=', $endDate->copy()->endOfDay())
            ->get(['created_at', 'total_amount']);

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