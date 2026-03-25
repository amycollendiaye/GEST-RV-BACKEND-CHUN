<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalAudit extends Model
{
    use HasFactory;

    protected $table = 'journal_audits';

    public $timestamps = false;

    protected $fillable = [
        'personel_hopital_id',
        'type_action',
        'details',
        'adresse_ip',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    public function auteur()
    {
        return $this->belongsTo(PersonelHopital::class, 'personel_hopital_id');
    }
}
