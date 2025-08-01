<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

   public function miembros()
{
    return $this->belongsToMany(Miembro::class, 'miembro_table', 'table_id', 'miembro_id');
}


}
