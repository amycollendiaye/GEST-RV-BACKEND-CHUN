<?php

namespace App\Models;

use App\Traits\EncryptableFields;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class PersonelHopital extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    use EncryptableFields;

    protected $table = 'personel_hopitals';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'email_hash',
        'telephone',
        'telephone_hash',
        'specialite',
        'matricule',
        'role',
        'statut',
        'activation_token',
        'activation_token_expires_at',
        'service_medical_id',
    ];

    protected $hidden = [
        'activation_token',
    ];

    protected $casts = [
        'activation_token_expires_at' => 'datetime',
    ];

    protected array $encryptable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'specialite',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (!empty($model->email)) {
                $model->email_hash = hash('sha256', strtolower(trim($model->email)));
            }
            if (!empty($model->telephone)) {
                $model->telephone_hash = hash('sha256', preg_replace('/\\D+/', '', $model->telephone));
            }
        });
    }

    public function serviceMedical()
    {
        return $this->belongsTo(ServiceMedical::class, 'service_medical_id');
    }

    public function infosConnexion()
    {
        return $this->hasOne(InfosConnexion::class, 'personel_hopital_id');
    }

    public function getAuthPassword()
    {
        return $this->infosConnexion?->password ?? '';
    }

    public function planningMedecins()
    {
        return $this->hasMany(PlanningMedecin::class, 'medecin_id');
    }

    public function scopeMedecins($query)
    {
        return $query->where('role', 'MEDECIN');
    }

    public function scopeSecretaires($query)
    {
        return $query->where('role', 'SECRETAIRE');
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', 'ACTIF');
    }

    public function scopeByService($query, $serviceId)
    {
        if ($serviceId) {
            $query->where('service_medical_id', $serviceId);
        }

        return $query;
    }

    public function scopeSearch($query, $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('nom', 'like', "%{$term}%")
                ->orWhere('prenom', 'like', "%{$term}%")
                ->orWhere('matricule', 'like', "%{$term}%");
        });
    }
}
