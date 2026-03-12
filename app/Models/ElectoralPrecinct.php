<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectoralPrecinct extends Model
{
    use HasFactory;

    protected $table = 'electoral_precincts'; // usa el nombre correcto

    protected $fillable = [
        'electoral_seat',
        'distric_number',
        'district_id',
        'name',
        'table'
    ];
    
    public function tables()
{
    return $this->hasMany(Table::class, 'electoral_precinct_id');
}

public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'municipality_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

}
