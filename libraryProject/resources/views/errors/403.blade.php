<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Yetkisiz Erişim</title>
    <style>
        :root {
            color-scheme: light dark;
            --bg: #0b1020;
            --card: #121a30;
            --text: #e8ecf8;
            --muted: #9aa6c4;
            --accent: #4f8cff;
            --accent-hover: #3d79ea;
            --border: rgba(255, 255, 255, 0.12);
            --danger: #ff6b6b;
        }

        @media (prefers-color-scheme: light) {
            :root {
                --bg: #f3f6ff;
                --card: #ffffff;
                --text: #1c2745;
                --muted: #5f6d90;
                --accent: #2f6df6;
                --accent-hover: #255dd3;
                --border: rgba(20, 35, 80, 0.12);
                --danger: #d73838;
            }
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: "Inter", "Segoe UI", Roboto, Arial, sans-serif;
            background:
                radial-gradient(1200px 500px at 10% -10%, rgba(79, 140, 255, 0.18), transparent 50%),
                radial-gradient(1000px 400px at 90% 110%, rgba(255, 107, 107, 0.14), transparent 50%),
                var(--bg);
            color: var(--text);
            padding: 24px;
        }

        .card {
            width: 100%;
            max-width: 620px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
            padding: 28px;
        }

        .code {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: var(--danger);
            background: rgba(255, 107, 107, 0.12);
            border: 1px solid rgba(255, 107, 107, 0.28);
            border-radius: 999px;
            padding: 6px 12px;
            margin-bottom: 14px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: clamp(1.6rem, 2.8vw, 2.2rem);
            line-height: 1.2;
        }

        p {
            margin: 0;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.6;
        }

        .actions {
            margin-top: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 10px;
            border: 1px solid transparent;
            padding: 11px 16px;
            font-weight: 600;
            font-size: .95rem;
            cursor: pointer;
            text-decoration: none;
            transition: .18s ease;
        }

        .btn-primary {
            color: #fff;
            background: var(--accent);
        }
        .btn-primary:hover { background: var(--accent-hover); }

        .btn-ghost {
            color: var(--text);
            background: transparent;
            border-color: var(--border);
        }
        .btn-ghost:hover {
            border-color: var(--accent);
            color: var(--accent);
        }
    </style>
</head>
<body>
    <main class="card" role="main" aria-labelledby="page-title">
        <div class="code">403 • Yetkisiz Erişim</div>
        <h1 id="page-title">Bu sayfaya erişim yetkiniz bulunmuyor.</h1>
        <p>
            Görüntülemeye çalıştığınız işlem için gerekli izinlere sahip değilsiniz.
            Lütfen bir önceki sayfaya dönün veya ana sayfadan devam edin.
        </p>

        <div class="actions">
            <button type="button" class="btn btn-primary" onclick="goBack()">Geri git</button>
            <a href="{{ url('/') }}" class="btn btn-ghost">Ana sayfaya dön</a>
        </div>
    </main>

    <script>
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
                return;
            }
            window.location.href = @json(url('/'));
        }
    </script>
</body>
</html>
