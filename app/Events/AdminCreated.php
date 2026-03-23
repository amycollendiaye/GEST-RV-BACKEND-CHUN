<?php

namespace App\Events;

use App\Models\PersonelHopital;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly PersonelHopital $admin,
        public readonly string $plainPassword
    ) {
    }
}
