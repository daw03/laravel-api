<?php

namespace App\Http\Controllers;

use App\Models\Peticione;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class PeticioneController extends Controller
{
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
            $peticiones = Peticione::where('user_id', $user->id)->get();
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


    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'titulo' => 'required|max:255',
                'descripcion' => 'required',
                'destinatario' => 'required',
                'categoria_id' => 'required|exists:categorias,id',
            ]);

            $category = Categoria::findOrFail($request->input('categoria_id'));
            $user = Auth::user();

            $peticion = new Peticione([
                'titulo' => $request->input('titulo'),
                'descripcion' => $request->input('descripcion'),
                'destinatario' => $request->input('destinatario'),
                'categoria_id' => $request->input('categoria_id'),
                'user_id' => $user->id,
                'firmantes' => 0,
                'estado' => 'pendiente',
            ]);

            $peticion->save();

            return response()->json($peticion, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear la petición', 'exception' => $e->getMessage()], 500);
        }
    }

    public function firmar(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $peticion = Peticione::findOrFail($id);

            if ($user->cannot('firmar', $peticion)) {
                return response()->json(['error' => 'No puedes firmar esta petición.'], 403);
            }

            $peticion->firmas()->attach($user->id);
            $peticion->firmantes += 1;
            $peticion->save();

            return response()->json($peticion, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al firmar la petición', 'exception' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $peticion = Peticione::findOrFail($id);

            if ($user->cannot('update', $peticion)) {
                return response()->json(['error' => 'No puedes actualizar esta petición.'], 403);
            }

            $peticion->update($request->all());
            return response()->json($peticion, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar la petición', 'exception' => $e->getMessage()], 500);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $peticion = Peticione::findOrFail($id);

            if ($user->cannot('delete', $peticion)) {
                return response()->json(['error' => 'No puedes eliminar esta petición.'], 403);
            }

            $peticion->delete();
            return response()->json(['message' => 'Petición eliminada correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar la petición', 'exception' => $e->getMessage()], 500);
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $peticion = Peticione::findOrFail($id);

            if ($user->cannot('cambiarEstado', $peticion)) {
                return response()->json(['error' => 'No puedes actualizar esta petición.'], 403);
            }

            $peticion = Peticione::findOrFail($id);
            $peticion->estado = 'aceptada';
            $peticion->save();

            return response()->json($peticion, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cambiar el estado de la petición', 'exception' => $e->getMessage()], 500);
        }
    }
}
