<?php

namespace App\Http\Controllers;

use App\Models\Peticione;
use App\Models\Categoria;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeticioneController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
    }
    public function index(Request $request)
    {
        try {
            $peticiones = Peticione::all();
            return response()->json($peticiones, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener las peticiones', 'exception' => $e->getMessage()], 500);
        }
    }

    public function listMine(Request $request)
    {
        try {
            $user = Auth::user();
            $peticiones = Peticione::where('user_id', $user->id)->get(); // Asegúrate de acceder al `id` del usuario
            return response()->json($peticiones, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener tus peticiones', 'exception' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $peticion = Peticione::findOrFail($id);
            return response()->json($peticion, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener la petición', 'exception' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $peticion = Peticione::findOrFail($id);
            $peticion->update($request->all());
            return response()->json($peticion, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar la petición', 'exception' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'titulo' => 'required|max:255',
                'descripcion' => 'required',
                'destinatario' => 'required',
                'categoria_id' => 'required',
            ]);
            $input = $request->all();
            $category = Categoria::findOrFail($request->input('categoria_id'));
            $user = Auth::user();
            $peticion = new Peticione($input);
            $peticion->user()->associate($user);
            $peticion->categoria()->associate($category);
            $peticion->firmantes = 0;
            $peticion->estado = 'pendiente';
            $peticion->save();

            return response()->json($peticion, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear la petición', 'exception' => $e->getMessage()], 500);
        }
    }

    public function firmar(Request $request, $id)
    {
        try {
            $peticion = Peticione::findOrFail($id);
            $user = Auth::user();

            if ($peticion->firmas->contains($user->id)) {
                return response()->json(['error' => 'Ya has firmado esta petición.'], 400); // Si ya ha firmado, devolvemos error
            }

            $peticion->firmas()->attach($user->id);
            $peticion->firmantes += 1;
            $peticion->save();

            // Devolver la petición actualizada
            return response()->json($peticion, 200);

        } catch (\Exception $e) {
            // Capturar errores generales
            return response()->json(['error' => 'Error al firmar la petición', 'exception' => $e->getMessage()], 500);
        }
    }


    public function cambiarEstado(Request $request, $id)
    {
        try {
            $peticion = Peticione::findOrFail($id);
            $peticion->estado = 'aceptada';
            $peticion->save();

            return response()->json($peticion, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cambiar el estado de la petición', 'exception' => $e->getMessage()], 500);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $peticion = Peticione::findOrFail($id);
            $peticion->delete();
            return response()->json(['message' => 'Petición eliminada correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar la petición', 'exception' => $e->getMessage()], 500);
        }
    }
}
