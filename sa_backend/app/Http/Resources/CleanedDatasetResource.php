<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CleanedDatasetResource extends JsonResource
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
            'rawTweet' => $this->raw_tweet,
            'casefolded_tweet' => $this->casefolded_tweet,
            'semiCleanedTweet' => $this->semi_cleaned_tweet,
            'cleansedTweet' => $this->cleansed_tweet,
            'fixedwordsTweet' => $this->fixedwords_tweet,
            'stopwordremovedTweet' => $this->stopwordremoved_tweet,
            'stemmedTweet' => $this->stemmed_tweet,
            'fullyCleanedTweet' => $this->fully_cleaned_tweet,
            'language' => $this->language,
            'classification' => $this->classification,
        ];
    }
}
