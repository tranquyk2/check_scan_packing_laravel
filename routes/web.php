<?php
use App\Http\Controllers\PackingController;
use App\Http\Controllers\PackingStatsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/packing-stats', function () {
    $month = now()->format('Y-m');
    $stats = DB::table('check_scan_packings')
        ->select([
            'model as packing_model',
            DB::raw('COUNT(*) as total'),
            DB::raw("SUM(CASE WHEN result = 'OK' THEN 1 ELSE 0 END) as ok_count"),
            DB::raw("SUM(CASE WHEN result = 'NG' THEN 1 ELSE 0 END) as ng_count")
        ])
        ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
        ->groupBy('model')
        ->orderByDesc('total')
        ->get();
    return view('packing.stats', compact('stats'));
})->name('packing.stats');



Route::get('/packing-stats/daily', [PackingStatsController::class, 'dailyStats'])->name('packing.stats.daily');
Route::get('/packing-stats/model', [PackingStatsController::class, 'modelStats'])->name('packing.stats.model');
Route::get('/packing-stats/topmodel', [PackingStatsController::class, 'topModelStats'])->name('packing.stats.topmodel');


Route::get('/packing-list', [PackingController::class, 'index'])->name('packing.list');
Route::get('/packing-list/export', [PackingController::class, 'export'])->name('packing.export');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
