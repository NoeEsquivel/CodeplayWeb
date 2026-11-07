<button id="completeTutorialButton" type="button" class="btn btn-complete"
        @if ($videoAccess['unlocked'])
            onclick="completarTutorial()"
        @else
            disabled aria-describedby="tutorial-completion-requirements"
        @endif>
    <i class="fas {{ $videoAccess['unlocked'] ? 'fa-check-circle' : 'fa-lock' }} me-2" aria-hidden="true"></i>Completar Tutorial
</button>
@unless ($videoAccess['unlocked'])
    <p id="tutorial-completion-requirements" class="text-warning mt-3 mb-0">
        Completa las lecturas y los tests indicados arriba para habilitar este botón.
    </p>
@endunless
