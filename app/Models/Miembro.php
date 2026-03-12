<?php

namespace App\Models;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\ElectoralPrecinct;
use App\Models\Table;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use App\Traits\LogsActivity;

class Miembro extends Model
{
    use LogsActivity;
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
            return $this->belongsTo(ElectoralPrecinct::class, 'electoral_precinct_id');
        }

        // Relación con las mesas (muchas a muchas)
        public function tables()
        {
            return $this->belongsToMany(Table::class, 'miembro_table', 'miembro_id', 'table_id'); // ajusta el nombre si tu tabla pivote se llama diferente
        }

        public function mesas()
        {
            return $this->belongsToMany(Table::class, 'miembro_table');
        }

        protected function fecnac(): Attribute
            {
                return Attribute::make(
                    // GET: retorna Carbon|null sin lanzar excepción
                    get: function ($value) {
                        if (!$value) return null;
                        $v = trim($value);
                        if ($v === '0000-00-00' || $v === '0000-00-00 00:00:00') return null;

                        try {
                            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $v)) {
                                return Carbon::createFromFormat('d/m/Y', $v);
                            }
                            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $v)) {
                                // Y-m-d o Y-m-d H:i:s
                                return Carbon::createFromFormat('Y-m-d', substr($v, 0, 10));
                            }
                            return Carbon::parse($v);
                        } catch (\Throwable $e) {
                            return null;
                        }
                    },

                    // SET: guarda siempre Y-m-d si se puede
                    set: function ($value) {
                        if (!$value) return null;
                        $v = trim($value);
                        try {
                            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $v)) {
                                return Carbon::createFromFormat('d/m/Y', $v)->format('Y-m-d');
                            }
                            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                                return $v; // ya viene bien
                            }
                            return Carbon::parse($v)->format('Y-m-d');
                        } catch (\Throwable $e) {
                            return $v; // deja el valor tal cual si no se puede parsear
                        }
                    }
                );
            }

         


}

