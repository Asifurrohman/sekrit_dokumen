<?php

namespace App\Http\Controllers;
use App\Http\Resources\CleanedDatasetResource;
use App\Models\CleanedDataset;
use App\Models\Dataset;
use App\Models\HarvestDataset;
use Illuminate\Http\Request;
use Sastrawi\Stemmer\StemmerFactory;
use Illuminate\Support\Facades\Response;

class CleanedDatasetController extends Controller
{
    /**
    * Display a listing of the resource.
    */
    public function index(Request $request)
    {
        $query = CleanedDataset::query();
        
        if($request->has('search') && !empty($request->search)){
            $keyword = $request->search;
            
            $query->where(function($q) use ($keyword){
                $q->where('cleaned_tweet', 'like', "%$keyword%");
            });
        }
        
        $CleanedDatasets = $query->paginate(20);
        return CleanedDatasetResource::collection($CleanedDatasets);
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
        $tweets = HarvestDataset::select('tweet', 'language')->get();
        
        $stopwordPath = storage_path('app/indonesian-stopwords/idstopwords.txt');
        if (!file_exists($stopwordPath)) {
            return response()->json(['message' => 'File stopword tidak ditemukan.'], 500);
        }
        $stopwords = file($stopwordPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $stopwords = array_map('strtolower', $stopwords);
        
        $slangPath = storage_path('app/indonesian-stopwords/combined_slang_words.txt');
        if (!file_exists($slangPath)) {
            return response()->json(['message' => 'File slang tidak ditemukan.'], 500);
        }
        $slangRaw = file_get_contents($slangPath);
        $slangMap = json_decode($slangRaw, true);
        
        $factory = new StemmerFactory();
        $stemmer = $factory->createStemmer();
        
        if ($tweets->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data tweet yang ditemukan.'], 404);
        }
        
        $uniqueTweets = $tweets->unique();
        
        $purgedCharacters = ['@', '#', '$', '%', '^', '&', '*', '(', ')', '+', '=', '{', '}', '[', ']', '|', '\\', '/', '<', '>', '`', '~'];
        
        $inserted = [];
        $seenCleaned = [];
        
        foreach($tweets->unique('tweet') as $row){
            $tweet = strtolower($row->tweet);
            $lang = $row->language ?? 'unknown';
            
            $tweet = preg_replace('/[\x{1F600}-\x{1F64F}' .  // emoticons
            '\x{1F300}-\x{1F5FF}' .  // symbols & pictographs
            '\x{1F680}-\x{1F6FF}' .  // transport & map
            '\x{1F1E0}-\x{1F1FF}' .  // flags (iOS)
            '\x{2600}-\x{26FF}' .    // miscellaneous symbols
            '\x{2700}-\x{27BF}]/u', '', $tweet);
            
            // normalisasi tanda baca (misal: !!!!!! menjadi ! atau ?????? menjadi ?)
            $tweet = preg_replace('/([!?.,])\1+/', '$1', $tweet);
            
            // Hapus URL yang ada di tweet
            $tweet = preg_replace('/https?:\/\/\S+/', '', $tweet);

            // Hapus mention seperti @username
            $tweet = preg_replace('/@\w+/', '', $tweet);
            
            $words = preg_split('/[\s\p{P}]+/u', $tweet, -1, PREG_SPLIT_NO_EMPTY);
            
            $cleanWords = [];
            
            foreach($words as $word){
                trim($word);
                
                if(strpbrk($word, implode('', $purgedCharacters))){
                    continue;
                }
                
                if(in_array($word, $stopwords)){
                    continue;
                }
                
                if ($word === 'rp') {
                    continue;
                }
                
                if (preg_match('/\d/', $word)) {
                    continue;
                }
                
                // $stemmedWord = $stemmer->stem($word);
                
                // $cleanWords[] = $stemmedWord;
                
                // Ganti slang jika ada
                $normalizedWord = $slangMap[$word] ?? $word;
                
                // Stem kata yang sudah dinormalisasi
                $stemmedWord = $stemmer->stem($normalizedWord);
                
                $cleanWords[] = $stemmedWord;
            }
            
            $cleaned = implode(' ', $cleanWords);
            
            if(!empty(trim($cleaned)) && !in_array($cleaned, $seenCleaned)){
                if (CleanedDataset::where('raw_tweet', $tweet)->exists()) {
                    continue;
                }
                
                $inserted[] = CleanedDataset::create([
                    'raw_tweet' => $tweet,
                    'cleaned_tweet' => $cleaned,
                    'language' => $lang
                ]);
                
                $seenCleaned[] = $cleaned;
            }
        }
        
        return response()->json([
            'message' => count($inserted) . ' data berhasil dibersihkan.',
            'data' => CleanedDatasetResource::collection(collect($inserted)),
        ]);
    }
    
    /**
    * Display the specified resource.
    */
    public function show(CleanedDataset $cleanedDataset)
    {
        //
    }
    
    /**
    * Show the form for editing the specified resource.
    */
    public function edit(CleanedDataset $cleanedDataset)
    {
        //
    }
    
    /**
    * Update the specified resource in storage.
    */
    public function update(Request $request, CleanedDataset $cleanedDataset)
    {
        //
    }
    
    /**
    * Remove the specified resource from storage.
    */
    public function destroy(CleanedDataset $cleanedDataset)
    {
        //
    }
    
    public function destroyAll()
    {
        CleanedDataset::truncate();
        
        return response()->json([
            'message' => 'Semua data pada tabel cleaned_datasets berhasil dihapus.'
        ]);
    }
    
    public function export()
    {
        $filename = 'cleaned_dataset_' . date('Ymd_His') . '.csv';
        
        // Simpan CSV ke memory
        $handle = fopen('php://temp', 'r+');
        
        // Tambahkan UTF-8 BOM agar bisa dibaca di Excel
        fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Header kolom
        fputcsv($handle, ['raw_tweet', 'cleaned_tweet']);
        
        // Ambil data
        $rows = CleanedDataset::select('raw_tweet', 'cleaned_tweet')->get();
        
        foreach ($rows as $row) {
            fputcsv($handle, [
                $row->raw_tweet ?? '',
                $row->cleaned_tweet ?? ''
            ]);
        }
        
        // Kembalikan ke awal
        rewind($handle);
        
        $csvContent = stream_get_contents($handle);
        fclose($handle);
        
        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
