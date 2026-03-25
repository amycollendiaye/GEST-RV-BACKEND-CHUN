<?php

namespace App\Events;

use App\Models\Consultation;
use App\Models\RendezVous;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConsultationTerminee
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Consultation $consultation,
        public readonly RendezVous $rendezVous
    ) {
    }
}
