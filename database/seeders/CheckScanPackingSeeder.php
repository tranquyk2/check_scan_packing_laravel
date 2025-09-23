<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckScanPackingSeeder extends Seeder
{
    public function run(): void
    {
        $lines = ['Line A', 'Line B', 'Line C'];
        $models = ['Model X', 'Model Y', 'Model Z'];
        $results = ['OK', 'NG'];
        for ($i = 1; $i <= 50; $i++) {
            $barcode = 'BC' . str_pad($i, 5, '0', STR_PAD_LEFT);
            $model = $models[array_rand($models)];
            $line = $lines[array_rand($lines)];
            $result = $results[array_rand($results)];
            $created = now()->subDays(rand(0, 30));
            DB::table('check_scan_packings')->insert([
                'barcode' => $barcode,
                'model' => $model,
                'result' => $result,
                'created_at' => $created,
                'updated_at' => $created,
            ]);
        }
    }
}
