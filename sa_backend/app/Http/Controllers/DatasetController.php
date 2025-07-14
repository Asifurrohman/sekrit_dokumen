<?php

namespace App\Http\Controllers;
use App\Http\Resources\DatasetResource;
use App\Models\Dataset;
use Illuminate\Http\Request;

class DatasetController extends Controller
{
    /**
    * Display a listing of the resource.
    */
    public function index(Request $request)
    {
        $query = Dataset::query();
        
        if($request->has('search') && !empty($request->search)){
            $keyword = $request->search;
            
            $query->where(function($q) use ($keyword){
                $q->where('tweet', 'like', "%$keyword%")->orWhere('username', 'like', "%$keyword%");
            });
        }
        
        $datasets = $query->paginate(20);
        return DatasetResource::collection($datasets);
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
    public function store(Request $request){
        $validated = $request->validate([
            'data' => 'required|array|min:1',
            'data.*.tweet_id' => 'required|string|max:255',
            'data.*.datetime' => 'required|date',
            'data.*.username' => 'required|string|max:255',
            'data.*.tweet' => 'required|string',
        ]);
        
        // Simpan ke DB
        $inserted = [];
        
        foreach ($validated['data'] as $row) {
            $inserted[] = Dataset::create([
                'tweet_id' => $row['tweet_id'],
                'datetime' => $row['datetime'],
                'username' => $row['username'],
                'tweet'    => $row['tweet'],
            ]);
        }
        
        return response()->json([
            'message' => count($inserted) . ' data berhasil ditambahkan.',
            'data' => DatasetResource::collection(collect($inserted)),
        ]);
    }
    
    /**
    * Display the specified resource.
    */
    public function show(Dataset $dataset)
    {
        //
    }
    
    /**
    * Show the form for editing the specified resource.
    */
    public function edit(Dataset $dataset)
    {
        //
    }
    
    /**
    * Update the specified resource in storage.
    */
    public function update(Request $request, Dataset $dataset)
    {
        //
    }
    
    /**
    * Remove the specified resource from storage.
    */
    public function destroy(Dataset $dataset)
    {
        //
    }
    
    public function all()
    {
        return DatasetResource::collection(Dataset::all());
    }
}
