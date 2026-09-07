<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIGAP - @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            /* ── SIDEBAR: navy gelap bergradasi ke biru, ganti tema ijo lama ── */
            --sidebar-bg: #0b1220;             /* solid fallback, dipake juga buat warna teks di atas accent terang */
            --sidebar-bg-start: #0b1220;
            --sidebar-bg-mid: #13233f;
            --sidebar-bg-end: #1c3f7c;
            --sidebar-hover: rgba(96,165,250,0.14);
            --sidebar-active-bg: linear-gradient(90deg, rgba(59,130,246,0.42), rgba(59,130,246,0.10));
            --sidebar-border: rgba(255,255,255,0.08);
            --sidebar-text: #8ea0c0;
            --sidebar-text-active: #ffffff;
            --sidebar-accent: #60a5fa;   /* biru terang, aksen khusus di atas background gelap */
            --sidebar-width: 260px;
            --topbar-h: 64px;

            /* ── KONTEN: biru yang SAMA PERSIS sama warna tombol (button.blade.php) biar satu tema ── */
            --content-accent: #3b82f6;
            --content-accent-dark: #2563eb;

            --main-bg: #f5f6fa;
            --card-bg: #ffffff;
            --text-primary: #1a1d2e;
            --text-secondary: #6b7280;
            --border: #e5e7eb;

            --danger: #ef4444;
            --danger-dark: #dc2626;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--main-bg);
            color: var(--text-primary);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--sidebar-bg-start) 0%, var(--sidebar-bg-mid) 70%, var(--sidebar-bg-end) 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            z-index: 300;
            transition: transform 0.25s ease;
        }

        .sidebar-logo {
            padding: 16px 20px 8px;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-logo .logo-icon {
            width: 74px;
            height: 74px;
            background: transparent;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        /* Gambar sapi dipaksa jadi siluet putih polos lewat CSS filter,
           karena file PNG-nya nggak bisa diwarnai sebagian lewat kode */
        .sidebar-logo .logo-icon-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .sidebar-logo .logo-text {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.3px;
        }

        .sidebar-logo .logo-sub {
            font-size: 9px;
            line-height: 1.3;
            color: var(--sidebar-text);
        }

        .sidebar-section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(142,160,192,0.55);
            padding: 18px 20px 6px;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 4px 12px;
        }

        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        .nav-item { margin-bottom: 2px; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.18s ease;
            position: relative;
            cursor: pointer;
        }

        .nav-link:hover { background: var(--sidebar-hover); color: var(--sidebar-text-active); }

        .nav-link.active {
            background: var(--sidebar-active-bg);
            color: var(--sidebar-text-active);
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);   
            width: 3px; height: 18px;
            background: var(--sidebar-accent);
            border-radius: 0 3px 3px 0;
        }

        .nav-link i { font-size: 18px; opacity: 0.75; transition: opacity 0.18s; }
        .nav-link:hover i, .nav-link.active i { opacity: 1; }

        .nav-link .chevron { margin-left: auto; font-size: 14px; transition: transform 0.2s; }
        .nav-link.collapsed .chevron { transform: rotate(-90deg); }

        .submenu { overflow: hidden; transition: max-height 0.25s ease; }

        .submenu-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px 7px 42px;
            border-radius: 7px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 13px;
            transition: all 0.15s;
        }

        .submenu-link::before {
            content: '';
            width: 5px; height: 5px;
            border-radius: 50%;
            background: rgba(142,160,192,0.45);
            flex-shrink: 0;
            transition: background 0.15s;
        }

        .submenu-link:hover { color: #fff; }
        .submenu-link:hover::before { background: var(--sidebar-accent); }
        .submenu-link.active { color: var(--sidebar-accent); font-weight: 500; }
        .submenu-link.active::before { background: var(--sidebar-accent); }

        .sidebar-bottom {
            padding: 12px;
            border-top: 1px solid var(--sidebar-border);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.18s;
        }

        .user-card:hover { background: var(--sidebar-hover); }

        .user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--sidebar-accent), var(--content-accent-dark));
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
        }

        .user-name { font-size: 13px; font-weight: 600; color: #fff; }
        .user-role { font-size: 11px; color: var(--sidebar-text); }

        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-width: 0; /* penting: cegah overflow horizontal dari konten (tabel dsb) */
            transition: margin-left 0.25s ease;
        }

        .topbar {
            height: var(--topbar-h);
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 28px;
            position: sticky; top: 0;
            z-index: 50;
            gap: 16px;
        }

        /* ── Tombol hamburger: kotak gelap rounded + 3 garis staggered ── */
        .topbar-hamburger {
            display: none;              /* DESKTOP: hamburger selalu disembunyikan di sini */
            background: var(--sidebar-bg-mid);
            border: none;
            border-radius: 10px;
            width: 38px;
            height: 38px;
            padding: 0;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            cursor: pointer;
        }
        .topbar-hamburger:hover { background: var(--sidebar-bg-end); }
        .topbar-hamburger .burger-bar {
            display: block;
            height: 2.5px;
            border-radius: 2px;
            background: #aab8d6;
            transition: background 0.15s;
        }
        .topbar-hamburger .burger-bar:nth-child(1) { width: 14px; }
        .topbar-hamburger .burger-bar:nth-child(2) { width: 20px; }
        .topbar-hamburger .burger-bar:nth-child(3) { width: 17px; }
        .topbar-hamburger:hover .burger-bar { background: #fff; }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--text-secondary);
            min-width: 0;
        }

        .breadcrumb span {
            color: var(--text-primary);
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .breadcrumb-logo {
            width: 44px; height: 44px;
            background: transparent;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .breadcrumb-logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .topbar-right { margin-left: auto; display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

        .topbar-btn {
            width: 36px; height: 36px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: transparent;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 18px;
            transition: all 0.15s;
            flex-shrink: 0;
        }

        .topbar-btn:hover { background: var(--main-bg); color: var(--text-primary); }

        .topbar-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--content-accent), var(--content-accent-dark));
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .page-content { flex: 1; padding: 28px; min-width: 0; }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 24px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 12px;
        }

        .card-title { font-size: 15px; font-weight: 600; }

        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; }
        .form-control {
            width: 100%;
            padding: 9px 13px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--text-primary);
            background: var(--card-bg);
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
        }
        .form-control:focus { border-color: var(--content-accent); box-shadow: 0 0 0 3px rgba(59,130,246,0.14); }
        .form-control::placeholder { color: #b0b5c5; }

        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13.5px; font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.15s;
            border: none;
            appearance: none; -webkit-appearance: none;
        }

        .btn-primary,
        .btn-primary:focus,
        .btn-primary:active { background: var(--content-accent); color: #fff; }
        .btn-primary:hover { background: var(--content-accent-dark); }
        .btn-secondary,
        .btn-secondary:focus,
        .btn-secondary:active { background: var(--main-bg); color: var(--text-primary); border: 1px solid var(--border); }
        .btn-secondary:hover { background: #eaedf5; }
        .btn-danger,
        .btn-danger:focus,
        .btn-danger:active { background: var(--danger); color: #fff; }
        .btn-danger:hover { background: var(--danger-dark); }

        /* ── Modal konfirmasi sign out ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10,13,25,0.55);
            z-index: 500;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.show { display: flex; }

        .modal-box {
            background: var(--card-bg);
            border-radius: 14px;
            padding: 28px 26px;
            width: 100%;
            max-width: 340px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
            animation: modalPop 0.18s ease;
        }
        @keyframes modalPop {
            from { transform: scale(0.94); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .modal-icon {
            width: 52px; height: 52px;
            margin: 0 auto 14px;
            border-radius: 50%;
            background: rgba(239,68,68,0.12);
            color: var(--danger);
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
        }

        .modal-title { font-size: 15.5px; font-weight: 700; margin-bottom: 6px; }
        .modal-text { font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 22px; }

        .modal-actions { display: flex; gap: 10px; }
        .modal-actions .btn { flex: 1; justify-content: center; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; }

        .stat-card {
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 20px;
            display: flex; align-items: flex-start; gap: 14px;
        }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }

        .stat-label { font-size: 12px; color: var(--text-secondary); font-weight: 500; margin-bottom: 4px; }
        .stat-value { font-size: 24px; font-weight: 700; }
        .stat-sub { font-size: 12px; color: #22c55e; margin-top: 2px; }

        /* backdrop gelap di belakang sidebar saat mode mobile terbuka */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10,13,25,0.5);
            z-index: 250;
        }
        .sidebar-backdrop.show { display: block; }

        /* ============================================================
           RESPONSIVE — berlaku global ke semua halaman yang pakai
           layout ini, jadi tidak perlu diulang per modul.
           ============================================================ */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: 0 0 30px rgba(0,0,0,0.25);
            }
            .sidebar.open { transform: translateX(0); }

            .main-wrapper { margin-left: 0; }

            .topbar-hamburger { display: flex; }   /* MOBILE: baru muncul di sini */

            .topbar {
                padding: 0 16px;
                gap: 12px;
                /* topbar jadi gelap kaya sidebar pas mobile */
                background: linear-gradient(180deg, var(--sidebar-bg-start) 0%, var(--sidebar-bg-mid) 100%);
                border-bottom: none;
            }

            .breadcrumb { gap: 12px; }
            .breadcrumb span { font-size: 16px; font-weight: 600; color: #fff; }
            .breadcrumb-logo { width: 40px; height: 40px; }
            /* logo breadcrumb ikut jadi putih polos, konsisten sama logo sidebar */
            .breadcrumb-logo-img { filter: brightness(0) invert(1); }

            /* tombol bell disesuaikan ke tema gelap */
            .topbar-btn {
                border-color: rgba(255,255,255,0.15);
                color: var(--sidebar-text);
            }
            .topbar-btn:hover {
                background: rgba(255,255,255,0.08);
                color: #fff;
            }

            .page-content { padding: 16px; }

            .grid-2, .grid-3 { grid-template-columns: 1fr; }

            .card { padding: 18px; }
            .card-header { flex-direction: column; align-items: stretch; }
            .card-header > div:last-child { display: flex; flex-wrap: wrap; gap: 8px; }
            .card-header .btn { flex: 1; justify-content: center; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeSidebar()"></div>

<aside class="sidebar" id="mainSidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">
            <img src="{{ asset('images/iconsapi.png') }}" alt="SIGAP" class="logo-icon-img">
        </div>
        <div>
            <div class="logo-text">SIGAP</div>
            <div class="logo-sub">Sistem Informasi Gejala<br>&amp; Analisis Peternak</div>
        </div>
    </div>

    <nav class="sidebar-nav">

        <div class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="ti ti-layout-dashboard"></i> Dashboard
            </a>
        </div>

        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('konsultasi.*') ? 'active' : 'collapsed' }}"
               onclick="toggleMenu(this, 'menu-konsultasi'); return false;">
                <i class="ti ti-stethoscope"></i> Konsultasi
                <i class="ti ti-chevron-down chevron"></i>
            </a>
            <div class="submenu" id="menu-konsultasi"
                 style="max-height:{{ request()->routeIs('konsultasi.*') ? '300px' : '0' }}">
                <a href="{{ route('konsultasi.baru') }}"
                   class="submenu-link {{ request()->routeIs('konsultasi.baru') ? 'active' : '' }}">
                    Mulai Konsultasi
                </a>
                <a href="{{ route('konsultasi.index') }}"
                   class="submenu-link {{ request()->routeIs('konsultasi.index') || request()->routeIs('konsultasi.hasil') ? 'active' : '' }}">
                    Riwayat Konsultasi
                </a>
            </div>
        </div>

        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('pengetahuan.*') ? 'active' : 'collapsed' }}"
               onclick="toggleMenu(this, 'menu-pengetahuan'); return false;">
                <i class="ti ti-database"></i> Basis Pengetahuan
                <i class="ti ti-chevron-down chevron"></i>
            </a>
            <div class="submenu" id="menu-pengetahuan"
                 style="max-height:{{ request()->routeIs('pengetahuan.*') ? '300px' : '0' }}">
                <a href="{{ route('pengetahuan.penyakit.index') }}"
                   class="submenu-link {{ request()->routeIs('pengetahuan.penyakit*') ? 'active' : '' }}">
                    Manajemen Penyakit
                </a>
                <a href="{{ route('pengetahuan.gejala.index') }}"
                   class="submenu-link {{ request()->routeIs('pengetahuan.gejala*') ? 'active' : '' }}">
                    Manajemen Gejala
                </a>
                <a href="{{ route('pengetahuan.relasi.index') }}"
                   class="submenu-link {{ request()->routeIs('pengetahuan.relasi*') ? 'active' : '' }}">
                    Relasi Penyakit-Gejala
                </a>
            </div>
        </div>

        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('sapi.*') || request()->routeIs('peternak.*') || request()->routeIs('monitoring.*') ? 'active' : 'collapsed' }}"
               onclick="toggleMenu(this, 'menu-sapi'); return false;">
                <i class="ti ti-paw"></i> Mainpi
                <i class="ti ti-chevron-down chevron"></i>
            </a>
            <div class="submenu" id="menu-sapi"
                 style="max-height:{{ request()->routeIs('sapi.*') || request()->routeIs('peternak.*') || request()->routeIs('monitoring.*') ? '300px' : '0' }}">
                <a href="{{ route('sapi.index') }}"
                   class="submenu-link {{ request()->routeIs('sapi.*') ? 'active' : '' }}">
                    Manajemen Sapi
                </a>
                <a href="{{ route('peternak.index') }}"
                   class="submenu-link {{ request()->routeIs('peternak.*') ? 'active' : '' }}">
                    Manajemen Peternak
                </a>
                <a href="{{ route('monitoring.index') }}"
                   class="submenu-link {{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
                    Monitoring Sapi
                </a>
            </div>
        </div>

        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('administrator.*') ? 'active' : 'collapsed' }}"
               onclick="toggleMenu(this, 'menu-administrator'); return false;">
                <i class="ti ti-shield-lock"></i> Administrator
                <i class="ti ti-chevron-down chevron"></i>
            </a>
            <div class="submenu" id="menu-administrator"
                 style="max-height:{{ request()->routeIs('administrator.*') ? '300px' : '0' }}">
                <a href="{{ route('administrator.useraccess') }}"
                   class="submenu-link {{ request()->routeIs('administrator.useraccess') ? 'active' : '' }}">
                    User Access
                </a>
            </div>
        </div>

    </nav>

    <div class="sidebar-bottom">
        @php
            $sesiUser = session('user');
            $namaUser = $sesiUser['nama'] ?? 'User';
            $roleUser = match ((string) ($sesiUser['role'] ?? '')) {
                '1'     => 'Dokter',
                '2'     => 'Administrator',
                default => 'User',
            };
            $inisialUser = collect(explode(' ', trim($namaUser)))
                ->filter()
                ->map(fn ($w) => strtoupper(substr($w, 0, 1)))
                ->take(2)
                ->implode('');
        @endphp
        <div class="user-card">
            <div class="user-avatar">{{ $inisialUser ?: 'U' }}</div>
            <div>
                <div class="user-name">{{ $namaUser }}</div>
                <div class="user-role">{{ $roleUser }}</div>
            </div>
            <i class="ti ti-logout" style="margin-left:auto;color:var(--sidebar-text);cursor:pointer"
               onclick="event.stopPropagation(); openLogoutModal();"
               title="Keluar"></i>
        </div>
    </div>
</aside>

{{-- Form logout tersembunyi, disubmit lewat JS setelah user konfirmasi --}}
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
    @csrf
</form>

{{-- Modal konfirmasi sign out --}}
<div class="modal-overlay" id="logoutModalOverlay" onclick="if(event.target === this) closeLogoutModal();">
    <div class="modal-box">
        <div class="modal-icon"><i class="ti ti-logout-2"></i></div>
        <div class="modal-title">Konfirmasi Keluar</div>
        <div class="modal-text">Apakah Anda yakin ingin keluar dari akun ini?</div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeLogoutModal();">Batal</button>
            <button type="button" class="btn btn-danger" onclick="confirmLogout();">Ya, Keluar</button>
        </div>
    </div>
</div>

<div class="main-wrapper">
    <header class="topbar">
        <button class="topbar-hamburger" onclick="toggleSidebar()" aria-label="Buka menu">
            <span class="burger-bar"></span>
            <span class="burger-bar"></span>
            <span class="burger-bar"></span>
        </button>
        <div class="breadcrumb">
            <div class="breadcrumb-logo">
                <img src="{{ asset('images/iconsapi.png') }}" alt="SIGAP" class="breadcrumb-logo-img">
            </div>
            <span>@yield('title', 'Dashboard')</span>
        </div>
        <div class="topbar-right">
            <button class="topbar-btn"><i class="ti ti-bell"></i></button>
            <div class="topbar-avatar">AD</div>
        </div>
    </header>

    <main class="page-content">
        @yield('content')
    </main>
</div>

<script>
function toggleMenu(link, menuId) {
    const menu = document.getElementById(menuId);
    const isOpen = menu.style.maxHeight !== '0px' && menu.style.maxHeight !== '';
    document.querySelectorAll('.submenu').forEach(m => m.style.maxHeight = '0');
    document.querySelectorAll('.nav-link').forEach(l => {
        if (l.querySelector('.chevron')) {
            l.classList.remove('active');
            l.classList.add('collapsed');
        }
    });
    if (!isOpen) {
        menu.style.maxHeight = '300px';
        link.classList.remove('collapsed');
        link.classList.add('active');
    }
}

// ================= SIDEBAR MOBILE (off-canvas) =================
function toggleSidebar() {
    document.getElementById('mainSidebar').classList.toggle('open');
    document.getElementById('sidebarBackdrop').classList.toggle('show');
}
function closeSidebar() {
    document.getElementById('mainSidebar').classList.remove('open');
    document.getElementById('sidebarBackdrop').classList.remove('show');
}
// otomatis nutup sidebar kalau user tap salah satu link menu (biar nggak nutupin konten)
document.querySelectorAll('.sidebar a.submenu-link, .sidebar > nav > .nav-item > a.nav-link:not([onclick])').forEach(function (el) {
    el.addEventListener('click', function () {
        if (window.innerWidth <= 900) closeSidebar();
    });
});
// reset state sidebar kalau layar di-resize balik ke desktop
window.addEventListener('resize', function () {
    if (window.innerWidth > 900) closeSidebar();
});

// ================= MODAL KONFIRMASI SIGN OUT =================
function openLogoutModal() {
    document.getElementById('logoutModalOverlay').classList.add('show');
}
function closeLogoutModal() {
    document.getElementById('logoutModalOverlay').classList.remove('show');
}
function confirmLogout() {
    document.getElementById('logout-form').submit();
}
// tutup modal kalau tekan Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeLogoutModal();
});
</script>
<script>
// Hapus tombol hamburger dari HTML kalau bukan mode mobile (bukan cuma disembunyikan via CSS)
(function kelolaHamburger() {
    var BATAS_MOBILE = 768;
    var btn = document.querySelector('.topbar-hamburger');
    if (!btn) return;

    // simpan salinan elemen aslinya biar bisa dimunculkan lagi kalau layar diperkecil
    var placeholder = document.createComment('topbar-hamburger-placeholder');
    var btnAsli = btn;
    var terpasang = true;

    function update() {
        var mobile = window.innerWidth <= BATAS_MOBILE;
        if (mobile && !terpasang) {
            placeholder.parentNode.insertBefore(btnAsli, placeholder);
            placeholder.remove();
            terpasang = true;
        } else if (!mobile && terpasang) {
            btnAsli.parentNode.insertBefore(placeholder, btnAsli);
            btnAsli.remove();
            terpasang = false;
        }
    }

    update();
    window.addEventListener('resize', update);
})();
</script>

@stack('scripts')

<x-alert />

</body>
</html>