@extends('layouts.app')

@section('title', 'Atur Relasi - ' . $penyakit->nm_penyakit)

@section('content')

{{-- trik: biar CSS .ua-data-table / .ua-search-combo / .ua-status-badge ikut ke-push
     meski halaman ini gak pakai <x-table> beneran (form checklist gak cocok
     pakai UATable — lihat penjelasan di chat) --}}
@once
    <div style="display:none"><x-table id="__uaStyleLoader" :columns="[]" /></div>
@endonce

<div class="card" id="relasiFormCard">
    <div class="card-header">
        <div>
            <div class="card-title">{{ $penyakit->nm_penyakit }}</div>
            <div style="font-size:12.5px;color:var(--text-secondary);margin-top:4px;">
                Kode: {{ $penyakit->code_penyakit }}
            </div>
        </div>
    </div>

    <p style="font-size:13px;color:var(--text-secondary);margin-bottom:14px;">
        Centang gejala yang berkaitan dengan penyakit ini, lalu pilih tingkat keyakinan (CF pakar) untuk tiap gejala.
        Gejala yang tidak dicentang tidak akan dianggap sebagai indikasi penyakit ini.
    </p>

    {{-- search ringan, CSS-only (display:none), TIDAK menghapus row dari DOM
         jadi checkbox & CF yang sudah diisi tetap ikut ke-submit walau lagi difilter --}}
    <div class="ua-search-combo" style="max-width:360px;margin-bottom:16px;">
        <span class="ua-search-icon"><i class="ti ti-search"></i></span>
        <input type="text" id="relasiSearch" placeholder="Cari kode atau nama gejala..." oninput="filterRelasiRows()">
    </div>

    <form id="formRelasi" action="{{ route('pengetahuan.relasi.update', $penyakit->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="ua-table-wrap" style="margin:0;border-radius:8px;border:1px solid #d1d5db;">
            <table class="ua-data-table">
                <thead>
                    <tr>
                        <th data-align="left" style="width:80px;">Kode</th>
                        <th data-align="left">Nama Gejala</th>
                        <th style="width:130px;">Status</th>
                        <th style="width:180px;">Nilai CF Pakar</th>
                    </tr>
                </thead>
                <tbody id="relasiTableBody">
                    @foreach ($gejala as $item)
                        <tr id="row-{{ $item->id }}" data-search="{{ strtolower($item->code_gejala . ' ' . $item->nm_gejala) }}">
                            <td style="text-align:left;font-weight:600;">{{ $item->code_gejala }}</td>
                            <td style="text-align:left;">{{ $item->nm_gejala }}</td>
                            <td>
                                {{-- checkbox asli disembunyikan, tetap ikut ke-submit form --}}
                                <input
                                    type="checkbox"
                                    class="relasi-checkbox-hidden"
                                    name="checked_gejala[]"
                                    value="{{ $item->id }}"
                                    id="chk-{{ $item->id }}"
                                    {{ $item->checked ? 'checked' : '' }}
                                >
                                {{-- badge ini sekaligus jadi tombol aksi (klik utk toggle) --}}
                                <span
                                    class="relasi-toggle-badge {{ $item->checked ? 'active' : 'inactive' }}"
                                    id="toggleBadge-{{ $item->id }}"
                                    onclick="toggleGejala('{{ $item->id }}')"
                                    role="button" tabindex="0"
                                >
                                    <i class="ti {{ $item->checked ? 'ti-circle-check-filled' : 'ti-circle' }}"></i>
                                    <span class="relasi-toggle-badge-text">{{ $item->checked ? 'Aktif' : 'Pilih' }}</span>
                                </span>
                            </td>
                            <td>
                                {{-- select asli tetap ada & tetap yang di-submit ke server,
                                     tapi disembunyikan lewat JS lalu diganti tampilan custom
                                     dropdown biru di bawah (lihat initCfDropdowns()) --}}
                                <select
                                    name="cf[{{ $item->id }}]"
                                    id="cf-{{ $item->id }}"
                                    class="form-control cf-select"
                                    {{ $item->checked ? '' : 'disabled' }}
                                    style="padding:6px 10px;"
                                >
                                    <option value="0"   {{ (float) $item->nilai_cf_pakar === 0.0 ? 'selected' : '' }}>Tidak</option>
                                    <option value="0.2" {{ (float) $item->nilai_cf_pakar === 0.2 ? 'selected' : '' }}>Tidak Tahu</option>
                                    <option value="0.4" {{ (float) $item->nilai_cf_pakar === 0.4 ? 'selected' : '' }}>Sedikit Yakin</option>
                                    <option value="0.6" {{ (float) $item->nilai_cf_pakar === 0.6 ? 'selected' : '' }}>Cukup Yakin</option>
                                    <option value="0.8" {{ (float) $item->nilai_cf_pakar === 0.8 ? 'selected' : '' }}>Yakin</option>
                                    <option value="1"   {{ (float) $item->nilai_cf_pakar === 1.0 ? 'selected' : '' }}>Sangat Yakin</option>
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div id="relasiEmptyState" style="display:none;text-align:center;padding:32px;color:#a0aec0;">
            <i class="ti ti-search-off" style="font-size:28px;display:block;margin-bottom:8px;opacity:0.4;"></i>
            Gejala tidak ditemukan
        </div>

        <div style="margin-top:20px;display:flex;justify-content:flex-end;gap:10px;">
            <a href="{{ route('pengetahuan.relasi.index') }}" class="btn btn-secondary">
                <span>Batal</span>
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i>
                <span>Simpan Relasi</span>
            </button>
        </div>
    </form>
</div>

@endsection

@push('styles')
<style>
    /* dipaksa biru konsisten sama warna .ua-action-btn.edit:hover (#3b82f6) di button.blade.php,
       jaga-jaga kalau .btn-primary global di project beda warna (di layouts/app.blade.php dia hijau) */
    #relasiFormCard .btn-primary,
    #relasiFormCard .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13.5px;
        line-height: 1;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        appearance: none; -webkit-appearance: none;
    }

    #relasiFormCard .btn-primary i,
    #relasiFormCard .btn-secondary i {
        font-size: 16px;
        line-height: 1;
    }

    #relasiFormCard .btn-primary,
    #relasiFormCard .btn-primary:focus,
    #relasiFormCard .btn-primary:active {
        background: #3b82f6; border-color: #3b82f6; color: #fff;
    }
    #relasiFormCard .btn-primary:hover {
        background: #2563eb; border-color: #2563eb;
    }
    #relasiFormCard .btn-secondary,
    #relasiFormCard .btn-secondary:focus,
    #relasiFormCard .btn-secondary:active {
        background: #fff; border: 1px solid #e2e8f0; color: #4a5568;
    }
    #relasiFormCard .btn-secondary:hover { background: #f7fafc; color: #ef4444; }

    /* .ua-table-wrap biasanya overflow-x:auto utk scroll horizontal di layar kecil —
       overflow-y dipaksa visible di sini supaya menu dropdown CF gak ketutup/kepotong */
    #relasiFormCard .ua-table-wrap {
        overflow-y: visible;
    }

    /* .form-control:focus global di layouts/app.blade.php pakai var(--content-accent)
       yang warnanya hijau — di-override khusus di halaman ini (select CF pakar) biar konsisten biru */
    #relasiFormCard .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.14);
    }

    /* select asli disembunyikan lewat JS (tetap ada di DOM utk value form) */
    #relasiFormCard select.cf-select {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 1px; height: 1px;
    }

    /* ===== custom dropdown CF (pengganti tampilan select native) ===== */
    .cf-dropdown {
        position: relative;
        width: 100%;
        min-width: 150px;
    }

    .cf-dropdown-toggle {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 7px 11px;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        font-size: 13px;
        font-weight: 500;
        color: #2d3748;
        cursor: pointer;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        appearance: none; -webkit-appearance: none;
    }
    .cf-dropdown-toggle:hover { border-color: #93c5fd; }
    .cf-dropdown.open .cf-dropdown-toggle {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.14);
    }
    .cf-dropdown-toggle:disabled {
        cursor: not-allowed;
        background: #f7fafc;
        color: #a0aec0;
        border-color: #e2e8f0;
    }
    .cf-dropdown-toggle i {
        font-size: 15px;
        color: #94a3b8;
        transition: transform 0.15s ease, color 0.15s ease;
        flex-shrink: 0;
    }
    .cf-dropdown.open .cf-dropdown-toggle i { transform: rotate(180deg); color: #3b82f6; }

    .cf-dropdown-menu {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        min-width: 170px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 10px 28px rgba(15,23,42,0.14);
        padding: 5px;
        z-index: 50;
        display: none;
    }
    .cf-dropdown.open .cf-dropdown-menu { display: block; }

    .cf-dropdown-option {
        padding: 8px 11px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        color: #4a5568;
        cursor: pointer;
        white-space: nowrap;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        transition: background 0.12s ease, color 0.12s ease;
    }
    .cf-dropdown-option:hover { background: #eff6ff; color: #2563eb; }
    .cf-dropdown-option.selected {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #fff;
        font-weight: 700;
    }
    .cf-dropdown-option.selected::after {
        content: '\ea5e'; /* tabler ti-check */
        font-family: 'tabler-icons' !important;
        font-size: 14px;
    }

    /* checkbox asli disembunyikan total — badge di bawah ini yang jadi tampilan & tombol aksinya */
    .relasi-checkbox-hidden { display: none; }

    /* badge klik-toggle: gabungan action + status indicator, gaya pill+icon kaya di role/status badge */
    .relasi-toggle-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 15px; border-radius: 999px;
        font-size: 12px; font-weight: 700; letter-spacing: 0.1px;
        border: 1.5px solid transparent; cursor: pointer; user-select: none;
        transition: transform 0.12s ease, box-shadow 0.12s ease, background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }
    .relasi-toggle-badge i { font-size: 15px; }
    .relasi-toggle-badge:active { transform: scale(0.96); }

    .relasi-toggle-badge.active {
        background: linear-gradient(135deg, #14b8a6, #0d9488);
        color: #fff; box-shadow: 0 2px 6px rgba(13,148,136,0.28);
    }
    .relasi-toggle-badge.active:hover { box-shadow: 0 3px 9px rgba(13,148,136,0.38); transform: translateY(-1px); }

    .relasi-toggle-badge.inactive {
        background: #f9fafb; border-color: #e2e8f0; color: #94a3b8;
    }
    .relasi-toggle-badge.inactive:hover {
        border-color: #3b82f6; color: #3b82f6; background: #eff6ff;
    }

    /* baris yang belum dicentang dibikin agak pudar biar fokus visual ke yang aktif */
    #relasiFormCard tr:has(.relasi-checkbox-hidden:not(:checked)) td {
        color: #a0aec0;
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleGejala(gejalaId) {
        const checkbox = document.getElementById('chk-' + gejalaId);
        const isChecked = !checkbox.checked;
        checkbox.checked = isChecked;

        const cfInput = document.getElementById('cf-' + gejalaId);
        cfInput.disabled = !isChecked;
        if (!isChecked) {
            cfInput.value = '0';
        }

        // sinkronkan dropdown custom (tampilan) dengan select asli (value form)
        syncCfDropdownFromSelect(cfInput);

        const badge = document.getElementById('toggleBadge-' + gejalaId);
        const icon = badge.querySelector('i');
        const text = badge.querySelector('.relasi-toggle-badge-text');

        badge.classList.toggle('active', isChecked);
        badge.classList.toggle('inactive', !isChecked);
        icon.className = 'ti ' + (isChecked ? 'ti-circle-check-filled' : 'ti-circle');
        text.textContent = isChecked ? 'Aktif' : 'Pilih';
    }

    // ============== Custom dropdown CF (pengganti tampilan <select> native) ==============
    // Select asli tetap yang di-submit ke server; dropdown ini cuma "wajah"-nya.

    function buildCfDropdown(select) {
        const wrap = document.createElement('div');
        wrap.className = 'cf-dropdown';
        wrap.id = 'cfDropdown-' + select.id;

        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'cf-dropdown-toggle';
        toggle.disabled = select.disabled;
        toggle.innerHTML = '<span class="cf-dropdown-label"></span><i class="ti ti-chevron-down"></i>';

        const menu = document.createElement('div');
        menu.className = 'cf-dropdown-menu';

        Array.from(select.options).forEach(function (opt) {
            const optionEl = document.createElement('div');
            optionEl.className = 'cf-dropdown-option' + (opt.selected ? ' selected' : '');
            optionEl.dataset.value = opt.value;
            optionEl.textContent = opt.text;

            optionEl.addEventListener('click', function () {
                select.value = opt.value;
                select.dispatchEvent(new Event('change'));
                menu.querySelectorAll('.cf-dropdown-option').forEach(function (o) {
                    o.classList.remove('selected');
                });
                optionEl.classList.add('selected');
                updateCfToggleLabel(select, toggle);
                closeAllCfDropdowns();
            });

            menu.appendChild(optionEl);
        });

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            if (toggle.disabled) return;
            const isOpen = wrap.classList.contains('open');
            closeAllCfDropdowns();
            if (!isOpen) wrap.classList.add('open');
        });

        updateCfToggleLabel(select, toggle);

        select.style.display = 'none';
        select.insertAdjacentElement('afterend', wrap);
        wrap.appendChild(toggle);
        wrap.appendChild(menu);
    }

    function updateCfToggleLabel(select, toggle) {
        const label = toggle.querySelector('.cf-dropdown-label');
        const selectedOption = select.options[select.selectedIndex];
        label.textContent = selectedOption ? selectedOption.text : '';
    }

    function closeAllCfDropdowns() {
        document.querySelectorAll('.cf-dropdown.open').forEach(function (d) {
            d.classList.remove('open');
        });
    }

    // dipanggil dari toggleGejala() supaya dropdown custom ikut update
    // pas gejala di-uncheck/checked (termasuk saat dipaksa reset ke "Tidak")
    function syncCfDropdownFromSelect(select) {
        const wrap = document.getElementById('cfDropdown-' + select.id);
        if (!wrap) return;

        const toggle = wrap.querySelector('.cf-dropdown-toggle');
        toggle.disabled = select.disabled;
        updateCfToggleLabel(select, toggle);

        wrap.querySelectorAll('.cf-dropdown-option').forEach(function (o) {
            o.classList.toggle('selected', o.dataset.value === select.value);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('select.cf-select').forEach(buildCfDropdown);
    });

    document.addEventListener('click', closeAllCfDropdowns);

    // biar badge bisa di-toggle pakai keyboard (Enter / Space), bukan cuma klik mouse
    document.querySelectorAll('.relasi-toggle-badge').forEach(function (badge) {
        badge.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                badge.click();
            }
        });
    });

    // search CSS-only: sembunyiin row yang gak match, TAPI tetap ada di DOM
    function filterRelasiRows() {
        const q = document.getElementById('relasiSearch').value.toLowerCase().trim();
        const rows = document.querySelectorAll('#relasiTableBody tr');
        let visibleCount = 0;

        rows.forEach(function (row) {
            const match = row.dataset.search.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        document.getElementById('relasiEmptyState').style.display = visibleCount === 0 ? 'block' : 'none';
    }

    // ============== UAAlert integration ==============

    // Validasi: minimal 1 gejala dicentang sebelum submit
    // (nilai CF tidak perlu divalidasi kosong lagi karena sekarang berupa <select>
    //  yang selalu punya value default "0")
    document.getElementById('formRelasi').addEventListener('submit', function (e) {
        const checkedBoxes = document.querySelectorAll('input[name="checked_gejala[]"]:checked');

        if (checkedBoxes.length === 0) {
            e.preventDefault();
            UAAlert.error('Pilih minimal satu gejala sebelum menyimpan relasi.');
            return;
        }
    });

    @if (session('success'))
        document.addEventListener('DOMContentLoaded', function () {
            UAAlert.success(@json(session('success')));
        });
    @endif

    @if (session('error'))
        document.addEventListener('DOMContentLoaded', function () {
            UAAlert.error(@json(session('error')));
        });
    @endif
</script>
@endpush