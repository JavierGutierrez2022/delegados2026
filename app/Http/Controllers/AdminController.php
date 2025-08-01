<?php

namespace App\Http\Controllers;

use App\Models\ElectoralPrecinct;
use App\Models\User;
use App\Models\Miembro;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\Table;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(){
        $usuarios = User::all();
        $miembros = Miembro::all();
        $provinces = Province::all();
        $municipalities = Municipality::all();
        $electoralprecincts = ElectoralPrecinct::all();
        $tables = Table::all();
        return view('admin.index',['usuarios'=>$usuarios, 'miembros'=>$miembros,'provinces'=>$provinces,'municipalities'=>$municipalities, 'electoralprecincts'=>$electoralprecincts, 'tables'=>$tables]);
    }

    /* public function index2(){
        $miembros = Miembro::all();
        return view('admin.index',['miembros'=>$miembros]);
    } */
}
