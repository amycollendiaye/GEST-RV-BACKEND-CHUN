<?php

namespace App\Http\Resources\Me;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminMeResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Profil administrateur recupere avec succes',
            'data' => $this->resource,
            'errors' => null,
        ];
    }
}
