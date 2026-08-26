{{-- ============================================================
     FILE      : Login.blade.php
     LETAK     : resources/views/auth/Login.blade.php
     DESKRIPSI : Halaman login SIPAKAR — Sistem Pakar Diagnosa
                 Penyakit Sapi untuk Peternak
     ============================================================ --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIGAP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* ── PALET: NAVY & BIRU (samain sama tema sidebar/dashboard) ── */
            --biru-tua:      #0b1220;   /* base gelap / warna utama, dulunya hijau-tua */
            --biru-sedang:   #3b82f6;   /* aksen utama, sama persis warna tombol, dulunya hijau-daun */
            --biru-terang:   #60a5fa;   /* aksen highlight, dulunya hijau-terang */
            --krem:          #f5f0e1;   /* susu / teks terang, tetep dipertahankan */
            --emas:          #c98a3e;   /* dipertahankan, jarang kepake langsung */
            --emas-terang:   #e0a851;
            --coklat-tanah:  #5c3d24;   /* tanah / kulit sapi, netral, gak disentuh */
            --overlay-gelap:
                radial-gradient(ellipse at 50% 45%, rgba(20,15,9,0.12) 0%, rgba(14,10,6,0.4) 55%, rgba(8,6,4,0.65) 100%),
                linear-gradient(180deg, rgba(10,8,5,0.35) 0%, rgba(10,8,5,0.05) 35%, rgba(10,8,5,0.45) 100%);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Public Sans', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;

            /* Background foto sapi asli — dari public/images/bg_sapi.jpg */
            background-image:
                var(--overlay-gelap),
                url('/images/bg_sapi.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        /* ── CARD (glassmorphism) ── */
        .login-card {
            background: rgba(11, 18, 32, 0.28);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(245, 240, 225, 0.25);
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.35);
            width: 100%;
            max-width: 400px;
            padding: 40px 34px 34px;
        }

        /* ── SIGNATURE: LOGO SAPI (dulunya badge bulat mirip ear-tag + ikon suntik) ── */
        .ear-tag {
            width: 112px; height: 112px;
            margin: 0 auto -15px;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        .ear-tag-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* dipaksa jadi siluet putih polos lewat CSS filter,
               karena file PNG-nya nggak bisa diwarnai sebagian lewat kode */
            filter: brightness(0) invert(1);
        }

        .login-title {
            font-family: 'Fraunces', serif;
            font-size: 26px; font-weight: 700;
            color: var(--krem);
            text-align: center;
            letter-spacing: -0.3px;
        }
        .login-subtitle {
            font-size: 12.5px;
            color: rgba(245,240,225,0.72);
            text-align: center;
            margin-top: 4px;
            margin-bottom: 30px;
            letter-spacing: 0.2px;
        }

        /* ── ALERT ERROR ── */
        .login-alert {
            background: rgba(220, 38, 38, 0.18);
            border: 1px solid rgba(248, 113, 113, 0.4);
            border-radius: 10px;
            padding: 11px 14px;
            display: flex; align-items: center; gap: 9px;
            margin-bottom: 20px;
            font-size: 13px; color: #fecaca;
        }
        .login-alert i { font-size: 16px; flex-shrink: 0; }

        /* ── FORM GROUP ── */
        .form-group { margin-bottom: 16px; }
        .form-label {
            display: block;
            font-size: 12px; font-weight: 600;
            color: rgba(245,240,225,0.85);
            margin-bottom: 6px;
            letter-spacing: 0.2px;
        }
        .form-label span { color: var(--biru-terang); margin-left: 2px; }

        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: rgba(11,18,32,0.5);
            font-size: 16px;
            pointer-events: none;
        }
        .form-input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 1px solid rgba(11,18,32,0.18);
            border-radius: 999px;
            font-size: 14px; font-family: inherit;
            font-weight: 500;
            color: var(--biru-tua);
            background: rgba(245, 240, 225, 0.92);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
        }
        .form-input::placeholder { color: rgba(11,18,32,0.45); }
        .form-input:focus {
            border-color: var(--biru-sedang);
            background: rgba(245, 240, 225, 0.98);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.28);
        }

        .btn-toggle-pwd {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer; padding: 4px;
            color: rgba(11,18,32,0.5);
            font-size: 16px;
            transition: color 0.15s;
        }
        .btn-toggle-pwd:hover { color: var(--biru-tua); }

        /* ── REMEMBER ME ── */
        .remember-row {
            display: flex; align-items: center; gap: 8px;
            margin: 6px 0 22px;
        }
        .remember-row input[type="checkbox"] {
            width: 15px; height: 15px;
            accent-color: var(--biru-sedang);
            cursor: pointer;
        }
        .remember-row label {
            font-size: 12.5px;
            color: rgba(245,240,225,0.8);
            cursor: pointer;
        }

        /* ── SUBMIT BUTTON ── */
        .btn-login {
            width: 100%;
            padding: 13px;
            border: none; border-radius: 999px;
            background: linear-gradient(135deg, var(--biru-terang), var(--biru-tua));
            color: var(--krem);
            font-size: 14px; font-weight: 700;
            font-family: inherit;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 7px;
            transition: transform 0.12s, box-shadow 0.15s;
            box-shadow: 0 4px 14px rgba(11,18,32,0.45);
        }
        .btn-login:hover   { box-shadow: 0 6px 18px rgba(59,130,246,0.5); }
        .btn-login:active  { transform: scale(0.98); }
        .btn-login i       { font-size: 17px; }

        /* ── FOOTER INFO ── */
        .login-footer {
            text-align: center;
            margin-top: 22px;
            font-size: 11.5px;
            color: rgba(245,240,225,0.55);
        }

        /* ── ROLE INFO PILLS ── */
        .role-info {
            display: flex; gap: 6px; justify-content: center;
            flex-wrap: wrap; margin-top: 18px;
        }
        .role-pill {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 11px; border-radius: 20px;
            font-size: 11px; font-weight: 600;
            background: rgba(245,240,225,0.12);
            color: var(--krem);
            border: 1px solid rgba(245,240,225,0.2);
        }
        .role-pill i { font-size: 12px; color: var(--biru-terang); }

        /* ── RESPONSIF ── */
        @media (max-width: 420px) {
            .login-card { padding: 32px 24px 26px; border-radius: 18px; }
            .login-title { font-size: 22px; }
            body { background-attachment: scroll; } /* fixed lambat di mobile */
        }

        /* ── AKSESIBILITAS: FOCUS RING KEYBOARD ── */
        a:focus-visible, button:focus-visible, input:focus-visible {
            outline: 2px solid var(--biru-terang);
            outline-offset: 2px;
        }

        /* ── HORMATI REDUCED MOTION ── */
        @media (prefers-reduced-motion: reduce) {
            .btn-login, .form-input, .btn-toggle-pwd { transition: none; }
        }
    </style>
</head>
<body>

<div class="login-card">

    <div class="ear-tag">
        <img src="{{ asset('images/iconsapi.png') }}" alt="SIGAP" class="ear-tag-img">
    </div>
    <div class="login-title">SIGAP</div>
    <div class="login-subtitle">Sistem Informasi Gejala &amp; Analisis Peternak</div>

    {{-- Alert error (dari session flash) --}}
    @if(session('login_error'))
    <div class="login-alert">
        <i class="ti ti-alert-circle"></i>
        <span>{{ session('login_error') }}</span>
    </div>
    @endif

    <form method="POST" action="/login">
        @csrf

        {{-- Email --}}
        <div class="form-group">
            <label class="form-label" for="email">
                Email <span>*</span>
            </label>
            <div class="input-wrap">
                <i class="ti ti-mail input-icon"></i>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input"
                    placeholder="contoh@email.com"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                >
            </div>
        </div>

        {{-- Password --}}
        <div class="form-group">
            <label class="form-label" for="password">
                Password <span>*</span>
            </label>
            <div class="input-wrap">
                <i class="ti ti-lock input-icon"></i>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input"
                    placeholder="Masukkan password"
                    required
                    autocomplete="current-password"
                    style="padding-right: 40px;"
                >
                <button type="button" class="btn-toggle-pwd" onclick="togglePwd()" id="toggleBtn" aria-label="Tampilkan password">
                    <i class="ti ti-eye" id="eyeIcon"></i>
                </button>
            </div>
        </div>

        {{-- Remember me --}}
        <div class="remember-row">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Ingat saya</label>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-login">
            <i class="ti ti-login"></i>
            Masuk
        </button>
    </form>

    <div class="login-footer">
        &copy; {{ date('Y') }} SIGAP — Sistem Pakar Peternakan
    </div>
</div>

<script>
    function togglePwd() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type   = 'text';
            icon.className = 'ti ti-eye-off';
        } else {
            input.type   = 'password';
            icon.className = 'ti ti-eye';
        }
    }
</script>
</body>
</html>