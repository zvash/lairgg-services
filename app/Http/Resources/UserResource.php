<?php

namespace App\Http\Resources;

use App\Traits\Resource\JsonResourceProvider;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    use JsonResourceProvider;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'verified_at' => $this->whenHasDate('email_verified_at'),
        ];
    }
}
