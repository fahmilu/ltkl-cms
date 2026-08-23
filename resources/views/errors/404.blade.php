@php
    // Error views render while something is already going wrong, so this page is
    // deliberately self-contained: no Vite bundle, no layout, no components.
    $panelUrl = \Illuminate\Support\Facades\Route::has('filament.administrator.auth.login')
        ? route('filament.administrator.auth.login')
        : url('/administrator');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>404 &middot; {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <style>
        :root {
            --bg: #f6f7f7;
            --panel: #ffffff;
            --border: rgba(18, 41, 43, 0.10);
            --text: #12292b;
            --muted: #5b6b6c;
            --accent: #1495ab;
            --accent-contrast: #ffffff;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0e1c1d;
                --panel: #12292b;
                --border: rgba(255, 255, 255, 0.10);
                --text: #f1f5f4;
                --muted: #9fb0b1;
                --accent: #26b6cf;
                --accent-contrast: #06181a;
            }
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: var(--bg);
            color: var(--text);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .card {
            width: 100%;
            max-width: 30rem;
            padding: 2.5rem;
            border: 1px solid var(--border);
            border-radius: .5rem;
            background: var(--panel);
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04), 0 12px 32px rgba(0, 0, 0, .06);
            text-align: center;
        }

        .logo { height: 34px; width: auto; margin-bottom: 1.75rem; }

        .code {
            margin: 0;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--accent);
        }

        h1 {
            margin: .5rem 0 0;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -.01em;
        }

        p {
            margin: .75rem 0 0;
            color: var(--muted);
            font-size: .9375rem;
        }

        .actions { margin-top: 1.75rem; }

        .btn {
            display: inline-block;
            padding: .625rem 1.25rem;
            border-radius: .25rem;
            background: var(--accent);
            color: var(--accent-contrast);
            font-size: .8125rem;
            font-weight: 800;
            letter-spacing: -.01em;
            text-transform: uppercase;
            text-decoration: none;
        }

        .btn:hover { opacity: .9; }

        .btn:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <main class="card">
        @if (file_exists(public_path('logo.svg')))
            <img class="logo" src="{{ asset('logo.svg') }}" alt="{{ config('app.name') }}">
        @endif

        <p class="code">Error 404</p>
        <h1>Page not found</h1>
        <p>
            Please check the URL and try again.
        </p>
        {{-- <div class="actions">
            <a class="btn" href="{{ $panelUrl }}">Go to the CMS</a>
        </div> --}}
    </main>
</body>
</html>
