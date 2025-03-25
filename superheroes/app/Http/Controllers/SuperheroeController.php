<?php

namespace App\Http\Controllers;

use App\Superheroe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuperheroeController extends Controller
{
    /**
     * Muestra la lista de superhéroes activos.
     */
    public function index()
    {
        // Por defecto, con SoftDeletes el método all() excluye los eliminados.
        $superheroes = Superheroe::all();
        return view('superheroes.index', compact('superheroes'));
    }

    /**
     * Muestra el formulario para crear un nuevo superhéroe.
     */
    public function create()
    {
        return view('superheroes.create');
    }

    /**
     * Almacena un nuevo superhéroe en la base de datos.
     */
    public function store(Request $request)
    {
        // Validamos los datos enviados.
        $validated = $request->validate([
            'nombre_real'          => 'required|string|max:255',
            'nombre_heroe'         => 'required|string|max:255',
            'foto'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'informacion_adicional'=> 'nullable|string',
        ]);

        // Si se sube una foto, la almacenamos en el disco 'public'
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('superheroes', 'public');
            // Guardamos el path en la columna 'foto_url'
            $validated['foto_url'] = $path;
        }

        Superheroe::create($validated);

        return redirect()->route('superheroes.index')
                         ->with('success', 'Superhéroe creado correctamente.');
    }

    /**
     * Muestra los detalles de un superhéroe.
     */
    public function show($id)
    {
        $superheroe = Superheroe::findOrFail($id);
        return view('superheroes.show', compact('superheroe'));
    }

    /**
     * Muestra el formulario para editar un superhéroe.
     */
    public function edit($id)
    {
        $superheroe = Superheroe::findOrFail($id);
        return view('superheroes.edit', compact('superheroe'));
    }

    /**
     * Actualiza la información del superhéroe en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $superheroe = Superheroe::findOrFail($id);

        $validated = $request->validate([
            'nombre_real'          => 'required|string|max:255',
            'nombre_heroe'         => 'required|string|max:255',
            'foto'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'informacion_adicional'=> 'nullable|string',
        ]);

        // Si se sube una nueva foto, opcionalmente borramos la antigua
        if ($request->hasFile('foto')) {
            if ($superheroe->foto_url && Storage::disk('public')->exists($superheroe->foto_url)) {
                Storage::disk('public')->delete($superheroe->foto_url);
            }
            $path = $request->file('foto')->store('superheroes', 'public');
            $validated['foto_url'] = $path;
        }

        $superheroe->update($validated);

        return redirect()->route('superheroes.index')
                         ->with('success', 'Superhéroe actualizado correctamente.');
    }

    /**
     * Realiza un soft delete (eliminación lógica) del superhéroe.
     */
    public function destroy($id)
    {
        $superheroe = Superheroe::findOrFail($id);
        $superheroe->delete(); // Soft delete: se establece 'deleted_at'
        return redirect()->route('superheroes.index')
                         ->with('success', 'Superhéroe eliminado correctamente (soft delete).');
    }

    /**
     * Muestra los superhéroes eliminados lógicamente.
     */
    public function eliminados()
    {
        $superheroes = Superheroe::onlyTrashed()->get();
        return view('superheroes.eliminados', compact('superheroes'));
    }

    /**
     * Restaura un superhéroe eliminado lógicamente.
     */
    public function restaurar($id)
    {
        $superheroe = Superheroe::onlyTrashed()->where('id', $id)->firstOrFail();
        $superheroe->restore();
        return redirect()->route('superheroes.eliminados')
                         ->with('success', 'Superhéroe restaurado correctamente.');
    }
}
