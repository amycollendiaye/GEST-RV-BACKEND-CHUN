<?php

namespace App\Models;

use App\Traits\EncryptableFields;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DossierMedical extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    use EncryptableFields;

    protected $table = 'dossier_medicals';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'numero_dossier',
        'patient_id',
        'groupe_sanguin',
        'antecedents_medicaux',
        'antecedents_chirurgicaux',
        'antecedents_familiaux',
        'allergies',
        'maladies_chroniques',
        'traitements_en_cours',
    ];

    protected array $encryptable = [
        'antecedents_medicaux',
        'antecedents_chirurgicaux',
        'antecedents_familiaux',
        'allergies',
        'maladies_chroniques',
        'traitements_en_cours',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'patient_id', 'patient_id');
    }
}
