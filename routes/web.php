<?php

use App\Http\Controllers\PdfConverterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PdfConverterController::class, 'index'])->name('converter.index');
Route::post('/convert', [PdfConverterController::class, 'convert'])->name('converter.process');
