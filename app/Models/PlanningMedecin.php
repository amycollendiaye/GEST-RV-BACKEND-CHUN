<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningMedecin extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'planning_medecins';

    protected $fillable = [
        'medecin_id',
        'date',
        'heure_debut',
        'heure_fin',
    ];

    protected $casts = [
        'date' => 'date',
        'heure_debut' => 'datetime:H:i',
        'heure_fin' => 'datetime:H:i',
    ];

    public function medecin()
    {
        return $this->belongsTo(PersonelHopital::class, 'medecin_id');
    }
}
