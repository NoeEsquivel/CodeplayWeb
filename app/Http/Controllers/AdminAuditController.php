<?php

namespace App\Http\Controllers;

use App\Models\RegistroAdministrativo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAuditController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'colegio_id' => ['nullable', 'integer', 'exists:colegios,id'],
            'accion' => ['nullable', 'string', 'max:80'],
            'limite' => ['nullable', 'integer', 'min:10', 'max:100'],
        ]);

        $query = RegistroAdministrativo::query()
            ->with([
                'actor:id,nombre,rol',
                'objetivoUsuario:id,nombre,rol',
                'colegio:id,nombre,codigo',
            ])
            ->latest('id');

        if (!empty($datos['colegio_id'])) {
            $query->where('colegio_id', $datos['colegio_id']);
        }

        if (!empty($datos['accion'])) {
            $query->where('accion', $datos['accion']);
        }

        $registros = $query->limit((int) ($datos['limite'] ?? 50))->get();

        return response()->json([
            'data' => $registros,
            'total_mostrado' => $registros->count(),
        ]);
    }
}
