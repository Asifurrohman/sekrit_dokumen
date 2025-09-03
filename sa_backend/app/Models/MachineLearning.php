<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineLearning extends Model
{
    protected $fillable = [
        'model_name',
        'total_data',
        'accuracy',
        'precision',
        'recall',
        'f1_score',
        'confusion_matrix',
        'top_words',
    ];
}
