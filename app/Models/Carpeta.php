<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carpeta extends Model
{
    use HasFactory;

    protected $fillable = ['nombre','carpeta_padre_id'];
    
    public function carpetashijos (){
        return $this->hasMany(Carpeta::class, 'carpeta_padre_id');
    }

    public function archivos(){
        return $this->hasMany(Archivo::class);
    }
    
}
