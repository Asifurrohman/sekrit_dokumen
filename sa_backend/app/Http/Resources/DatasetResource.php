<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DatasetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tweetId' => $this->tweet_id,
            'datetime' => $this->datetime,
            'username' => $this->username,
            'tweet' => $this->tweet
        ];
    }
}
