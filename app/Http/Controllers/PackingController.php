<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\PackingExport;
use Maatwebsite\Excel\Facades\Excel;

class PackingController extends Controller
{
    public function index(Request $request)
    {
        // Nếu có filter ngày tháng thì gom packing theo tháng, join từng bảng barcode tháng
        $hasDateFilter = $request->filled('packing_time_from') || $request->filled('packing_time_to');
        $packingQuery = DB::table('check_scan_packings');
        if ($request->filled('barcode')) {
            $packingQuery->where('barcode', 'like', '%' . $request->barcode . '%');
        }
        if ($request->filled('packing_model')) {
            $packingQuery->where('model', 'like', '%' . $request->packing_model . '%');
        }
        if ($request->filled('packing_result')) {
            $packingQuery->where('result', 'like', '%' . $request->packing_result . '%');
        }
        if ($request->filled('packing_time_from')) {
            $packingQuery->whereDate('created_at', '>=', $request->packing_time_from);
        }
        if ($request->filled('packing_time_to')) {
            $packingQuery->whereDate('created_at', '<=', $request->packing_time_to);
        }
        $packingsRaw = $packingQuery->orderByDesc('created_at')->get();

        $results = [];
        $missingMonths = [];
        if ($hasDateFilter) {
            // Gom packing theo tháng
            $groups = $packingsRaw->groupBy(function($item) {
                return date('Ym', strtotime($item->created_at));
            });
            foreach ($groups as $month => $packings) {
                $barcodeTable = 'barcodes_' . $month;
                try {
                    $barcodes = DB::table($barcodeTable . ' as b')
                        ->leftJoin('barcode_lines as l', 'b.line_id', '=', 'l.id')
                        ->whereIn('b.code', $packings->pluck('barcode'))
                        ->select('b.code', 'b.line_id', 'l.name as line_name')
                        ->get()->keyBy('code');
                } catch (\Exception $e) {
                    $missingMonths[] = $month;
                    $barcodes = collect();
                }
                foreach ($packings as $packing) {
                    $barcode = $barcodes[$packing->barcode] ?? null;
                    $results[] = [
                        'packing_id' => $packing->id,
                        'barcode' => $packing->barcode,
                        'packing_model' => $packing->model,
                        'packing_result' => $packing->result,
                        'packing_time' => $packing->created_at,
                        'packing_updated' => $packing->updated_at,
                        'line_id' => $barcode->line_id ?? null,
                        'line_name' => $barcode->line_name ?? ($missingMonths ? 'Chưa có bảng barcode tháng ' . implode(", ", $missingMonths) : null),
                    ];
                }
            }
        } else {
            // Không có filter ngày tháng, join từng bản ghi với bảng barcode tháng
            foreach ($packingsRaw as $packing) {
                $monthTable = 'barcodes_' . date('Ym', strtotime($packing->created_at));
                try {
                    $barcode = DB::table($monthTable . ' as b')
                        ->leftJoin('barcode_lines as l', 'b.line_id', '=', 'l.id')
                        ->where('b.code', $packing->barcode)
                        ->select('b.line_id', 'l.name as line_name')
                        ->first();
                } catch (\Exception $e) {
                    $barcode = null;
                }
                $results[] = [
                    'packing_id' => $packing->id,
                    'barcode' => $packing->barcode,
                    'packing_model' => $packing->model,
                    'packing_result' => $packing->result,
                    'packing_time' => $packing->created_at,
                    'packing_updated' => $packing->updated_at,
                    'line_id' => $barcode->line_id ?? null,
                    'line_name' => $barcode->line_name ?? 'Chưa có bảng barcode tháng ' . date('Ym', strtotime($packing->created_at)),
                ];
            }
        }
        // Phân trang thủ công
        $page = $request->input('page', 1);
        $perPage = 50;
        $packings = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($results, ($page - 1) * $perPage, $perPage),
            count($results),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Lấy danh sách Model, Packing Result, Line ID, Line Name duy nhất cho dropdown
        $modelOptions = DB::table('check_scan_packings')->distinct()->pluck('model')->filter()->values();
        $packingResultOptions = DB::table('check_scan_packings')->distinct()->pluck('result')->filter()->values();
        $lineIdOptions = DB::table('barcodes')->distinct()->pluck('line_id')->filter()->values();
        $lineNameOptions = DB::table('barcode_lines')->distinct()->pluck('name')->filter()->values();

        return view('packing.list', compact('packings', 'modelOptions', 'packingResultOptions', 'lineIdOptions', 'lineNameOptions'));
    }

    public function export(Request $request)
    {
        $query = DB::table('check_scan_packings as p')
            ->leftJoin('barcodes as b', 'p.barcode', '=', 'b.code')
            ->leftJoin('barcode_lines as l', 'b.line_id', '=', 'l.id')
            ->select([
                'p.id as packing_id',
                'p.barcode',
                'p.model as packing_model',
                'p.result as packing_result',
                'p.created_at as packing_time',
                'p.updated_at as packing_updated',
                'b.line_id',
                'l.name as line_name',
            ]);

        if ($request->filled('barcode')) {
            $query->where('p.barcode', 'like', '%' . $request->barcode . '%');
        }
        if ($request->filled('packing_model')) {
            $query->where('p.model', 'like', '%' . $request->packing_model . '%');
        }
        if ($request->filled('packing_result')) {
            $query->where('p.result', 'like', '%' . $request->packing_result . '%');
        }
        if ($request->filled('packing_time_from')) {
            $query->whereDate('p.created_at', '>=', $request->packing_time_from);
        }
        if ($request->filled('packing_time_to')) {
            $query->whereDate('p.created_at', '<=', $request->packing_time_to);
        }
        if ($request->filled('line_id')) {
            $query->where('b.line_id', $request->line_id);
        }
        if ($request->filled('line_name')) {
            $query->where('l.name', 'like', '%' . $request->line_name . '%');
        }

        $packings = $query->orderByDesc('p.created_at')->get();

        // Lấy danh sách cột cần export từ query string
        $columns = $request->filled('columns') ? explode(',', $request->input('columns')) : [
            'packing_id','barcode','packing_model','packing_result','packing_time','packing_updated','line_id','line_name'
        ];

        return Excel::download(new \App\Exports\PackingExport($packings, $columns), 'packing-list.xlsx');
    }
}
