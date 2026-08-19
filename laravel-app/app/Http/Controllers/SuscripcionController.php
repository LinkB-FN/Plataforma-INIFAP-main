<?php

namespace App\Http\Controllers;

use App\Models\Suscripcion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SuscripcionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $palabrasPermitidas = array_keys(config('temas.suscripciones', []));

        $datos = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'palabras_clave' => ['required', 'array', 'min:1'],
            'palabras_clave.*' => ['required', 'string', Rule::in($palabrasPermitidas)],
        ], [
            'palabras_clave.required' => 'Selecciona al menos un tema de interés.',
            'palabras_clave.min' => 'Selecciona al menos un tema de interés.',
        ]);

        foreach (array_unique($datos['palabras_clave']) as $palabra) {
            Suscripcion::firstOrCreate([
                'email' => mb_strtolower($datos['email']),
                'palabra_clave' => $palabra,
            ]);
        }

        return back()->with('suscripcion_status', '¡Listo! Te avisaremos cuando haya contenido nuevo de tus intereses.');
    }
}
