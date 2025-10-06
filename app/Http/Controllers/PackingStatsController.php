<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackingStatsController extends Controller
{
    // API: Top 5 model được packing nhiều nhất
    // Thống kê model (tổng/OK/NG) theo tháng hiện tại
    public function modelStatsMonthly(Request $request)
    {
        $month = now()->format('Y-m');
        $query = DB::table('check_scan_packings')
            ->select([
                'model as packing_model',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN result = 'OK' THEN 1 ELSE 0 END) as ok_count"),
                DB::raw("SUM(CASE WHEN result = 'NG' THEN 1 ELSE 0 END) as ng_count")
            ])
            ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
            ->groupBy('model')
            ->orderByDesc('total');

        $stats = $query->get();
        return response()->json($stats);
    }
    // API: Thống kê số lượng packing theo từng model
    // Bỏ các API thống kê model cũ
    // API: Thống kê số lượng packing theo ngày
    // Bỏ API thống kê theo ngày
    // Bỏ API thống kê theo line
}
