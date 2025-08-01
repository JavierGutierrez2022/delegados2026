<?php

namespace App\Models;


use App\Models\Municipality;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;
    
    public function municipalities()
{
    return $this->hasMany(Municipality::class);
}


}


