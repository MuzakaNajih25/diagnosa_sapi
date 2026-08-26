@extends('layouts.app')

@section('title', 'Manajemen Penyakit')

@section('content')

<x-table
    id="penyakitTable"
    :columns="[
        ['key' => 'code_penyakit', 'label' => 'Kode',          'sortable' => true, 'align' => 'left', 'width' => '90px'],
        ['key' => 'nm_penyakit',   'label' => 'Nama Penyakit', 'sortable' => true, 'align' => 'left'],
        ['key' => 'deskripsi',     'label' => 'Deskripsi',     'sortable' => false, 'align' => 'left'],
        ['key' => 'solusi',        'label' => 'Solusi',        'sortable' => false, 'align' => 'left'],
    ]"
    add-label="Tambah Penyakit"
    add-onclick="openCreateModal()"
    search-placeholder="Cari kode atau nama penyakit..."
    clear-onclick="clearPenyakitFilter()"
>
    <x-slot:filters>
        <div>
            <label class="ua-form-label">Kode Penyakit</label>
            <input type="text" class="ua-form-input" id="filterKodePenyakit" placeholder="Contoh: P04">
        </div>
        <div>
            <label class="ua-form-label">Nama Penyakit</label>
            <input type="text" class="ua-form-input" id="filterNamaPenyakit" placeholder="Nama penyakit...">
        </div>
    </x-slot:filters>
</x-table>

{{-- trik kecil: biar CSS .ua-action-btn ikut ke-push meski tombol edit/delete
     baru muncul lewat JS (renderRow), bukan ditulis langsung di Blade --}}
@once
    <div style="display:none"><x-button type="edit" /><x-button type="delete" /></div>
@endonce

{{-- form tersembunyi, dipakai ulang buat submit delete (method spoof DELETE) --}}
<form id="deleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

{{-- ================= MODAL DETAIL PENYAKIT (read-only, muncul saat klik baris) ================= --}}
<div id="modalDetailPenyakit" style="display:none;position:fixed;inset:0;background:rgba(15,17,26,0.5);z-index:200;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg);border-radius:12px;width:520px;max-width:92vw;max-height:88vh;overflow-y:auto;">
        <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div id="detailNama" style="font-size:15px;font-weight:600;">Nama Penyakit</div>
                <div id="detailKode" style="font-size:12px;color:var(--text-secondary);margin-top:2px;">Kode</div>
            </div>
            <i class="ti ti-x" style="cursor:pointer;color:var(--text-secondary);" onclick="closeDetailModal()"></i>
        </div>

        <div style="padding:24px;">
            <div style="margin-bottom:18px;">
                <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-secondary);margin-bottom:6px;">Deskripsi</div>
                <div id="detailDeskripsi" style="font-size:13.5px;line-height:1.7;white-space:pre-line;">-</div>
            </div>
            <div>
                <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-secondary);margin-bottom:6px;">Solusi / Saran Penanganan</div>
                <div id="detailSolusi" style="font-size:13.5px;line-height:1.7;white-space:pre-line;">-</div>
            </div>
        </div>

        <div style="padding:16px 24px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px;">
            <button type="button" class="btn btn-secondary" onclick="closeDetailModal()">
                <span>Tutup</span>
            </button>
            <button type="button" class="btn btn-primary" id="detailEditBtn" onclick="editFromDetail()">
                <i class="ti ti-pencil"></i>
                <span>Edit</span>
            </button>
        </div>
    </div>
</div>

{{-- ================= MODAL TAMBAH / EDIT (custom, bukan Bootstrap) ================= --}}
<div id="modalPenyakit" style="display:none;position:fixed;inset:0;background:rgba(15,17,26,0.5);z-index:200;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg);border-radius:12px;width:480px;max-width:92vw;max-height:88vh;overflow-y:auto;">
        <form id="formPenyakit" method="POST">
            @csrf
            <div id="methodField"></div>

            <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                <div id="modalTitle" style="font-size:15px;font-weight:600;">Tambah Penyakit</div>
                <i class="ti ti-x" style="cursor:pointer;color:var(--text-secondary);" onclick="closeModal()"></i>
            </div>

            <div style="padding:24px;">
                <div class="form-group">
                    <label class="form-label">Kode Penyakit</label>
                    <input type="text" name="code_penyakit" id="input_kode" class="form-control" placeholder="Contoh: P04" required maxlength="20">
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Penyakit</label>
                    <input type="text" name="nm_penyakit" id="input_nama" class="form-control" placeholder="Contoh: Chikungunya" required maxlength="150">
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" id="input_deskripsi" class="form-control" rows="3" placeholder="Penjelasan singkat mengenai penyakit ini"></textarea>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Solusi / Saran Penanganan</label>
                    <textarea name="solusi" id="input_solusi" class="form-control" rows="3" placeholder="Saran penanganan atau rujukan medis"></textarea>
                </div>
            </div>

            <div style="padding:16px 24px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">
                    <span>Batal</span>
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy"></i>
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* dipaksa biru konsisten sama warna .ua-action-btn.edit:hover (#3b82f6) di button.blade.php,
       jaga-jaga kalau .btn-primary global di project beda warna */
    #modalPenyakit .btn-primary,
    #modalPenyakit .btn-secondary,
    #modalDetailPenyakit .btn-primary,
    #modalDetailPenyakit .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13.5px;
        line-height: 1;
        padding: 9px 16px;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        appearance: none; -webkit-appearance: none;
    }

    #modalPenyakit .btn-primary i,
    #modalPenyakit .btn-secondary i,
    #modalDetailPenyakit .btn-primary i,
    #modalDetailPenyakit .btn-secondary i {
        font-size: 16px;
        line-height: 1;
    }

    #modalPenyakit .btn-primary,
    #modalPenyakit .btn-primary:focus,
    #modalPenyakit .btn-primary:active,
    #modalDetailPenyakit .btn-primary,
    #modalDetailPenyakit .btn-primary:focus,
    #modalDetailPenyakit .btn-primary:active {
        background: #3b82f6; border-color: #3b82f6; color: #fff;
    }
    #modalPenyakit .btn-primary:hover,
    #modalDetailPenyakit .btn-primary:hover {
        background: #2563eb; border-color: #2563eb;
    }
    #modalPenyakit .btn-secondary,
    #modalPenyakit .btn-secondary:focus,
    #modalPenyakit .btn-secondary:active,
    #modalDetailPenyakit .btn-secondary,
    #modalDetailPenyakit .btn-secondary:focus,
    #modalDetailPenyakit .btn-secondary:active {
        background: #fff; border: 1px solid #e2e8f0; color: #4a5568;
    }
    #modalPenyakit .btn-secondary:hover,
    #modalDetailPenyakit .btn-secondary:hover { background: #f7fafc; color: #ef4444; }

    /* .form-control:focus global di layouts/app.blade.php pakai var(--content-accent)
       yang warnanya hijau — di-override khusus di dalam modal ini biar konsisten biru */
    #modalPenyakit .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.14);
    }

    /* baris tabel penyakit terasa clickable, kecuali kolom action */
    #penyakitTable tbody tr td.ua-clickable-cell {
        cursor: pointer;
    }
    #penyakitTable tbody tr:hover td.ua-clickable-cell {
        background: rgba(59,130,246,0.05);
    }
</style>
@endpush

@push('scripts')
<script>
    const modal        = document.getElementById('modalPenyakit');
    const form          = document.getElementById('formPenyakit');
    const methodField   = document.getElementById('methodField');
    const detailModal   = document.getElementById('modalDetailPenyakit');

    let selectedDetailId = null;

    // helper: potong teks panjang kayak Str::limit(), dipakai buat kolom deskripsi/solusi
    function uaLimit(text, len) {
        text = text ?? '';
        return text.length > len ? text.substring(0, len).trim() + '...' : text;
    }

    // ================= INIT TABLE =================
    const penyakitTable = UATable.init({
        tableId: 'penyakitTable',
        data: {!! json_encode($penyakit) !!},
        perPage: 10,
        emptyColspan: 6,
        emptyMessage: 'Belum ada data penyakit.',
        defaultSort: (a, b) => a.code_penyakit.localeCompare(b.code_penyakit),

        renderRow: function (d, index) {
            const safeId = String(d.id);
            return '<tr>' +
                '<td class="ua-clickable-cell" onclick="openDetailModalById(\'' + safeId + '\')">' + (index + 1) + '</td>' +
                '<td class="ua-clickable-cell" style="text-align:left;font-weight:600;" onclick="openDetailModalById(\'' + safeId + '\')">' + d.code_penyakit + '</td>' +
                '<td class="ua-clickable-cell" style="text-align:left;" onclick="openDetailModalById(\'' + safeId + '\')">' + d.nm_penyakit + '</td>' +
                '<td class="ua-clickable-cell" style="text-align:left;color:#6b7280;max-width:280px;" onclick="openDetailModalById(\'' + safeId + '\')">' + uaLimit(d.deskripsi, 80) + '</td>' +
                '<td class="ua-clickable-cell" style="text-align:left;color:#6b7280;max-width:280px;" onclick="openDetailModalById(\'' + safeId + '\')">' + uaLimit(d.solusi, 80) + '</td>' +
                '<td><div class="ua-action-group">' +
                    '<button class="ua-action-btn edit" title="Edit" onclick="event.stopPropagation();openEditModalById(\'' + safeId + '\')"><i class="ti ti-pencil"></i></button>' +
                    '<button class="ua-action-btn delete" title="Hapus" onclick="event.stopPropagation();confirmDeletePenyakit(\'' + safeId + '\', \'' + String(d.nm_penyakit).replace(/'/g, "\\'") + '\')"><i class="ti ti-trash"></i></button>' +
                '</div></td>' +
            '</tr>';
        },

        getFilters: function () {
            return {
                search: document.getElementById('penyakitTableSearch').value.toLowerCase(),
                kode:   document.getElementById('filterKodePenyakit').value.toLowerCase(),
                nama:   document.getElementById('filterNamaPenyakit').value.toLowerCase(),
            };
        },

        filterFn: function (d, f) {
            return (d.code_penyakit.toLowerCase().includes(f.search) || d.nm_penyakit.toLowerCase().includes(f.search)) &&
                d.code_penyakit.toLowerCase().includes(f.kode) &&
                d.nm_penyakit.toLowerCase().includes(f.nama);
        },
    });

    function clearPenyakitFilter() {
        document.getElementById('penyakitTableSearch').value = '';
        document.getElementById('filterKodePenyakit').value = '';
        document.getElementById('filterNamaPenyakit').value = '';
        penyakitTable.filter();
    }

    // ================= MODAL DETAIL (klik baris) =================
    function openDetailModalById(id) {
        const item = penyakitTable.getData().find(x => String(x.id) === String(id));
        if (!item) return;

        selectedDetailId = item.id;

        document.getElementById('detailNama').innerText = item.nm_penyakit;
        document.getElementById('detailKode').innerText = item.code_penyakit;
        document.getElementById('detailDeskripsi').innerText = item.deskripsi && item.deskripsi.length ? item.deskripsi : '-';
        document.getElementById('detailSolusi').innerText = item.solusi && item.solusi.length ? item.solusi : '-';

        detailModal.style.display = 'flex';
    }

    function closeDetailModal() {
        detailModal.style.display = 'none';
        selectedDetailId = null;
    }

    function editFromDetail() {
        if (selectedDetailId === null) return;
        const id = selectedDetailId;
        closeDetailModal();
        openEditModalById(id);
    }

    detailModal.addEventListener('click', function (e) {
        if (e.target === detailModal) closeDetailModal();
    });

    // ================= MODAL TAMBAH/EDIT =================
    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Tambah Penyakit';
        form.action = "{{ route('pengetahuan.penyakit.store') }}";
        methodField.innerHTML = '';
        form.reset();
        modal.style.display = 'flex';
    }

    function openEditModalById(id) {
        const item = penyakitTable.getData().find(x => String(x.id) === String(id));
        if (!item) return;
        document.getElementById('modalTitle').innerText = 'Edit Penyakit';
        form.action = "{{ url('pengetahuan/penyakit') }}/" + item.id;
        methodField.innerHTML = '@method("PUT")';
        document.getElementById('input_kode').value = item.code_penyakit;
        document.getElementById('input_nama').value = item.nm_penyakit;
        document.getElementById('input_deskripsi').value = item.deskripsi ?? '';
        document.getElementById('input_solusi').value = item.solusi ?? '';
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    // ================= DELETE via UAAlert + form tersembunyi =================
    function confirmDeletePenyakit(id, nama) {
        UAAlert.confirm({
            title: 'Hapus Penyakit?',
            text: 'Yakin ingin menghapus "' + nama + '"? Data ini tidak bisa dikembalikan.',
            confirmText: 'Yes, Delete',
            cancelText: 'Cancel',
            onConfirm: function () {
                const deleteForm = document.getElementById('deleteForm');
                deleteForm.action = "{{ url('pengetahuan/penyakit') }}/" + id;
                deleteForm.submit();
            }
        });
    }

    // ================= SUCCESS/ERROR ALERT (session flash) =================
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