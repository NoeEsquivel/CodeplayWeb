<details class="mt-3">
    <summary title="Elige en qué clases estará disponible este banco y, si lo deseas, limita sus fechas.">
        <i class="fas fa-calendar-check me-2"></i>Asignar a clases y programar disponibilidad
    </summary>
    <form method="POST" action="{{ route('profesor.tests.banks.assign', $bank) }}">
        @csrf
        @php
            $assigned = $bank->clasesAsignadas
                ->filter(fn($class) => (int) $class->pivot->asignado_por_usuario_id === (int) session('usuario_id'))
                ->pluck('id')->map(fn($id) => (int) $id)->all();
        @endphp
        <p class="action-explanation mb-3">Marca las clases que podrán utilizar este banco. Las fechas son opcionales.</p>
        <div class="assignment-classes">
            @foreach($classes as $class)
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="clase_ids[]" value="{{ $class->id }}" @checked(in_array((int) $class->id, $assigned, true))>
                    {{ $class->nombre }}
                </label>
            @endforeach
        </div>
        <div class="row g-2 mt-2">
            <div class="col-md-6"><label class="form-label">Disponible desde</label><input class="form-control" type="datetime-local" name="disponible_desde"></div>
            <div class="col-md-6"><label class="form-label">Disponible hasta</label><input class="form-control" type="datetime-local" name="disponible_hasta"></div>
        </div>
        <button class="teacher-btn teacher-btn-primary teacher-btn-small mt-3" title="Guarda las clases y fechas seleccionadas.">
            <i class="fas fa-save"></i>Guardar asignación
        </button>
    </form>
</details>
