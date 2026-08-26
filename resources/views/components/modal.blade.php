{{--
    ============================================================
    <x-modal /> — Reusable Bootstrap 5 Modal (variant="modern")
    ============================================================
    Taruh di: resources/views/components/modal.blade.php

    Ini BUKAN pengganti <x-alert /> (yang custom, tanpa Bootstrap).
    <x-modal /> ini khusus buat modal yang pakai Bootstrap Modal API
    asli (bootstrap.Modal, data-bs-dismiss, show.bs.modal, dst) —
    dipakai buat form Add/Edit atau dialog konfirmasi Delete.

    PROPS
    ------------------------------------------------------------
    id       (wajib) — id modal, dipakai bootstrap.Modal & JS kamu
    title    (wajib) — teks judul di header
    size     (opsional) — sm | md (default) | lg | xl
    icon     (opsional) — class icon di depan title, misal 'fas fa-clock'
    variant  (opsional) — 'modern' (default). Reserved buat varian lain nanti.

    CATATAN:
    Tombol close (X) di pojok kanan atas header SUDAH DIHILANGKAN.
    Modal cuma bisa ditutup lewat tombol di footer (mis. "Batal" yang
    punya data-bs-dismiss="modal") — jadi WAJIB selalu sediakan tombol
    dismiss di footer, jangan andalkan X lagi.

    SLOT
    ------------------------------------------------------------
    default slot -> isi <div class="modal-body">
    x-slot:footer / <x-slot name="footer"> -> isi <div class="modal-footer">
        (kalau gak diisi, footer gak dirender sama sekali)

    ============================================================
    CONTOH 1 — ADD / EDIT (persis pola addOvertimeRequestModal)
    ------------------------------------------------------------

        <x-modal id="addOvertimeRequestModal" title="Add New Overtime Request" size="lg">
            <form id="addOvertimeRequestForm" method="POST" action="{{ route('overtime.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Overtime Date</label>
                        <input type="date" class="form-control" name="date_overtime">
                    </div>
                    (field lainnya di bawah ini, dipotong biar ringkas)
                </div>
            </form>

            <x-slot name="footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addOvertimeSubmitBtn">
                    <i class="fas fa-paper-plane me-2"></i>Submit Request
                </button>
            </x-slot>
        </x-modal>

    Buka modalnya sama persis kayak Bootstrap biasa:
        new bootstrap.Modal(document.getElementById('addOvertimeRequestModal')).show();
    atau lewat tombol:
        <button data-bs-toggle="modal" data-bs-target="#addOvertimeRequestModal">Add</button>

    ============================================================
    CONTOH 2 — EDIT (isi form di-fill lewat JS sebelum show)
    ------------------------------------------------------------

        <x-modal id="editGejalaModal" title="Edit Gejala">
            <form id="editGejalaForm" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label required">Kode Gejala</label>
                    <input type="text" class="form-control" id="edit_kode" name="code_gejala">
                </div>
                <div class="mb-3">
                    <label class="form-label required">Nama Gejala</label>
                    <textarea class="form-control" id="edit_nama" name="nm_gejala" rows="2"></textarea>
                </div>
            </form>

            <x-slot name="footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="editGejalaSubmitBtn">
                    <i class="fas fa-save me-2"></i>Simpan
                </button>
            </x-slot>
        </x-modal>

        <script>
        function openEditModal(item) {
            document.getElementById('edit_kode').value = item.code_gejala;
            document.getElementById('edit_nama').value = item.nm_gejala;
            new bootstrap.Modal(document.getElementById('editGejalaModal')).show();
        }
        </script>

    ============================================================
    CONTOH 3 — DELETE CONFIRM (pakai <x-modal> juga, footer merah)
    ------------------------------------------------------------

        <x-modal id="deleteConfirmModal" title="Hapus Data?" size="sm" icon="fas fa-triangle-exclamation">
            <p class="mb-0" id="deleteConfirmText">Yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>

            <x-slot name="footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="deleteConfirmBtn">
                    <i class="fas fa-trash me-2"></i>Ya, Hapus
                </button>
            </x-slot>
        </x-modal>

        <script>
        let deleteTargetId = null;

        function confirmDeleteGejala(id, nama) {
            deleteTargetId = id;
            document.getElementById('deleteConfirmText').textContent =
                'Yakin ingin menghapus "' + nama + '"? Tindakan ini tidak dapat dibatalkan.';
            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
        }

        document.getElementById('deleteConfirmBtn').addEventListener('click', function () {
            document.getElementById('deleteForm-' + deleteTargetId).submit();
        });
        </script>

    (Kalau modul kamu masih pakai UAAlert.confirm dari <x-alert />, itu tetap
    valid dan lebih ringkas buat delete — <x-modal> versi delete ini cuma
    dipakai kalau kamu memang mau delete-confirm ikut gaya Bootstrap modal
    yang sama kayak Add/Edit-nya, bukan gaya ring/checkmark UAAlert.)
    ============================================================
--}}

@props([
    'id'      => 'modal-' . uniqid(),
    'title'   => '',
    'size'    => 'md',
    'icon'    => null,
    'variant' => 'modern',
])

@php
    $sizeClass = match($size) {
        'sm' => 'modal-sm',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
        default => '',
    };
@endphp

@once
@push('styles')
<style>
    .ua-modal .modal-content {
        border: none;
        border-radius: 14px;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .ua-modal .modal-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border, #eef0f2);
        align-items: center;
    }
    .ua-modal .modal-title {
        font-size: 16.5px;
        font-weight: 700;
        color: var(--text-primary, #1f2937);
        display: flex;
        align-items: center;
    }
    .ua-modal .modal-title i { color: #f59e0b; font-size: 16px; }
    .ua-modal .modal-body { padding: 24px; }
    .ua-modal .modal-footer {
        padding: 14px 24px;
        border-top: 1px solid var(--border, #eef0f2);
        background: var(--card-bg-soft, #f9fafb);
        gap: 8px;
    }
    .ua-modal .form-label.required::after { content: ' *'; color: #dc3545; }
    .ua-modal .is-invalid { border-color: #dc3545 !important; }
    .ua-modal .field-error { font-size: 0.85rem; color: #dc3545; }
    .ua-modal .form-control:focus,
    .ua-modal .form-select:focus {
        border-color: #3699ff; box-shadow: 0 0 0 0.2rem rgba(54,153,255,0.25);
    }
</style>
@endpush
@endonce

<div class="modal fade ua-modal" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true" {{ $attributes }}>
    <div class="modal-dialog modal-dialog-centered {{ $sizeClass }}">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">
                    @if($icon)<i class="{{ $icon }} me-2"></i>@endif{{ $title }}
                </h5>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @isset($footer)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>