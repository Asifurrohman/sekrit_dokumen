<?php

namespace App\Http\Controllers;

use App\Http\Resources\HarvestDatasetResource;
use App\Models\HarvestDataset;
use Illuminate\Http\Request;

class HarvestDatasetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = HarvestDataset::query()->where('language', 'in');
        
        if($request->has('search') && !empty($request->search)){
            $keyword = $request->search;
            
            $query->where(function($q) use ($keyword){
                $q->where('tweet', 'like', "%$keyword%")->orWhere('username', 'like', "%$keyword%");
            });
        }
        
        $datasets = $query->paginate(20);
        return HarvestDatasetResource::collection($datasets);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'data' => 'required|array|min:1',
            'data.*.tweet_id' => 'required|string|max:255',
            'data.*.datetime' => 'required|date',
            'data.*.username' => 'required|string|max:255',
            'data.*.tweet' => 'required|string',
            'data.*.language' => 'required|string',
        ]);
        
        $inserted = [];
        
        foreach ($validated['data'] as $row) {
            $inserted[] = HarvestDataset::create([
                'tweet_id' => $row['tweet_id'],
                'datetime' => $row['datetime'],
                'username' => $row['username'],
                'tweet'    => $row['tweet'],
                'language'    => $row['language'],
            ]);
        }
        
        return response()->json([
            'message' => count($inserted) . ' data berhasil ditambahkan.',
            'data' => HarvestDatasetResource::collection(collect($inserted)),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(HarvestDataset $harvestDataset)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HarvestDataset $harvestDataset)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HarvestDataset $harvestDataset)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HarvestDataset $harvestDataset)
    {
        //
    }
}
