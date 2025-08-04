<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HarvestDatasetResource extends JsonResource
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
            'tweetId' => $this->tweet_id,
            'datetime' => $this->datetime,
            'username' => $this->username,
            'tweet' => $this->tweet,
            'language' => $this->language
        ];
    }
}
