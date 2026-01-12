<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
use App\Http\Controllers\PickerController;

Route::get('/', [PickerController::class, 'index'])->name('picker.index');

Route::post('/import', [PickerController::class, 'import'])->name('picker.import');
Route::post('/draw', [PickerController::class, 'draw'])->name('picker.draw');
Route::post('/reset', [PickerController::class, 'reset'])->name('picker.reset');

Route::get('/export/csv', [PickerController::class, 'exportCsv'])->name('picker.export.csv');
Route::get('/export/xlsx', [PickerController::class, 'exportXlsx'])->name('picker.export.xlsx');
Route::get('/export/pdf', [PickerController::class, 'exportPdf'])->name('picker.export.pdf');