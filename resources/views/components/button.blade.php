{{--
    ============================================================
    <x-button /> — Reusable Action Button
    ============================================================
    Taruh di: resources/views/components/button.blade.php

    Ada 2 "mode" pemakaian:

    1) ICON-ONLY (buat aksi di baris tabel — edit/delete)

        <x-button type="edit" title="Edit Data"
            onclick="openModal('edit', '{{ $row->id }}')" />

        <x-button type="delete" title="Hapus Akses"
            onclick="openDelete('{{ $row->id }}')" />

        Custom icon:
        <x-button type="custom" icon="ti-eye" title="Lihat Detail"
            onclick="viewDetail('{{ $row->id }}')" />

    2) BERLABEL (icon + teks — buat tombol Simpan/Batal di form/modal)
       Tinggal isi prop `label`, otomatis pindah ke style tombol
       biasa (bukan icon bulat kecil).

        <x-button type="save" label="Simpan" />
        <x-button type="save" label="Simpan" onclick="doSubmit()" />

        <x-button type="cancel" label="Batal" onclick="closeModal()" />

        Custom label + icon:
        <x-button type="custom" icon="ti-download" label="Export"
            onclick="exportData()" />

    Kalau butuh versi HTML string (dipakai di dalam renderRow JS,
    bukan Blade), tinggal contek markup <button> di bawah.

    NOTE: style di-load sekali lewat @once, aman dipanggil
    berkali-kali / dari banyak modul dalam 1 request.
    ============================================================
--}}

@props([
    'type'    => 'edit',   // edit | delete | save | cancel | custom
    'icon'    => null,     // override icon class, misal 'ti-eye'
    'label'   => null,     // kalau diisi -> render sebagai tombol berlabel
    'title'   => null,
    'onclick' => null,
    'submit'  => false,    // true -> render type="submit" (dipakai bareng label="Simpan")
])

@php
    $iconMap = [
        'edit'   => 'ti-pencil',
        'delete' => 'ti-trash',
        'save'   => 'ti-device-floppy',
        'cancel' => 'ti-x',
    ];
    $resolvedIcon  = $icon ?? ($iconMap[$type] ?? 'ti-dots');
    $resolvedTitle = $title ?? ucfirst($type);
    $isLabeled     = filled($label);

    // type 'save' dianggap tombol submit otomatis kecuali sengaja di-override
    $isSubmit = $submit || $type === 'save';
@endphp

@once
    @push('styles')
    <style>
        /* ---------- Mode icon-only (aksi baris tabel) ---------- */
        .ua-action-group {
            display: inline-flex; align-items: center; justify-content: center; gap: 12px;
        }
        .ua-action-btn {
            display: inline-flex; align-items: center; justify-content: center;
            background: transparent; border: none; padding: 0;
            cursor: pointer; font-size: 21px; color: #a0aec0;
            transition: color 0.15s ease;
        }
        .ua-action-btn:focus, .ua-action-btn:active { outline: none; box-shadow: none; }
        .ua-action-btn.edit:hover   { color: #3b82f6; }
        .ua-action-btn.delete:hover { color: #ef4444; }
        .ua-action-btn.custom:hover { color: #6b7280; }

        /* ---------- Mode berlabel (form / modal) ---------- */
        .ua-btn {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13.5px; font-weight: 500; line-height: 1;
            padding: 9px 16px; border-radius: 8px;
            cursor: pointer; border: 1px solid transparent;
            transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }
        .ua-btn i { font-size: 16px; line-height: 1; }
        .ua-btn:focus, .ua-btn:active { outline: none; box-shadow: none; }

        /* biru konsisten sama warna hover .ua-action-btn.edit di atas */
        .ua-btn.save {
            background: #3b82f6; border-color: #3b82f6; color: #fff;
        }
        .ua-btn.save:hover { background: #2563eb; border-color: #2563eb; }

        .ua-btn.cancel {
            background: #fff; border-color: #e2e8f0; color: #4a5568;
        }
        .ua-btn.cancel:hover { background: #f7fafc; color: #ef4444; border-color: #e2e8f0; }

        .ua-btn.custom {
            background: #fff; border-color: #e2e8f0; color: #4a5568;
        }
        .ua-btn.custom:hover { background: #f7fafc; color: #3b82f6; }
    </style>
    @endpush
@endonce

@if ($isLabeled)
    <button
        type="{{ $isSubmit ? 'submit' : 'button' }}"
        class="ua-btn {{ $type }}"
        title="{{ $resolvedTitle }}"
        @if($onclick) onclick="{{ $onclick }}" @endif
        {{ $attributes }}
    >
        <i class="ti {{ $resolvedIcon }}"></i>
        <span>{{ $label }}</span>
    </button>
@else
    <button
        type="button"
        class="ua-action-btn {{ $type }}"
        title="{{ $resolvedTitle }}"
        @if($onclick) onclick="{{ $onclick }}" @endif
        {{ $attributes }}
    >
        <i class="ti {{ $resolvedIcon }}"></i>
    </button>
@endif