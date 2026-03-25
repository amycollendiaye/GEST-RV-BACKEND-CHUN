<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public bool $wasReprogrammed = false;

    protected $table = 'consultations';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'rendez_vous_id',
        'patient_id',
        'medecin_id',
        'tension_artielle',
        'poids',
        'temperature',
        'sumptomes',
        'diagnostic',
        'traitement',
        'observations',
        'date_heure',
        'statut',
    ];

    protected $casts = [
        'date_heure' => 'datetime',
        'poids' => 'decimal:2',
        'temperature' => 'decimal:1',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function medecin()
    {
        return $this->belongsTo(PersonelHopital::class, 'medecin_id');
    }

    public function rendezVous()
    {
        return $this->belongsTo(RendezVous::class, 'rendez_vous_id');
    }
}
