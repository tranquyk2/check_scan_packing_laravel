<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FullDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa dữ liệu cũ để tránh lỗi trùng unique
        DB::table('check_scan_packing_box')->truncate();
        DB::table('check_scan_packings')->truncate();
        DB::table('check_scans')->truncate();
        DB::table('check_scan_models')->truncate();
        DB::table('barcodes')->truncate();
        DB::table('barcode_models')->truncate();
        DB::table('barcode_lines')->truncate();
        $lines = ['Line A', 'Line B', 'Line C'];
        $models = ['Model X', 'Model Y', 'Model Z'];
        $modelCodes = ['MX01', 'MY02', 'MZ03'];
        $modelStds = ['STD1', 'STD2', 'STD3'];
        $results = ['OK', 'NG'];
        $scanFiles = ['scan1.txt', 'scan2.txt', 'scan3.txt'];
        $boxDates = [Carbon::now()->subDays(1), Carbon::now()->subDays(2), Carbon::now()->subDays(3)];

        // Barcode Lines
        foreach ($lines as $i => $line) {
            DB::table('barcode_lines')->updateOrInsert(
                ['id' => $i+1],
                [
                    'name' => $line,
                    'factory_id' => 1 // Giá trị mặc định cho factory_id
                ]
            );
        }
        // Barcode Models
        foreach ($models as $i => $model) {
            DB::table('barcode_models')->updateOrInsert(['id' => $i+1], ['model' => $model, 'std_record' => $modelStds[$i]]);
        }
        // Barcodes
    $insertedBarcodes = [];
        for ($i = 1; $i <= 30; $i++) {
            $barcode = 'BC' . str_pad($i, 5, '0', STR_PAD_LEFT);
            $lineId = ($i % 3) + 1;
            $modelIdx = $i % 3;
            // Đảm bảo barcode liên kết đúng line_id để join ra line_name
            DB::table('barcodes')->insert([
                'line_id' => $lineId,
                'factory_id' => 1,
                'model_id' => 1,
                'code' => $barcode, // Lưu mã barcode vào code
                'device_name' => 'Device ' . $lineId,
                'datetime' => Carbon::now(),
                'type_id' => 1,
                'char_count' => 12,
                'status' => 1
            ]);
        }
        // CheckScanPacking, CheckScan, CheckScanModel, CheckScanPackingBox
        for ($i = 1; $i <= 30; $i++) {
            $barcode = 'BC' . str_pad($i, 5, '0', STR_PAD_LEFT);
            $modelIdx = $i % 3;
            $model = $models[$modelIdx];
            // Tạo model code ngẫu nhiên cho từng barcode
            $modelCode = $modelCodes[$modelIdx] . '-' . $i;
            $modelStd = $modelStds[$modelIdx];
            $line = $lines[$modelIdx];
            $result = $results[$i % 2];
            $created = Carbon::now()->subDays(rand(0, 30))->setTime(rand(0,23), rand(0,59), rand(0,59));
            $scanResult = $results[($i+1) % 2];
            $scanTime = $created->copy()->addMinutes(rand(1,60));
            $scanFile = $scanFiles[$i % 3];
            $boxCount = rand(1, 10);
            $boxDate = $boxDates[$i % 3];

            // Packing
            $packingId = DB::table('check_scan_packings')->insertGetId([
                'barcode' => $barcode,
                'model' => $model,
                'result' => $result,
                'created_at' => $created,
                'updated_at' => $created,
            ]);

            // Scan (chỉ insert mỗi barcode duy nhất)
            if (!in_array($barcode, $insertedBarcodes)) {
                DB::table('check_scans')->insert([
                    'barcode' => $barcode,
                    'model' => $model,
                    'result' => $scanResult,
                    'datetime' => $scanTime,
                    'file_name' => $scanFile,
                ]);
                $insertedBarcodes[] = $barcode;
            }

            // Model (mỗi barcode có 1 code riêng)
            DB::table('check_scan_models')->insert([
                'barcode' => $barcode,
                'code' => $modelCode,
            ]);

            // Packing Box
            DB::table('check_scan_packing_box')->insert([
                'model' => $model,
                'box_count' => $boxCount,
                'date' => $boxDate,
            ]);
        }
    }
}
