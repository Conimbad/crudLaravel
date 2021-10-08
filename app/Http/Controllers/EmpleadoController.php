<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datos['empleados'] = Empleado::paginate(1);
        return view('empleado.index', $datos);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('empleado.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validando los campos de formulario
        $campos = [
            'Nombre'=>'required|string|max:100',
            'ApellidoPaterno'=>'required|string|max:100',
            'ApellidoMaterno'=>'required|string|max:100',
            'Correo'=>'required|email',
            'Foto'=>'required|max:10000|mimes:jpeg,png,jpg,gif'
        ];

        // Mensajes a mostrar si un campo no está lleno
        $mensaje = [
            'required'=>'El :attribute es requerido.',
            'Foto.required'=>'La foto es requerida'
        ];

        // Validando: ...
        $this->validate($request, $campos, $mensaje);

        //$datosEmpleado = request()->all();
        $datosEmpleado = request()->except('_token');

        // Valida si existe un campo imágenes y la sube a sto
        if ($request->hasFile('Foto')) {
            $datosEmpleado['Foto'] = $request->file('Foto')->store('uploads', 'public');
        }

        Empleado::insert($datosEmpleado);
        return redirect('empleado')->with('mensaje', 'Empleado agregado exitosamente'); 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Empleado  $empleado
     * @return \Illuminate\Http\Response
     */
    public function show(Empleado $empleado)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Empleado  $empleado
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $empleado = Empleado::findOrFail($id);
        return view('empleado.edit', compact('empleado'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Empleado  $empleado
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $campos = [
            'Nombre'=>'required|string|max:100',
            'ApellidoPaterno'=>'required|string|max:100',
            'ApellidoMaterno'=>'required|string|max:100',
            'Correo'=>'required|email'
        ];

        // Mensajes a mostrar si un campo no está lleno
        $mensaje = [
            'required'=>'El :attribute es requerido.',
        ];

        // Validando si subió una foto nueva: 
        if ($request->hasFile('Foto')) {
            $campos = [ 'Foto'=>'required|max:10000|mimes:jpeg,png,jpg,gif' ];
            $mensaje = [ 'Foto.required'=>'La foto es requerida' ];
        }
        // Validando: ...
        $this->validate($request, $campos, $mensaje);

        // Exceptuando el input _token y _method
        $datosEmpleado = request()->except('_token', '_method');
        
        // Borrando la imagen antigua y colocando la nueva:
        if ($request->hasFile('Foto')) {
            $empleado = Empleado::findOrFail($id);

            Storage::delete('public/'.$empleado->Foto);
            $datosEmpleado['Foto'] = $request->file('Foto')->store('uploads', 'public');
        }

        Empleado::where('id','=',$id)->update($datosEmpleado);

        // Retorna a la ruta edit:
        $empleado = Empleado::findOrFail($id);
        // return view('empleado.edit', compact('empleado'));
        return redirect('empleado')->with('mensaje', 'Empleado modificado.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Empleado  $empleado
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $empleado = Empleado::findOrFail($id);
        // Elimina la foto del registro:         
        if (Storage::delete('public/'.$empleado->Foto)) {
            Empleado::destroy($id);
        }
        Empleado::destroy($id);
        return redirect('empleado')->with('mensaje', 'Empleado borrado.');
    }
}
