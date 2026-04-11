<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderSimulationController extends Controller
{
    public function getSimulationData($id)
    {
        $order = Order::findOrFail($id);

        // 1. Tọa độ kho hàng mặc định (SORA Boutique - Buôn Ma Thuột)
        $origin = [
            'name' => 'SORA Jewelry (Kho Buôn Ma Thuột)',
            'lat' => 12.6667,
            'lng' => 108.0383
        ];

        // 2. Phân tích địa chỉ khách để lấy tọa độ tương đối
        $address = mb_strtolower($order->customer_address);
        
        // Mặc định là TP.Hồ Chí Minh nếu không nhận diện được
        $destination = ['name' => $order->customer_address, 'lat' => 10.762622, 'lng' => 106.660172]; 

        if (strpos($address, 'hà nội') !== false) {
            $destination = ['name' => $order->customer_address, 'lat' => 21.033333, 'lng' => 105.849998];
        } elseif (strpos($address, 'đà nẵng') !== false) {
            $destination = ['name' => $order->customer_address, 'lat' => 16.054407, 'lng' => 108.202164];
        } elseif (strpos($address, 'hải phòng') !== false) {
            $destination = ['name' => $order->customer_address, 'lat' => 20.844912, 'lng' => 106.688084];
        } elseif (strpos($address, 'cần thơ') !== false) {
            $destination = ['name' => $order->customer_address, 'lat' => 10.045162, 'lng' => 105.746857];
        }

        // Thêm chút ngẫu nhiên (Random) để các đơn cùng thành phố không bị trùng khít tọa độ lên nhau
        $destination['lat'] += (rand(-10, 10) / 1000);
        $destination['lng'] += (rand(-10, 10) / 1000);

        return response()->json([
            'success' => true,
            'data' => [
                'order_code' => $order->order_code,
                'status' => $order->status,
                'origin' => $origin,
                'destination' => $destination
            ]
        ]);
    }
}