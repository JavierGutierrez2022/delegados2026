<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'miembro_id','scope','electoral_precinct_id','table_id','table_key','role'
    ];
    

    public function miembro() { return $this->belongsTo(Miembro::class); }
    public function electoralPrecinct() { return $this->belongsTo(ElectoralPrecinct::class); }
    public function table() { return $this->belongsTo(Table::class); }
}