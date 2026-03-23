<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfosConnexion extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'infos_connexions';

    protected $fillable = [
        'personel_hopital_id',
        'login',
        'password',
        'first_login',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'first_login' => 'boolean',
    ];

    public function personnel()
    {
        return $this->belongsTo(PersonelHopital::class, 'personel_hopital_id');
    }
}
