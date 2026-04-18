<?php

namespace App\Policies;

use App\Models\JournalAudit;
use App\Models\PersonelHopital;
use Illuminate\Auth\Access\Response;

class JournalAuditPolicy
{
    public function viewAny(PersonelHopital $user): Response
    {
        return $this->authorizeAdmin($user);
    }

    public function view(PersonelHopital $user, JournalAudit $journalAudit): Response
    {
        return $this->authorizeAdmin($user);
    }

    public function export(PersonelHopital $user): Response
    {
        return $this->authorizeAdmin($user);
    }

    private function authorizeAdmin(PersonelHopital $user): Response
    {
        if (strtoupper($user->role) === 'ADMIN') {
            return Response::allow();
        }

        return Response::deny('Seul l\'administrateur peut consulter les journaux d\'audit.');
    }
}
