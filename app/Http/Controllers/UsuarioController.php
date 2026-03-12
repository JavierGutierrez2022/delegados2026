<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;


class UsuarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:ver usuarios')->only(['index', 'show']);
        $this->middleware('can:crear usuarios')->only(['create', 'store']);
        $this->middleware('can:editar usuarios')->only(['edit', 'update']);
        $this->middleware('can:eliminar usuarios')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = User::all();
        return view('admin.usuarios.index',['usuarios'=>$usuarios]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.usuarios.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       

        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|unique:users',
            'password' => 'required|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $usuario = new User();
        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->password = Hash::make($request['password']);
        $usuario->slug = Str::uuid();
        $usuario->save();
        $usuario->assignRole($request->role);

        return redirect()->route('usuarios.index')
            ->with('mensaje','Se registro al usuario de la manera correcta')
            ->with('icono','success');

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $usuario = User::findOrFail($id);
        return view ('admin.usuarios.show',['usuario'=>$usuario]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        return view ('admin.usuarios.edit',['usuario'=>$usuario]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
            {
                $usuario = User::findOrFail($id);

                $request->validate([
                    'name'     => ['required','string','max:100'],
                    'email'    => ['required','email', Rule::unique('users','email')->ignore($usuario->id)],
                    'password' => ['nullable','confirmed','min:8'],
                ]);

                $usuario->name  = $request->name;
                $usuario->email = $request->email;

                if ($request->filled('password')) {
                    $usuario->password = Hash::make($request->password);
                }

                $usuario->save();

                return redirect()->route('usuarios.index')
                    ->with('mensaje','Se actualizó al usuario de la manera correcta')
                    ->with('icono','success');
            }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        User::destroy($id);
        return redirect()->route('usuarios.index')
            ->with('mensaje','Se eliminó al usuario de la manera correcta')
            ->with('icono','success');
    }

    public function registro(){
        return view( 'auth.registro');
    }

    public function registro_create(Request $request)
    {
        

        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|unique:users',
            'password' => 'required|confirmed',
        ]);

        $usuario = new User();
        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->password = Hash::make($request['password']);
        $usuario->save();

        Auth::login($usuario);

        return redirect('/')
            ->with('mensaje','Bienvenido a la plataforma gestión de archivos')
            ->with('icono','success');

    }

                public function editRoles(User $user)
            {
                $roles = Role::orderBy('name')->get();
                return view('admin.usuarios.roles', compact('user','roles'));
            }

            public function updateRoles(Request $request, User $user)
            {
                $user->syncRoles($request->input('roles', []));
                return redirect()->route('usuarios.index')
                    ->with('mensaje','Roles actualizados correctamente')->with('icono','success');
            }

}
