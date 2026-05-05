<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>SMS Doğrulama</title>
    <style>
        :root {
            --background: #f5f0e8;
            --foreground: #3d3226;
            --card: #faf8f3;
            --primary: #7a5c3c;
            --border: #d9d0c2;
            --muted: #7a7060;
            --danger: #c53030;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            background: var(--background);
            color: var(--foreground);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            width: 100%;
            max-width: 430px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
        }
        h1 { font-size: 24px; margin-bottom: 8px; }
        p { color: var(--muted); font-size: 14px; line-height: 1.5; margin-bottom: 16px; }
        .alert {
            background: rgba(122,92,60,0.08);
            border: 1px solid rgba(122,92,60,0.2);
            color: var(--foreground);
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-size: 13px;
        }
        .error {
            background: rgba(197,48,48,0.08);
            border: 1px solid rgba(197,48,48,0.2);
            color: var(--danger);
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-size: 13px;
        }
        label { display: block; font-size: 14px; margin-bottom: 6px; }
        input {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
            padding: 11px 12px;
            font-size: 18px;
            letter-spacing: 4px;
            text-align: center;
            margin-bottom: 12px;
        }
        .btn {
            width: 100%;
            border: none;
            border-radius: 8px;
            padding: 11px 14px;
            font-size: 14px;
            cursor: pointer;
        }
        .btn:disabled { opacity: .6; cursor: not-allowed; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--foreground); margin-top: 10px; }
        .actions { margin-top: 6px; }
        .countdown { margin-top: 8px; font-size: 12px; color: var(--muted); text-align: center; }
    </style>
</head>
<body>
<div class="card">
    <h1>SMS Doğrulama</h1>
    <p>
        <strong>{{ $maskedPhone }}</strong>
        numarasına gönderilen 6 haneli doğrulama kodunu girin.
    </p>

    @if(session('twofactor_info'))
        <div class="alert">{{ session('twofactor_info') }}</div>
    @endif

    @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('auth.twofactor.verify') }}">
        @csrf
        <label for="code">Doğrulama Kodu</label>
        <input id="code" name="code" type="text" maxlength="6" inputmode="numeric" autocomplete="one-time-code" autofocus value="{{ old('code') }}" />
        <button type="submit" class="btn btn-primary">Doğrula ve Giriş Yap</button>
    </form>

    <form method="POST" action="{{ route('auth.twofactor.resend') }}" class="actions">
        @csrf
        <button type="submit" class="btn btn-outline" id="resendBtn">Kodu Yeniden Gönder</button>
        <div class="countdown" id="resendCountdown"></div>
    </form>
</div>

<script>
    var codeInput = document.getElementById('code');
    codeInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
    });

    var resendBtn = document.getElementById('resendBtn');
    var resendCountdown = document.getElementById('resendCountdown');
    var secondsLeft = {{ (int) ($remainingSeconds ?? 0) }};

    function fmt(sec) {
        var m = Math.floor(sec / 60);
        var s = sec % 60;
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    function tick() {
        if (secondsLeft > 0) {
            resendBtn.disabled = true;
            resendCountdown.textContent = 'Yeni kod ' + fmt(secondsLeft) + ' sonra gönderilebilir.';
            secondsLeft--;
            return;
        }

        resendBtn.disabled = false;
        resendCountdown.textContent = '';
        clearInterval(timerId);
    }

    var timerId = setInterval(tick, 1000);
    tick();
</script>
</body>
</html>
