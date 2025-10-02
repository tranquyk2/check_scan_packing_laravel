<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PackingDemoSeeder extends Seeder
{
    public function run()
    {
        // Xóa dữ liệu cũ
        DB::table('check_scan_packings')->truncate();
        DB::table('barcode_lines')->truncate();
        // Tạo bảng barcode tháng hiện tại nếu chưa có
        $monthTable = 'barcodes_' . date('Ym');
        try {
            DB::statement("CREATE TABLE IF NOT EXISTS `$monthTable` LIKE `barcodes`");
        } catch (\Exception $e) {}
        DB::table($monthTable)->truncate();
        // Tạo line mẫu
        DB::table('barcode_lines')->insert([
            ['id' => 1, 'name' => 'Line A', 'factory_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'name' => 'Line B', 'factory_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
        // Tạo barcode mẫu (tháng hiện tại)
        DB::table($monthTable)->insert([
            ['id' => 1, 'factory_id' => 1, 'line_id' => 1, 'model_id' => 1, 'code' => 'BC001', 'device_name' => 'Device 1', 'datetime' => Carbon::now(), 'type_id' => 1, 'char_count' => 10, 'note' => null, 'status' => 'active', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'factory_id' => 1, 'line_id' => 2, 'model_id' => 2, 'code' => 'BC002', 'device_name' => 'Device 2', 'datetime' => Carbon::now()->subDays(1), 'type_id' => 1, 'char_count' => 10, 'note' => null, 'status' => 'active', 'created_at' => Carbon::now()->subDays(1), 'updated_at' => Carbon::now()->subDays(1)],
        ]);
        // Tạo packing mẫu
        DB::table('check_scan_packings')->insert([
            ['id' => 1, 'barcode' => 'BC001', 'result' => 'OK', 'model' => 'Model X', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'barcode' => 'BC002', 'result' => 'NG', 'model' => 'Model Y', 'created_at' => Carbon::now()->subDays(1), 'updated_at' => Carbon::now()->subDays(1)],
        ]);
    }
}
