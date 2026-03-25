<?php
namespace App\Models;

use App\Traits\EncryptableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ServiceMedical extends Model
{
    use EncryptableFields;
    use HasFactory;
    use SoftDeletes;
    use HasUuids;

    protected $table = 'service_medicals';

    protected $keyType   = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nom',
        'nom_hash',
        'description',
        'heure_ouverture',
        'heure_fermeture',
        'etat',
    ];
    protected array $encryptable = [
        'nom',
        'description',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (!empty($model->nom)) {
                $model->nom_hash = hash('sha256', strtolower(trim($model->nom)));
            }
        });
    }
    

    // Génération automatique UUID
    public function personnels()
    {
        return $this->hasMany(PersonelHopital::class, 'service_medical_id');
    }

    public function medecins()
    {
        return $this->personnels()->where('role', 'MEDECIN');
    }

    public function secretaires()
    {
        return $this->personnels()->where('role', 'SECRETAIRE');
    }

    public function planningMedecins()
    {
        return $this->hasMany(PlanningMedecin::class, 'service_medical_id');
    }
}
