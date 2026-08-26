{{--
    ============================================================
    <x-alert /> — Reusable Alert Modal Component
    ============================================================
    Taruh SEKALI di layout utama (contoh: resources/views/layouts/app.blade.php),
    sebelum tag </body>:

        <x-alert />

    Setelah itu, di modul/halaman MANAPUN, tinggal panggil lewat JS,
    TANPA perlu include ulang HTML/CSS/JS ini:

        // Konfirmasi delete
        UAAlert.confirm({
            title: 'Delete User Access?',
            text: 'Are you sure? This action cannot be undone.',
            confirmText: 'Yes, Delete',   // optional, default 'Yes, Delete'
            cancelText: 'Cancel',         // optional, default 'Cancel'
            onConfirm: function () {
                // taruh logic delete (fetch/ajax) di sini
            }
        });

        // Alert sukses
        UAAlert.success('Data berhasil disimpan!');

        // Alert error
        UAAlert.error('Gagal menyimpan data!');

    Desain (warna, radius, animasi ring) ini adalah PATOKAN —
    jangan diubah dari sini. Kalau modul lain butuh tampilan beda,
    itu artinya bikin component baru, bukan modif file ini.

    CATATAN PENTING:
    Component ini SENGAJA tidak pakai @push('styles')/@push('scripts').
    Karena <x-alert /> dipasang di bagian BAWAH layout (setelah
    @stack('styles') di <head> dan @stack('scripts') sebelum </body>
    sudah lebih dulu dieksekusi), @push di sini akan telat dan
    CSS/JS-nya tidak akan pernah muncul. Makanya style & script
    ditulis langsung (inline) di sini, dibungkus @once biar aman
    kalau ada yang tidak sengaja panggil <x-alert /> lebih dari sekali.
    ============================================================
--}}

@once
<style>
    /* Reset & Base (khusus elemen di dalam alert modal ini) */
    .ua-alert-modal-overlay button,
    .ua-alert-modal-overlay input {
        box-shadow: none !important;
    }
    .ua-alert-modal-overlay button:focus,
    .ua-alert-modal-overlay button:active {
        box-shadow: none !important; outline: none !important;
    }

    .ua-alert-modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.4);
        z-index: 9998; display: none; align-items: center; justify-content: center;
        backdrop-filter: blur(2px);
    }
    .ua-alert-modal-overlay.show { display: flex; }

    @keyframes ua-alert-modal-in {
        from { opacity: 0; transform: translateY(8px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .ua-alert-box {
        background: #fff; border-radius: 18px; width: 100%; max-width: 380px; margin: 20px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
        overflow: hidden; animation: ua-alert-modal-in 0.2s ease;
        padding: 32px 26px 26px; text-align: center;
    }

    .ua-alert-ring {
        width: 74px; height: 74px; border-radius: 50%;
        border: 3px solid #f59e0b;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 18px; font-size: 30px; color: #f59e0b;
    }
    .ua-alert-ring.success { border: none; }
    .ua-alert-ring.error   { border: none; }

    .ua-alert-title {
        font-size: 19px; font-weight: 700; color: #1f2937; margin: 0 0 8px;
    }
    .ua-alert-text {
        font-size: 14.5px; color: #6b7280; line-height: 1.55; margin: 0;
    }
    .ua-alert-actions {
        display: flex; justify-content: center; gap: 10px; margin-top: 24px;
    }
    .ua-alert-btn {
        border: none; border-radius: 10px; padding: 11px 26px;
        font-size: 14px; font-weight: 600; font-family: inherit;
        cursor: pointer; transition: filter 0.15s ease, transform 0.1s ease;
    }
    .ua-alert-btn:hover  { filter: brightness(0.93); }
    .ua-alert-btn:active { transform: scale(0.97); }
    .ua-alert-btn.cancel  { background: #eef0f4; color: #374151; }
    .ua-alert-btn.danger  { background: #e11d3f; color: #fff; }
    .ua-alert-btn.primary { background: #3b82f6; color: #fff; }

    /* Animated success checkmark (SVG circle + check draw-in) */
    .ua-check-svg { width: 74px; height: 74px; display: block; }
    .ua-check-circle {
        fill: none; stroke: #10b981; stroke-width: 3;
        stroke-dasharray: 170; stroke-dashoffset: 170;
        animation: ua-check-circle-draw 0.5s ease-out forwards;
    }
    .ua-check-mark {
        fill: none; stroke: #10b981; stroke-width: 4;
        stroke-linecap: round; stroke-linejoin: round;
        stroke-dasharray: 48; stroke-dashoffset: 48;
        animation: ua-check-mark-draw 0.35s 0.45s ease-out forwards;
    }
    @keyframes ua-check-circle-draw { to { stroke-dashoffset: 0; } }
    @keyframes ua-check-mark-draw   { to { stroke-dashoffset: 0; } }

    /* Animated error ring (X draw-in) */
    .ua-x-svg { width: 74px; height: 74px; display: block; }
    .ua-x-circle {
        fill: none; stroke: #ef4444; stroke-width: 3;
        stroke-dasharray: 170; stroke-dashoffset: 170;
        animation: ua-check-circle-draw 0.5s ease-out forwards;
    }
    .ua-x-mark {
        fill: none; stroke: #ef4444; stroke-width: 4; stroke-linecap: round;
        stroke-dasharray: 30; stroke-dashoffset: 30;
        animation: ua-check-mark-draw 0.3s 0.45s ease-out forwards;
    }

    /* Static warning triangle ring (dipakai untuk confirm dialog) */
    .ua-alert-ring i.ti-alert-triangle { font-size: 30px; color: #f59e0b; }
</style>
@endonce

{{-- CONFIRM MODAL (dipakai untuk delete / aksi berbahaya lainnya) --}}
<div class="ua-alert-modal-overlay" id="uaConfirmOverlay">
    <div class="ua-alert-box">
        <div class="ua-alert-ring" id="uaConfirmRing"><i class="ti ti-alert-triangle"></i></div>
        <p class="ua-alert-title" id="uaConfirmTitle">Are you sure?</p>
        <p class="ua-alert-text" id="uaConfirmText"></p>
        <div class="ua-alert-actions">
            <button class="ua-alert-btn cancel" id="uaConfirmCancelBtn" onclick="UAAlert._closeConfirm()">Cancel</button>
            <button class="ua-alert-btn danger" id="uaConfirmOkBtn" onclick="UAAlert._runConfirm()">Yes, Delete</button>
        </div>
    </div>
</div>

{{-- INFO ALERT MODAL (success / error) --}}
<div class="ua-alert-modal-overlay" id="uaInfoOverlay">
    <div class="ua-alert-box">
        <div class="ua-alert-ring" id="uaInfoRing"></div>
        <p class="ua-alert-title" id="uaInfoTitle">Success!</p>
        <p class="ua-alert-text" id="uaInfoMsg"></p>
        <div class="ua-alert-actions">
            <button class="ua-alert-btn primary" onclick="UAAlert._closeInfo()">OK</button>
        </div>
    </div>
</div>

@once
<script>
/**
 * UAAlert — global reusable alert API.
 * Panggil dari modul manapun tanpa perlu include ulang komponen ini.
 */
window.UAAlert = (function () {
    let _onConfirmCallback = null;

    function confirm(opts) {
        opts = opts || {};
        document.getElementById('uaConfirmTitle').textContent   = opts.title || 'Are you sure?';
        document.getElementById('uaConfirmText').textContent    = opts.text  || 'This action cannot be undone.';
        document.getElementById('uaConfirmOkBtn').textContent   = opts.confirmText || 'Yes, Delete';
        document.getElementById('uaConfirmCancelBtn').textContent = opts.cancelText || 'Cancel';
        _onConfirmCallback = typeof opts.onConfirm === 'function' ? opts.onConfirm : null;
        document.getElementById('uaConfirmOverlay').classList.add('show');
    }

    function _closeConfirm() {
        document.getElementById('uaConfirmOverlay').classList.remove('show');
        _onConfirmCallback = null;
    }

    function _runConfirm() {
        const cb = _onConfirmCallback;
        document.getElementById('uaConfirmOverlay').classList.remove('show');
        _onConfirmCallback = null;
        if (cb) cb();
    }

    function _setRing(type) {
        const ring = document.getElementById('uaInfoRing');
        if (type === 'success') {
            ring.className = 'ua-alert-ring success';
            ring.innerHTML = '<svg class="ua-check-svg" viewBox="0 0 52 52">' +
                '<circle class="ua-check-circle" cx="26" cy="26" r="23"/>' +
                '<path class="ua-check-mark" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>' +
                '</svg>';
        } else {
            ring.className = 'ua-alert-ring error';
            ring.innerHTML = '<svg class="ua-x-svg" viewBox="0 0 52 52">' +
                '<circle class="ua-x-circle" cx="26" cy="26" r="23"/>' +
                '<path class="ua-x-mark" d="M17 17l18 18"/>' +
                '<path class="ua-x-mark" d="M35 17l-18 18"/>' +
                '</svg>';
        }
    }

    function success(message, title) {
        _setRing('success');
        document.getElementById('uaInfoTitle').textContent = title || 'Success!';
        document.getElementById('uaInfoMsg').textContent   = message || '';
        document.getElementById('uaInfoOverlay').classList.add('show');
    }

    function error(message, title) {
        _setRing('error');
        document.getElementById('uaInfoTitle').textContent = title || 'Error!';
        document.getElementById('uaInfoMsg').textContent   = message || '';
        document.getElementById('uaInfoOverlay').classList.add('show');
    }

    function _closeInfo() {
        document.getElementById('uaInfoOverlay').classList.remove('show');
    }

    // Tutup modal kalau klik di luar box
    document.addEventListener('click', function (e) {
        if (e.target.id === 'uaConfirmOverlay') _closeConfirm();
        if (e.target.id === 'uaInfoOverlay') _closeInfo();
    });

    return { confirm, success, error, _closeConfirm, _runConfirm, _closeInfo };
})();
</script>
@endonce