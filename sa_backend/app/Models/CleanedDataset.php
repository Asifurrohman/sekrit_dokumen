<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CleanedDataset extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'raw_tweet',
        'cleaned_tweet'
    ];
}
