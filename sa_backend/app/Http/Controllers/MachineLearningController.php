<?php

namespace App\Http\Controllers;

use App\Http\Resources\MachineLearningResource;
use App\Models\CleanedDataset;
use Illuminate\Http\Request;

class MachineLearningController extends Controller
{
    public function index(Request $request){
        $data = CleanedDataset::all();
        return MachineLearningResource::collection($data);
    }
}
