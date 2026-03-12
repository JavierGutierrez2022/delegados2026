<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Province;

class Municipality extends Model
{
    use HasFactory;

   public function electoralPrecincts()
{
    return $this->hasMany(ElectoralPrecinct::class, 'municipality_id');
}
public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function districts()
    {
        return $this->hasMany(District::class, 'municipality_id');
    }
}
