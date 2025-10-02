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
                            // Không lấy line_id và line_name nữa, chỉ lấy dữ liệu barcode
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
                        // Không lấy line_id và line_name nữa, chỉ lấy dữ liệu barcode
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

    // Lấy danh sách Model, Packing Result duy nhất cho dropdown
    $modelOptions = DB::table('check_scan_packings')->distinct()->pluck('model')->filter()->values();
    $packingResultOptions = DB::table('check_scan_packings')->distinct()->pluck('result')->filter()->values();

    return view('packing.list', compact('packings', 'modelOptions', 'packingResultOptions'));
    }

    public function export(Request $request)
    {
        $query = DB::table('check_scan_packings')
            ->select([
                'id as packing_id',
                'barcode',
                'model as packing_model',
                'result as packing_result',
                'created_at as packing_time',
                'updated_at as packing_updated',
            ]);

        if ($request->filled('barcode')) {
            $query->where('barcode', 'like', '%' . $request->barcode . '%');
        }
        if ($request->filled('packing_model')) {
            $query->where('model', 'like', '%' . $request->packing_model . '%');
        }
        if ($request->filled('packing_result')) {
            $query->where('result', 'like', '%' . $request->packing_result . '%');
        }
        if ($request->filled('packing_time_from')) {
            $query->whereDate('created_at', '>=', $request->packing_time_from);
        }
        if ($request->filled('packing_time_to')) {
            $query->whereDate('created_at', '<=', $request->packing_time_to);
        }

        $packings = $query->orderByDesc('created_at')->get();

        // Lấy danh sách cột cần export từ query string
        $columns = $request->filled('columns') ? explode(',', $request->input('columns')) : [
            'packing_id','barcode','packing_model','packing_result','packing_time','packing_updated'
        ];

        return Excel::download(new \App\Exports\PackingExport($packings, $columns), 'packing-list.xlsx');
    }
}
