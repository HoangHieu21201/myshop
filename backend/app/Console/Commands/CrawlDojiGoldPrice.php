<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use DOMDocument;
use DOMXPath;

class CrawlDojiGoldPrice extends Command
{
    protected $signature = 'sora:crawl-gold';

    protected $description = 'Điệp viên ngầm cào giá vàng từ trang chủ DOJI mỗi 5 phút';

    public function handle()
    {
        $this->info('Bắt đầu đột nhập DOJI...');

        try {
            // Đóng giả làm trình duyệt người dùng thật
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            ])->timeout(15)->get('https://giavang.doji.vn/');

            if (!$response->successful()) {
                $this->error('Đột nhập thất bại. Mã lỗi: ' . $response->status());
                return;
            }

            $html = $response->body();

            $dom = new DOMDocument();
            @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            $xpath = new DOMXPath($dom);

            $rows = $xpath->query('//table//tbody//tr');
            
            $goldPrices = [];

            $cleanAndFormatPrice = function($rawPrice) {
                $rawPrice = trim($rawPrice);
                if (empty($rawPrice) || $rawPrice === '-') return $rawPrice;

                $pureNumber = str_replace([',', '.'], '', $rawPrice);
                
                if (is_numeric($pureNumber)) {
                    return number_format($pureNumber);
                }
                
                return $rawPrice;
            };

            foreach ($rows as $row) {
                $cols = $xpath->query('td', $row);
                if ($cols->length >= 3) {
                    $name = trim($cols->item(0)->textContent);
                    
                    $buy = $cleanAndFormatPrice($cols->item(1)->textContent);
                    $sell = $cleanAndFormatPrice($cols->item(2)->textContent);

                    if ($name && $buy && $sell && !empty($name)) {
                        $goldPrices[] = [
                            'name' => $name,
                            'buy' => $buy,
                            'sell' => $sell,
                        ];
                    }
                }
            }

            if (count($goldPrices) > 0) {
                Cache::put('sora_gold_prices', $goldPrices, 300);
                Cache::put('sora_gold_last_updated', now()->format('H:i d/m/Y'), 300);

                $this->info('Thành công! Đã lấy được ' . count($goldPrices) . ' mã vàng.');
            } else {
                $this->warn('Không tìm thấy dữ liệu giá vàng!');
            }

        } catch (\Exception $e) {
            $this->error('Lỗi kĩ thuật: ' . $e->getMessage());
        }
    }
}