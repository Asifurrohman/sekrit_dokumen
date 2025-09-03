<?php

use App\Http\Controllers\CleanedDatasetController;
use App\Http\Controllers\DatasetController;
use App\Http\Controllers\HarvestDatasetController;
use App\Http\Controllers\MachineLearningController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/datasets/all', [DatasetController::class, 'all']);
Route::apiResource('/datasets', DatasetController::class);

Route::apiResource('/harvest-datasets', HarvestDatasetController::class);

Route::delete('/cleaned-datasets', [CleanedDatasetController::class, 'destroyAll']);
Route::get('/cleaned-datasets/export', [CleanedDatasetController::class, 'export']);
Route::get('/cleaned-datasets/all', [CleanedDatasetController::class, 'all']);
Route::post('/cleaned-datasets/reclean', [CleanedDatasetController::class, 'recleanedDataset']);
Route::post('/cleaned-datasets/classify', [CleanedDatasetController::class, 'classifyDataset']);
Route::post('/cleaned-datasets/train', [CleanedDatasetController::class, 'trainDataset']);
Route::post('/cleaned-datasets/evaluate', [CleanedDatasetController::class, 'evaluateDataset']);
Route::apiResource('/cleaned-datasets', CleanedDatasetController::class);

Route::apiResource('/machine-learning', MachineLearningController::class);
