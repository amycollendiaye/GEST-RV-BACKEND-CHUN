<?php

namespace App\Events;

use App\Models\RendezVous;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RendezVousReprogramme
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly RendezVous $ancienRendezVous,
        public readonly RendezVous $nouveauRendezVous
    ) {
    }
}
