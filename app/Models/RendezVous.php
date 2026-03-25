<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RendezVous extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'rendez_vous';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'patient_id',
        'service_medical_id',
        'medecin_id',
        'planning_medecin_id',
        'date_rendez_vous',
        'motif',
        'statut',
    ];

    protected $casts = [
        'date_rendez_vous' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function serviceMedical()
    {
        return $this->belongsTo(ServiceMedical::class, 'service_medical_id');
    }

    public function medecin()
    {
        return $this->belongsTo(PersonelHopital::class, 'medecin_id');
    }

    public function consultation()
    {
        return $this->hasOne(Consultation::class, 'rendez_vous_id');
    }

    public function planningMedecin()
    {
        return $this->belongsTo(PlanningMedecin::class, 'planning_medecin_id');
    }

    public function scopeSearch($query, $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->whereHas('patient', function ($q) use ($term) {
            $q->where('nom', 'like', "%{$term}%")
                ->orWhere('prenom', 'like', "%{$term}%")
                ->orWhere('matricule', 'like', "%{$term}%");
        });
    }
}
