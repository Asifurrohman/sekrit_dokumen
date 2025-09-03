<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HarvestDataset extends Model
{
    protected $fillable = [
        'tweet_id',
        'datetime',
        'username',
        'tweet',
        'language'
    ];
}
