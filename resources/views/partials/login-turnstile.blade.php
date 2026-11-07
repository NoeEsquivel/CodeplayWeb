@if (config('services.turnstile.enabled'))
    <div class="mb-4">
        <p class="small text-light mb-2">Verificación de seguridad</p>
        @if (config('services.turnstile.site_key'))
            <div class="d-flex justify-content-center">
                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-action="login" data-theme="dark" data-size="compact" data-language="es"></div>
            </div>
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        @else
            <p class="text-warning">La verificación no está disponible. Contacta al administrador.</p>
        @endif
        @if (config('services.turnstile.testing') && app()->environment(['local', 'testing']))
            <small class="d-block text-warning mt-2">Modo de prueba local: esta verificación no ofrece protección real contra bots.</small>
        @endif
    </div>
@endif
