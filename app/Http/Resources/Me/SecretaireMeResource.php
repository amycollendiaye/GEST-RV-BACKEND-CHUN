<?php

namespace App\Http\Resources\Me;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecretaireMeResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Profil secretaire recupere avec succes',
            'data' => $this->resource,
            'errors' => null,
        ];
    }
}
