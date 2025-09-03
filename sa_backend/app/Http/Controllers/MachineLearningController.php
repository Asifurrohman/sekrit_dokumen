<?php

namespace App\Http\Controllers;

use App\Http\Resources\MachineLearningResource;
use App\Models\CleanedDataset;
use App\Models\MachineLearning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

// class MachineLearningController extends Controller
class MachineLearningController extends Controller
{
    public function index(Request $request){
        $data = MachineLearning::all();
        return MachineLearningResource::collection($data);
    }
    
}
