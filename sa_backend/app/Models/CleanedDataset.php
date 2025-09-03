<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CleanedDataset extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'raw_tweet',
        'casefolded_tweet',
        'semi_cleaned_tweet',
        'cleansed_tweet',
        'fixedwords_tweet',
        'stopwordremoved_tweet',
        'stemmed_tweet',
        'fully_cleaned_tweet',
        'language',
        'classification'
    ];
}
