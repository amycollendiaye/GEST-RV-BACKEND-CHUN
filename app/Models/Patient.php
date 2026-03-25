<?php

namespace App\Models;

use App\Traits\EncryptableFields;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Patient extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    use EncryptableFields;

    protected $table = 'patients';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'email_hash',
        'telephone',
        'telephone_hash',
        'date_naissance',
        'adresse',
        'matricule',
        'login',
        'password',
        'first_login',
        'statut',
        'activation_token',
        'activation_token_expires_at',
    ];

    protected $hidden = [
        'password',
        'activation_token',
    ];

    protected $casts = [
        'first_login' => 'boolean',
        'activation_token_expires_at' => 'datetime',
        'date_naissance' => 'date',
    ];

    protected array $encryptable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
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

    public function dossierMedical()
    {
        return $this->hasOne(DossierMedical::class, 'patient_id');
    }

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class, 'patient_id');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'patient_id');
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', 'ACTIF');
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
