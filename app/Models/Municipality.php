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
    return $this->hasMany(ElectoralPrecinct::class);
}

}
