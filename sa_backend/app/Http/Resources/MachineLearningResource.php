<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MachineLearningResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'modelName' => $this->model_name,
            'totalData' => $this->total_data,
            'accuracy' => $this->accuracy,
            'precision' => $this->precision,
            'recall' => $this->recall,
            'f1Score' => $this->f1_score,
            'confusionMatrix' => $this->confusion_matrix,
            'topWords' => $this->top_words,
        ];
    }
}
