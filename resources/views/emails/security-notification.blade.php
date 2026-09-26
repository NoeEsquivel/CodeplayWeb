<!doctype html>
<html lang="es">
<body style="font-family:Arial,sans-serif;color:#132a2d">
    <h1 style="font-size:22px">{{ $subjectLine }}</h1>
    <p style="white-space:pre-line">{{ $bodyText }}</p>
    @if($actionUrl)
        <p><a href="{{ $actionUrl }}" style="display:inline-block;padding:12px 18px;background:#146c5a;color:#fff;text-decoration:none;border-radius:6px">{{ $actionLabel ?: 'Continuar' }}</a></p>
    @endif
    <p>Si no realizaste esta acción, comunícate con la administración de tu institución.</p>
</body>
</html>
