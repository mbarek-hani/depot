<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
    <script>
        (function () {
            var theme = localStorage.getItem('theme') || 'system';
            var dark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>
    <style>
        :root {
            color-scheme: light;
            --bg: #faf6ee;
            --card: #ffffff;
            --text: #1c1917;
            --muted: #78716c;
            --line: #e7e5e4;
            --brand: #ca8a04;
            --danger: #e11d48;
            --danger-bg: #fff1f2;
            --warning: #d97706;
            --warning-bg: #fffbeb;
        }

        html.dark {
            color-scheme: dark;
            --bg: #0f0e0c;
            --card: #1c1a16;
            --text: #f5f5f4;
            --muted: #a8a29e;
            --line: #292524;
            --brand: #facc15;
            --danger: #fb7185;
            --danger-bg: #2a1219;
            --warning: #fbbf24;
            --warning-bg: #2a2110;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: var(--bg);
            color: var(--text);
            font-family: system-ui, -apple-system, "Segoe UI", Tahoma, sans-serif;
            line-height: 1.7;
        }

        .card {
            width: min(100%, 460px);
            padding: 32px;
            border: 1px solid var(--line);
            border-radius: 24px;
            background: var(--card);
            text-align: center;
            box-shadow: 0 24px 60px -32px rgba(0, 0, 0, .25);
        }

        .mark {
            display: grid;
            place-items: center;
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            border-radius: 20px;
            background: var(--tone-bg);
            color: var(--tone);
            font-weight: 800;
            font-size: 24px;
        }

        h1 { margin: 0 0 8px; font-size: 24px; }

        p { margin: 0; color: var(--muted); }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-top: 24px;
        }

        a {
            flex: 1 1 140px;
            padding: 11px 16px;
            border: 1px solid transparent;
            border-radius: 12px;
            background: var(--brand);
            color: #1c1917;
            font-weight: 700;
            text-decoration: none;
        }

        a.secondary {
            border-color: var(--line);
            background: transparent;
            color: var(--text);
        }
    </style>
</head>
<body style="--tone: @yield('tone', 'var(--brand)'); --tone-bg: @yield('tone-bg', 'var(--bg)');">
    <main class="card">
        <div class="mark">@yield('mark')</div>
        <h1>@yield('heading')</h1>
        <p>@yield('description')</p>
        <div class="actions">@yield('actions')</div>
    </main>
</body>
</html>
