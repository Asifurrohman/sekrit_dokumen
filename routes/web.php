<?php

use App\Http\Controllers\DatasetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    return redirect('/statistic');
});

Route::get('/statistics', function() {
    return view('statistic', ['title' => 'Statistik']);
});

// Route::get('/dataset', function() {
//     return view('dataset', ['title' => 'Dataset', DatasetController::class, 'index']);
// });

Route::get('/dataset', [DatasetController::class, 'index']);
