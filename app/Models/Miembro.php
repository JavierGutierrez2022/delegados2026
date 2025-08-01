<?php

namespace App\Models;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\ElectoralPrecinct;
use App\Models\Table;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Miembro extends Model
{
    use HasFactory;

        public function index()
        {
            $miembros = Miembro::with(['province', 'municipality', 'electoralPrecinct', 'tables'])->get();

            return view('admin.delegados.index', compact('miembros'));
        }

    // Relación con la provincia
        public function province()
        {
            return $this->belongsTo(Province::class);
        }

        // Relación con el municipio
        public function municipality()
        {
            return $this->belongsTo(Municipality::class);
        }

        // Relación con el recinto
        public function electoralPrecinct()
        {
            return $this->belongsTo(ElectoralPrecinct::class);
        }

        // Relación con las mesas (muchas a muchas)
        public function tables()
        {
            return $this->belongsToMany(Table::class, 'miembro_table'); // ajusta el nombre si tu tabla pivote se llama diferente
        }

        public function mesas()
        {
            return $this->belongsToMany(Table::class, 'miembro_table');
        }


}

