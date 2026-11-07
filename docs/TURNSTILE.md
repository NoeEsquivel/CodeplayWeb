# Verificación del login con Cloudflare Turnstile

El formulario y el servidor verifican Turnstile antes de consultar las credenciales.
Si está habilitado, los errores de conexión, los tokens inválidos o la falta de
configuración impiden ingresar. El límite de intentos del login se conserva.

## Prueba en esta computadora

La instalación local usa las claves oficiales públicas de prueba, con internet:

```dotenv
TURNSTILE_ENABLED=true
TURNSTILE_TESTING=true
TURNSTILE_SITE_KEY=1x00000000000000000000AA
TURNSTILE_SECRET_KEY=1x0000000000000000000000000000000AA
TURNSTILE_HOSTNAMES=localhost,127.0.0.1
```

El modo de prueba requiere APP_ENV=local (o testing) y acceso mediante localhost
o 127.0.0.1. No ofrece protección real. No se admite en producción.
Para trabajar sin internet, configura TURNSTILE_ENABLED=false.

## Activación en codeplay.lat

1. En el panel de Cloudflare, abre Turnstile y añade un widget.
2. Ponle un nombre como CodePlay y autoriza codeplay.lat. Añade www.codeplay.lat
   solo si también utilizas ese dominio. Elige el modo administrado (Managed).
3. Copia las claves reales al .env del servidor, sin registrarlas en Git:

```dotenv
APP_ENV=production
TURNSTILE_ENABLED=true
TURNSTILE_TESTING=false
TURNSTILE_SITE_KEY=CLAVE_PUBLICA_REAL
TURNSTILE_SECRET_KEY=CLAVE_SECRETA_REAL
TURNSTILE_HOSTNAMES=codeplay.lat
```

4. Ejecuta `php artisan config:cache` después de guardar la configuración.
5. Abre el login y comprueba un acceso. La verificación valida la acción login y
   el hostname devuelto por Cloudflare. Tanto el navegador como el servidor deben
   poder conectar con challenges.cloudflare.com.

El .env de esta computadora no se sincroniza por Git. Actualizar el repositorio
del servidor no configura las claves ni activa el widget por sí solo.

Referencias oficiales:
- https://developers.cloudflare.com/turnstile/troubleshooting/testing/
- https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
