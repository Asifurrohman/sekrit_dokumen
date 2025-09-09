<?php

namespace App\Http\Controllers;
use App\Http\Resources\CleanedDatasetResource;
use App\Models\CleanedDataset;
use App\Models\Dataset;
use App\Models\HarvestDataset;
use App\Models\MachineLearning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Sastrawi\Stemmer\StemmerFactory;
use Illuminate\Support\Facades\Response;

class CleanedDatasetController extends Controller
{
    /**
    * Display a listing of the resource.
    */
    public function index(Request $request)
    {
        $query = CleanedDataset::query()->where('language', 'in');
        
        if($request->has('search') && !empty($request->search)){
            $keyword = $request->search;
            
            $query->where(function($q) use ($keyword){
                $q->where('fully_cleaned_tweet', 'like', "%$keyword%")->orWhere('raw_tweet', 'like', "%$keyword%");
            });
        }
        
        if($request->has('classification') && !empty($request->classification)){
            $classification = strtolower($request->classification);
            
            if(in_array($classification, ['positive', 'negative'])){
                $query->where('classification', $classification);
            }
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
    public function store(Request $request)
    {
        $tweets = HarvestDataset::select('tweet', 'language')->where('language', 'in')->get();
        
        // Custom normalization
        $customNormalization = [
            'tertawa' => 'tawa',
            'peperangan' => 'perang',
        ];
        
        if ($tweets->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data tweet yang ditemukan.'], 404);
        }
        
        // Load slang map
        $slangPath = storage_path('app/indonesian-stopwords/combined_slang_words.txt');
        if (!file_exists($slangPath)) {
            return response()->json(['message' => 'File slang tidak ditemukan.'], 500);
        }
        $slangRaw = file_get_contents($slangPath);
        $slangMap = json_decode($slangRaw, true);
        
        // Gabungkan dengan extended slang dari config
        $extendedSlangMap = config('extended-found-slang');
        $slangMap = array_merge($slangMap, $extendedSlangMap);
        
        // Stopwords
        $stopwordPath = storage_path('app/indonesian-stopwords/idstopwords.txt');
        if (!file_exists($stopwordPath)) {
            return response()->json(['message' => 'File stopword tidak ditemukan.'], 500);
        }
        $stopwords = array_map('strtolower', file($stopwordPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        
        // Stemmer
        $factory = new StemmerFactory();
        $stemmer = $factory->createStemmer();
        
        $purgedCharacters = ['@', '#', '$', '%', '^', '&', '*', '(', ')', '+', '=', '{', '}', '[', ']', '|', '\\', '/', '<', '>', '`', '~'];
        
        $inserted = [];
        
        foreach ($tweets->unique('tweet') as $row) {
            $rawTweet = $row->tweet;
            $lang = $row->language ?? 'unknown';
            
            // 1. Case folding
            $casefolded = strtolower($rawTweet);
            
            // 2. Slang + custom normalization (perbaikan kata)
            $words = explode(' ', $casefolded);
            $normalizedWords = array_map(function ($word) use ($slangMap, $customNormalization) {
                $word = $slangMap[$word] ?? $word;
                return $customNormalization[$word] ?? $word;
            }, $words);
            
            // Semi-cleaned (setelah perbaikan kata, sebelum hapus tanda baca)
            $semiCleaned = implode(' ', $normalizedWords);
            
            // 3. Cleansed Tweet (hapus url, username, hashtag, emoji, angka, tanda baca)
            $cleansed = $semiCleaned;
            $cleansed = preg_replace('/https?:\/\/\S+/', '', $cleansed);
            $cleansed = preg_replace('/@\w+/', '', $cleansed);
            $cleansed = preg_replace('/#\w+/', '', $cleansed);
            $cleansed = preg_replace('/[\x{1F600}-\x{1F64F}' .
            '\x{1F300}-\x{1F5FF}' .
            '\x{1F680}-\x{1F6FF}' .
            '\x{1F1E0}-\x{1F1FF}' .
            '\x{2600}-\x{26FF}' .
            '\x{2700}-\x{27BF}]/u', '', $cleansed); // hapus emoji
            $cleansed = preg_replace('/\d+/', '', $cleansed); // hapus angka
            $cleansed = preg_replace('/[[:punct:]]/', ' ', $cleansed); // hapus tanda baca
            $cleansed = preg_replace('/\s+/', ' ', $cleansed);
            
            // 4. Stopword removal
            $stopwordRemovedWords = array_filter(explode(' ', trim($cleansed)), function ($word) use ($stopwords) {
                return !in_array($word, $stopwords) && $word !== '' && $word !== 'rp';
            });
            $stopwordRemoved = implode(' ', $stopwordRemovedWords);
            
            // 5. Stemming
            $stemmedWords = array_map(function ($word) use ($stemmer) {
                return $stemmer->stem($word);
            }, explode(' ', $stopwordRemoved));
            $stemmedTweet = implode(' ', $stemmedWords);
            
            // 6. Fully Cleaned (final)
            $fullyCleaned = $stemmedTweet;
            
            // --- Simpan ke DB ---
            if (!CleanedDataset::where('raw_tweet', $rawTweet)->exists()) {
                $inserted[] = CleanedDataset::create([
                    'raw_tweet'             => $rawTweet,
                    'casefolded_tweet'      => $casefolded,
                    'semi_cleaned_tweet'    => $semiCleaned,   // hasil slang fix
                    'cleansed_tweet'        => $cleansed,
                    'fixedwords_tweet'      => $semiCleaned,   // bisa dibuat sama dengan semi_cleaned
                    'stopwordremoved_tweet' => $stopwordRemoved,
                    'stemmed_tweet'         => $stemmedTweet,
                    'fully_cleaned_tweet'   => $fullyCleaned,
                    'language'              => $lang,
                    'classification'        => null,
                    'created_at'            => now(),
                    'updated_at'            => now()
                ]);
            }
        }
        
        
        return response()->json([
            'message' => count($inserted) . ' data berhasil dibersihkan.',
            'data' => CleanedDatasetResource::collection(collect($inserted)),
        ]);
    }
    
    public function recleanedDataset(Request $request)
    {
        $tweets = CleanedDataset::where('language', 'in')->get();
        
        $customNormalization = [
            'tertawa' => 'tawa',
            'peperangan' => 'perang',
        ];
        
        if ($tweets->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data tweet yang ditemukan.'], 404);
        }
        
        // Slang map
        $slangPath = storage_path('app/indonesian-stopwords/combined_slang_words.txt');
        if (!file_exists($slangPath)) {
            return response()->json(['message' => 'File slang tidak ditemukan.'], 500);
        }
        $slangMap = json_decode(file_get_contents($slangPath), true);
        $extendedSlangMap = config('extended-found-slang');
        $slangMap = array_merge($slangMap, $extendedSlangMap);
        
        // Stopwords
        $stopwordPath = storage_path('app/indonesian-stopwords/idstopwords.txt');
        if (!file_exists($stopwordPath)) {
            return response()->json(['message' => 'File stopword tidak ditemukan.'], 500);
        }
        $stopwords = array_map('strtolower', file($stopwordPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        
        // Stemmer
        $factory = new StemmerFactory();
        $stemmer = $factory->createStemmer();
        
        $updatedCount = 0;
        
        foreach ($tweets as $row) {
            $rawTweet = $row->raw_tweet;
            $lang = $row->language ?? 'unknown';
            
            // 1. Casefolding
            $casefolded = strtolower($rawTweet);
            
            // 2. Perbaikan kata (slang → baku + custom normalization)
            $words = explode(' ', trim($casefolded));
            $fixedWords = array_map(function ($word) use ($slangMap, $customNormalization) {
                $word = $slangMap[$word] ?? $word;
                return $customNormalization[$word] ?? $word;
            }, $words);
            $fixedTweet = implode(' ', $fixedWords);
            
            // 3. Semi-cleaned (langsung dari fixedTweet, sebelum hapus tanda baca/angka)
            $semiCleaned = strtolower(trim($fixedTweet));
            
            // 4. Cleansed Tweet (hapus url, username, hashtag, angka, tanda baca, emoji)
            $cleansed = preg_replace('/https?:\/\/\S+/', '', $semiCleaned);
            $cleansed = preg_replace('/@\w+/', '', $cleansed);
            $cleansed = preg_replace('/#\w+/', '', $cleansed);
            $cleansed = preg_replace('/[\x{1F600}-\x{1F64F}' .
            '\x{1F300}-\x{1F5FF}' .
            '\x{1F680}-\x{1F6FF}' .
            '\x{1F1E0}-\x{1F1FF}' .
            '\x{2600}-\x{26FF}' .
            '\x{2700}-\x{27BF}]/u', '', $cleansed);
            $cleansed = preg_replace('/\d+/', '', $cleansed);
            $cleansed = preg_replace('/[[:punct:]]/', ' ', $cleansed);
            $cleansed = preg_replace('/\s+/', ' ', $cleansed);
            $cleansed = trim($cleansed);
            
            // 5. Stopword removal
            $stopwordRemovedWords = array_filter(explode(' ', $cleansed), function ($word) use ($stopwords) {
                return !in_array($word, $stopwords) && $word !== '' && $word !== 'rp';
            });
            $stopwordRemoved = implode(' ', $stopwordRemovedWords);
            
            // 6. Stemming
            $stemmedWords = array_map(function ($word) use ($stemmer) {
                return $stemmer->stem($word);
            }, explode(' ', $stopwordRemoved));
            $stemmedTweet = implode(' ', $stemmedWords);
            
            // 7. Fully Cleaned
            $fullyCleaned = $stemmedTweet;
            
            // --- Update record ---
            $row->update([
                'casefolded_tweet'      => $casefolded,
                'semi_cleaned_tweet'    => $semiCleaned,
                'cleansed_tweet'        => $cleansed,
                'fixedwords_tweet'      => $fixedTweet,
                'stopwordremoved_tweet' => $stopwordRemoved,
                'stemmed_tweet'         => $stemmedTweet,
                'fully_cleaned_tweet'   => $fullyCleaned,
                'updated_at'            => now(),
            ]);
            
            $updatedCount++;
        }
        
        return response()->json([
            'message' => "$updatedCount data berhasil dibersihkan ulang.",
            'total_updated' => $updatedCount
        ]);
    }
    
    
    
    
    
    /**
    * Display the specified resource.
    */
    public function show(CleanedDataset $cleanedDataset)
    {
        
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
        // Validasi input
        $validated = $request->validate([
            'classification' => 'required|in:positive,neutral,negative'
        ]);
        
        // Update data di database
        $cleanedDataset->update([
            'classification' => $validated['classification']
        ]);
        
        // Kembalikan response JSON
        return response()->json([
            'message' => 'Label berhasil diperbarui.',
            'data' => $cleanedDataset
        ]);
    }
    
    /**
    * Remove the specified resource from storage.
    */
    public function destroy(CleanedDataset $cleanedDataset)
    {
        //
    }
    
    public function deleteSelectedDataset($id)
    {
        try {
            $dataset = CleanedDataset::findOrFail($id);
            $dataset->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Dataset berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus dataset.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    public function destroyAll()
    {
        CleanedDataset::truncate();
        
        return response()->json([
            'message' => 'Semua data pada tabel cleaned_datasets berhasil dihapus.'
        ]);
    }
    
    public function all()
    {
        $cleanedDatasets = CleanedDataset::where('language', 'in')->get();
        return CleanedDatasetResource::collection($cleanedDatasets);
    }
    
    public function classifyDataset()
    {
        // 1. Ambil data dari DB
        $tweets = CleanedDataset::where('language', 'in')
        ->get(['id', 'semi_cleaned_tweet', 'classification'])
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'tweet' => $item->semi_cleaned_tweet,
                'old_classification' => $item->classification,
            ];
        })
        ->toArray(); // penting biar bisa di-chunk
        
        $batchSize = 50; // jumlah per batch
        $allResults = [];
        
        // 2. Kirim batch ke Flask API
        foreach (array_chunk($tweets, $batchSize) as $batch) {
            $response = Http::timeout(600)->post('http://127.0.0.1:5000/classify', [
                'tweets' => $batch
            ]);
            
            if ($response->successful()) {
                $results = $response->json()['results'];
                
                // 3. Simpan hasil ke DB
                foreach ($results as $result) {
                    // dd($result);
                    CleanedDataset::where('id', $result['id'])
                    ->update([
                        'classification' => $result['predicted_classification'] // cuma "negative"/"neutral"/"positive"
                    ]);
                }
                
                // gabungkan semua hasil
                $allResults = array_merge($allResults, $results);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $response->body()
                ], 500);
            }
        }
        
        // 4. Return hasil ke frontend
        return response()->json([
            'status' => 'success',
            'results' => $allResults
        ]);
    }
    
    // public function trainDataset()
    // {
    //     // 1. Ambil data dari DB
    //     $tweets = CleanedDataset::where('language', 'in')
    //     ->whereIn('classification', ['positive', 'negative'])
    //     ->get(['id', 'fully_cleaned_tweet', 'classification'])
    //     ->map(function ($item) {
    //         return [
    //             'id'    => $item->id,
    //             'tweet'  => $item->fully_cleaned_tweet,
    //             'label' => $item->classification, // ground truth untuk training
    //         ];
    //     })
    //     ->toArray();
    
    //     if (empty($tweets)) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Dataset kosong untuk training'
    //         ], 400);
    //     }
    
    //     // 2. Kirim data ke Flask API
    //     $response = Http::timeout(600)->post('http://127.0.0.1:5000/train', [
    //         'tweets' => $tweets
    //     ]);
    
    //     // 3. Return hasil sesuai response dari Flask
    //     if ($response->successful()) {
    //         return response()->json([
    //             'status'   => 'success',
    //             'training' => $response->json()
    //         ]);
    //     }
    
    //     return response()->json([
    //         'status'  => 'error',
    //         'message' => $response->body()
    //     ], $response->status());
    // }
    
    public function trainDataset()
    {
        // 1. Ambil semua data positif
        $positives = CleanedDataset::where('language', 'in')
        ->where('classification', 'positive')
        ->get(['id', 'fully_cleaned_tweet', 'classification']);
        
        // 2. Ambil sebagian data negatif (acak, limit 76)
        $negatives = CleanedDataset::where('language', 'in')
        ->where('classification', 'negative')
        ->inRandomOrder()
        ->limit(76)
        ->get(['id', 'fully_cleaned_tweet', 'classification']);
        
        // 3. Gabungkan dataset
        $tweets = $positives->merge($negatives)
        ->map(function ($item) {
            return [
                'id'    => $item->id,
                'tweet' => $item->fully_cleaned_tweet,
                'label' => $item->classification, // ground truth
            ];
        })
        ->toArray();
        
        if (empty($tweets)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dataset kosong untuk training'
            ], 400);
        }
        
        // 4. Kirim data ke Flask API
        $response = Http::timeout(600)->post('http://127.0.0.1:5000/train', [
            'tweets' => $tweets
        ]);
        
        // 5. Return hasil sesuai response dari Flask
        if ($response->successful()) {
            return response()->json([
                'status'   => 'success',
                'training' => $response->json()
            ]);
        }
        
        return response()->json([
            'status'  => 'error',
            'message' => $response->body()
        ], $response->status());
    }
    
    
    
    public function evaluateDataset()
    {
        $tweets = CleanedDataset::where('language', 'in')
        ->whereIn('classification', ['positive', 'negative'])
        ->get(['id', 'fully_cleaned_tweet', 'classification'])
        ->map(function ($item) {
            return [
                'id'    => $item->id,
                'tweet'  => $item->fully_cleaned_tweet,
                'label' => $item->classification, // ground truth
            ];
        })
        ->toArray();
        
        // 2. Kirim data ke Flask API
        $response = Http::timeout(600)->post('http://127.0.0.1:5000/evaluate', [
            'tweets' => $tweets
        ]);
        
        if ($response->successful()) {
            $evaluation = $response->json();
            
            // Hapus data lama di tabel machine_learnings
            // MachineLearning::truncate(); 
            // kalau mau hapus hanya berdasarkan model/dataset tertentu bisa pakai:
            // MachineLearning::where('model_name', 'SVM-TFIDF')->delete();
            
            // 3. Simpan hasil evaluasi baru
            MachineLearning::create([
                'model_name'       => 'SVM-TFIDF', // bisa dibuat dinamis sesuai kebutuhan
                'dataset_name'     => 'CleanedDataset (lang=in)',
                'total_data'       => $evaluation['total_data'] ?? count($tweets),
                'accuracy'         => $evaluation['accuracy'] ?? null,
                'precision'        => $evaluation['precision'] ?? null,
                'recall'           => $evaluation['recall'] ?? null,
                'f1_score'         => $evaluation['f1_score'] ?? null,
                'confusion_matrix' => json_encode($evaluation['confusion_matrix'] ?? []),
                'top_words'        => json_encode($evaluation['top_words'] ?? []),
            ]);
            
            // 4. Return hasil ke frontend
            return response()->json([
                'status'     => 'success',
                'evaluation' => $evaluation
            ]);
        }
        
        return response()->json([
            'status'  => 'error',
            'message' => $response->body()
        ], 500);
    }
}
