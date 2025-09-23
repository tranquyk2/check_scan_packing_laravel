<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackingStatsController extends Controller
{
    // API: Top 5 model được packing nhiều nhất
    public function topModelStats(Request $request)
    {
        $query = DB::table('check_scan_packings')
            ->select([
                'model as packing_model',
                DB::raw('COUNT(*) as total')
            ])
            ->groupBy('model')
            ->orderByDesc('total')
            ->limit(5);

        if ($request->filled('packing_time_from')) {
            $query->whereDate('created_at', '>=', $request->packing_time_from);
        }
        if ($request->filled('packing_time_to')) {
            $query->whereDate('created_at', '<=', $request->packing_time_to);
        }

        $stats = $query->get();
        return response()->json($stats);
    }
    // API: Thống kê số lượng packing theo từng model
    public function modelStats(Request $request)
    {
        $query = DB::table('check_scan_packings')
            ->select([
                'model as packing_model',
                DB::raw('COUNT(*) as total')
            ])
            ->groupBy('model')
            ->orderByDesc('total');

        if ($request->filled('packing_time_from')) {
            $query->whereDate('created_at', '>=', $request->packing_time_from);
        }
        if ($request->filled('packing_time_to')) {
            $query->whereDate('created_at', '<=', $request->packing_time_to);
        }

        $stats = $query->get();
        return response()->json($stats);
    }
    // API: Thống kê số lượng packing theo ngày
    public function dailyStats(Request $request)
    {
        $query = DB::table('check_scan_packings')
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            ])
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date');

        if ($request->filled('packing_time_from')) {
            $query->whereDate('created_at', '>=', $request->packing_time_from);
        }
        if ($request->filled('packing_time_to')) {
            $query->whereDate('created_at', '<=', $request->packing_time_to);
        }

        $stats = $query->get();
        return response()->json($stats);
    }
    public function lineStats(Request $request)
    {
        // Lấy thống kê tổng, OK, NG theo Line Name
        $query = DB::table('check_scan_packings as p')
            ->leftJoin('barcodes as ba', 'p.barcode', '=', 'ba.code')
            ->leftJoin('barcode_lines as bl', 'ba.line_id', '=', 'bl.id')
            ->select([
                'bl.name as line_name',
                DB::raw('count(*) as total'),
                DB::raw("sum(case when p.result = 'OK' then 1 else 0 end) as ok_count"),
                DB::raw("sum(case when p.result = 'NG' then 1 else 0 end) as ng_count"),
            ])
            ->groupBy('bl.name')
            ->orderByDesc('total');

        // Có thể thêm filter theo ngày nếu muốn
        if ($request->filled('packing_time_from')) {
            $query->whereDate('p.created_at', '>=', $request->packing_time_from);
        }
        if ($request->filled('packing_time_to')) {
            $query->whereDate('p.created_at', '<=', $request->packing_time_to);
        }

        $stats = $query->get();

        // Truyền dữ liệu sang view
        return view('packing.stats', [
            'stats' => $stats
        ]);
    }
}
