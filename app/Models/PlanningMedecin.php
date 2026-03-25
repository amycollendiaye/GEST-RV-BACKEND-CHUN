<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanningMedecin extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'planning_medecins';

    protected $fillable = [
        'medecin_id',
        'service_medical_id',
        'date',
        'heure_debut',
        'heure_fin',
        'heure_ouverture',
        'heure_fermeture',
        'capacite',
    ];

    protected $casts = [
        'date' => 'date',
        'heure_debut' => 'datetime:H:i',
        'heure_fin' => 'datetime:H:i',
        'heure_ouverture' => 'datetime:H:i',
        'heure_fermeture' => 'datetime:H:i',
        'capacite' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $planning): void {
            if (!empty($planning->heure_ouverture) && empty($planning->heure_debut)) {
                $planning->heure_debut = $planning->heure_ouverture;
            }

            if (!empty($planning->heure_fermeture) && empty($planning->heure_fin)) {
                $planning->heure_fin = $planning->heure_fermeture;
            }

            if (!empty($planning->heure_debut) && empty($planning->heure_ouverture)) {
                $planning->heure_ouverture = $planning->heure_debut;
            }

            if (!empty($planning->heure_fin) && empty($planning->heure_fermeture)) {
                $planning->heure_fermeture = $planning->heure_fin;
            }
        });
    }

    public function medecin()
    {
        return $this->belongsTo(PersonelHopital::class, 'medecin_id');
    }

    public function serviceMedical()
    {
        return $this->belongsTo(ServiceMedical::class, 'service_medical_id');
    }

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class, 'planning_medecin_id');
    }

    public function attributedRendezVous()
    {
        return $this->hasMany(RendezVous::class, 'planning_medecin_id')
            ->whereNull('deleted_at')
            ->where('statut', '!=', 'ANNULER');
    }

    public function scopeDateBetween($query, ?string $dateDebut, ?string $dateFin)
    {
        if ($dateDebut) {
            $query->whereDate('date', '>=', $dateDebut);
        }

        if ($dateFin) {
            $query->whereDate('date', '<=', $dateFin);
        }

        return $query;
    }

    public function scopeByService($query, ?string $serviceId)
    {
        if ($serviceId) {
            $query->where('service_medical_id', $serviceId);
        }

        return $query;
    }

    public function scopeByMedecin($query, ?string $medecinId)
    {
        if ($medecinId) {
            $query->where('medecin_id', $medecinId);
        }

        return $query;
    }
}
