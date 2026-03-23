<?php

namespace App\Events;

use App\Models\PersonelHopital;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MedecinCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly PersonelHopital $medecin,
        public readonly string $plainPassword
    ) {
    }
}
