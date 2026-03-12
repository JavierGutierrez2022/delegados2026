<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'municipality_id',
        'name',
        'state',
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'municipality_id');
    }

    public function electoralPrecincts()
    {
        return $this->hasMany(ElectoralPrecinct::class, 'district_id');
    }
}

