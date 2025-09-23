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

        $packings = $query->orderByDesc('p.created_at')->paginate(50);

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
