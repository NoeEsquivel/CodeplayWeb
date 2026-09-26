<!doctype html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Seguridad') · CodePlay</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body{min-height:100vh;margin:0;background:linear-gradient(135deg,#07161c,#123a3d);color:#ecfff8;font-family:Arial,sans-serif;display:grid;place-items:center;padding:24px}
        .security-card{width:min(620px,100%);background:#0b2228;border:1px solid #55d6ae;border-radius:18px;padding:32px;box-shadow:0 18px 60px #0008}
        h1{color:#86efc8;font-size:1.8rem}.muted{color:#b9d3d2}.form-control{background:#102e35;border-color:#4f787a;color:#fff}.form-control:focus{background:#12383f;color:#fff;border-color:#86efc8;box-shadow:0 0 0 .2rem #55d6ae33}.btn-primary{background:#43b996;border:0;color:#041513;font-weight:700}.btn-link{color:#9ff6d7}.code{font-family:monospace;letter-spacing:.08em;background:#071a1f;padding:12px;border-radius:8px;overflow-wrap:anywhere}.alert{border:0}
    </style>
</head>
<body>
<main class="security-card">
    <div class="mb-4 fw-bold text-uppercase" style="letter-spacing:.18em;color:#e6c76e">CodePlay · Seguridad</div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
