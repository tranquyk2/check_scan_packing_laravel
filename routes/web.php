<?php
use App\Http\Controllers\PackingController;

use App\Http\Controllers\PackingStatsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/packing-stats/line', [PackingStatsController::class, 'lineStats'])->name('packing.stats.line');
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
