<?php

use App\Http\Controllers\InvoicePrintController;
use App\Http\Controllers\MusterSheetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {

    Route::get(
        '/invoices/{invoice}/print',
        InvoicePrintController::class
    )->name('invoices.print');

    Route::get('/muster-sheet/print', [MusterSheetController::class, 'print'])
    ->name('muster-sheet.print');

});