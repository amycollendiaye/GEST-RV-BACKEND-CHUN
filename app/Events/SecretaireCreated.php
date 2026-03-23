<?php

namespace App\Events;

use App\Models\PersonelHopital;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SecretaireCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly PersonelHopital $secretaire,
        public readonly string $plainPassword
    ) {
    }
}
