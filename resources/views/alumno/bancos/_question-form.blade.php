@php
    $pregunta = $pregunta ?? null;
    $tipo = old('tipo', $pregunta?->tipo ?? 'opcion_unica');
    $opciones = old('opciones', $pregunta?->opciones ?? ['a'=>'','b'=>'','c'=>'','d'=>'']);
    $correctas = collect($pregunta?->respuestas_correctas ?? [])->map(fn($v)=>(string)$v)->all();
@endphp
<div class="form-grid">
    <label class="wide">Pregunta
        <textarea name="enunciado" rows="2" maxlength="2000" required>{{ old('enunciado', $pregunta?->enunciado) }}</textarea>
    </label>
    <label>Tipo
        <select name="tipo" class="question-type">
            <option value="opcion_unica" @selected($tipo==='opcion_unica')>Opción única</option>
            <option value="seleccion_multiple" @selected($tipo==='seleccion_multiple')>Selección múltiple</option>
            <option value="verdadero_falso" @selected($tipo==='verdadero_falso')>Verdadero o falso</option>
            <option value="respuesta_directa" @selected($tipo==='respuesta_directa')>Respuesta escrita</option>
        </select>
    </label>
    <label>Tiempo por pregunta
        <div class="time-field"><input type="number" name="tiempo_limite" min="5" max="300" value="{{ old('tiempo_limite', $pregunta?->tiempo_limite ?? 20) }}" required><span>segundos</span></div>
    </label>
    <label>Puntos en examen
        <input type="number" name="puntos" min="1" max="1000" value="{{ old('puntos', $pregunta?->puntos ?? 1) }}" required>
    </label>
    <div class="wide choice-fields">
        <span class="field-title">Opciones y respuestas correctas</span>
        @foreach(['a','b','c','d'] as $letter)
        <div class="choice-row">
            <span class="letter">{{ strtoupper($letter) }}</span>
            <input type="text" name="opciones[{{ $letter }}]" maxlength="500" value="{{ $opciones[$letter] ?? '' }}" placeholder="Texto de la opción {{ strtoupper($letter) }}">
            <label class="unique-check" title="Respuesta correcta"><input type="radio" name="respuesta_unica" value="{{ $letter }}" @checked(in_array($letter,$correctas,true))> única</label>
            <label class="multiple-check" title="Respuesta correcta"><input type="checkbox" name="respuestas_multiples[]" value="{{ $letter }}" @checked(in_array($letter,$correctas,true))> correcta</label>
        </div>
        @endforeach
    </div>
    <div class="wide boolean-fields">
        <span class="field-title">Respuesta correcta</span>
        <label class="inline"><input type="radio" name="respuesta_booleana" value="1" @checked(in_array('1',$correctas,true) || in_array('true',$correctas,true))> Verdadero</label>
        <label class="inline"><input type="radio" name="respuesta_booleana" value="0" @checked(in_array('0',$correctas,true) || in_array('false',$correctas,true))> Falso</label>
    </div>
    <label class="wide written-fields">Respuestas válidas
        <textarea name="respuestas_escritas" rows="4" placeholder="Escribe una respuesta válida por línea. Puedes añadir alternativas con distinta redacción.">{{ $tipo === 'respuesta_directa' ? old('respuestas_escritas', implode(PHP_EOL, $correctas)) : old('respuestas_escritas') }}</textarea>
    </label>
    <label class="wide">Explicación después de responder
        <textarea name="explicacion" rows="3" maxlength="3000" placeholder="Opcional: explica por qué esa es la respuesta correcta.">{{ old('explicacion', $pregunta?->explicacion) }}</textarea>
    </label>
</div>

