@extends('layouts.app')

@section('title', 'Manajemen Peternak')

@section('content')

<x-table
    id="peternakTable"
    :columns="[
        ['key' => 'nama_peternak', 'label' => 'Nama Peternak', 'sortable' => true, 'align' => 'left'],
        ['key' => 'alamat',        'label' => 'Alamat',        'sortable' => false, 'align' => 'left'],
        ['key' => 'no_telp',       'label' => 'No. Telepon',   'sortable' => false, 'align' => 'left', 'width' => '160px'],
    ]"
    add-label="Tambah Peternak"
    add-onclick="openCreateModal()"
    search-placeholder="Cari nama peternak..."
    clear-onclick="clearPeternakFilter()"
>
    <x-slot:filters>
        <div>
            <label class="ua-form-label">Nama Peternak</label>
            <input type="text" class="ua-form-input" id="filterNamaPeternak" placeholder="Nama peternak...">
        </div>
        <div>
            <label class="ua-form-label">No. Telepon</label>
            <input type="text" class="ua-form-input" id="filterTelpPeternak" placeholder="Nomor telepon...">
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

{{-- ================= MODAL DETAIL PETERNAK (read-only, muncul saat klik baris) ================= --}}
<div id="modalDetailPeternak" style="display:none;position:fixed;inset:0;background:rgba(15,17,26,0.5);z-index:200;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg);border-radius:12px;width:480px;max-width:92vw;max-height:88vh;overflow-y:auto;">
        <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div id="detailNama" style="font-size:15px;font-weight:600;">Nama Peternak</div>
                <div id="detailTelp" style="font-size:12px;color:var(--text-secondary);margin-top:2px;">No. Telepon</div>
            </div>
            <i class="ti ti-x" style="cursor:pointer;color:var(--text-secondary);" onclick="closeDetailModal()"></i>
        </div>

        <div style="padding:24px;">
            <div>
                <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-secondary);margin-bottom:6px;">Alamat</div>
                <div id="detailAlamat" style="font-size:13.5px;line-height:1.7;white-space:pre-line;">-</div>
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
<div id="modalPeternak" style="display:none;position:fixed;inset:0;background:rgba(15,17,26,0.5);z-index:200;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg);border-radius:12px;width:460px;max-width:92vw;max-height:88vh;overflow-y:auto;">
        <form id="formPeternak" method="POST">
            @csrf
            <div id="methodField"></div>

            <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                <div id="modalTitle" style="font-size:15px;font-weight:600;">Tambah Peternak</div>
                <i class="ti ti-x" style="cursor:pointer;color:var(--text-secondary);" onclick="closeModal()"></i>
            </div>

            <div style="padding:24px;">
                <div class="form-group">
                    <label class="form-label">Nama Peternak</label>
                    <input type="text" name="nama_peternak" id="input_nama" class="form-control" placeholder="Contoh: Pak Slamet" required maxlength="100">
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" id="input_alamat" class="form-control" rows="3" placeholder="Alamat lengkap peternak" required maxlength="255"></textarea>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="no_telp" id="input_telp" class="form-control" placeholder="Contoh: 081234567890" required maxlength="20">
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
    #modalPeternak .btn-primary,
    #modalPeternak .btn-secondary,
    #modalDetailPeternak .btn-primary,
    #modalDetailPeternak .btn-secondary {
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

    #modalPeternak .btn-primary i,
    #modalPeternak .btn-secondary i,
    #modalDetailPeternak .btn-primary i,
    #modalDetailPeternak .btn-secondary i {
        font-size: 16px;
        line-height: 1;
    }

    #modalPeternak .btn-primary,
    #modalPeternak .btn-primary:focus,
    #modalPeternak .btn-primary:active,
    #modalDetailPeternak .btn-primary,
    #modalDetailPeternak .btn-primary:focus,
    #modalDetailPeternak .btn-primary:active {
        background: #3b82f6; border-color: #3b82f6; color: #fff;
    }
    #modalPeternak .btn-primary:hover,
    #modalDetailPeternak .btn-primary:hover {
        background: #2563eb; border-color: #2563eb;
    }
    #modalPeternak .btn-secondary,
    #modalPeternak .btn-secondary:focus,
    #modalPeternak .btn-secondary:active,
    #modalDetailPeternak .btn-secondary,
    #modalDetailPeternak .btn-secondary:focus,
    #modalDetailPeternak .btn-secondary:active {
        background: #fff; border: 1px solid #e2e8f0; color: #4a5568;
    }
    #modalPeternak .btn-secondary:hover,
    #modalDetailPeternak .btn-secondary:hover { background: #f7fafc; color: #ef4444; }

    #modalPeternak .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.14);
    }

    #peternakTable tbody tr td.ua-clickable-cell {
        cursor: pointer;
    }
    #peternakTable tbody tr:hover td.ua-clickable-cell {
        background: rgba(59,130,246,0.05);
    }
</style>
@endpush

@push('scripts')
<script>
    const modal        = document.getElementById('modalPeternak');
    const form          = document.getElementById('formPeternak');
    const methodField   = document.getElementById('methodField');
    const detailModal   = document.getElementById('modalDetailPeternak');

    let selectedDetailId = null;

    function uaLimit(text, len) {
        text = text ?? '';
        return text.length > len ? text.substring(0, len).trim() + '...' : text;
    }

    // ================= INIT TABLE =================
    const peternakTable = UATable.init({
        tableId: 'peternakTable',
        data: {!! json_encode($peternak) !!},
        perPage: 10,
        emptyColspan: 4,
        emptyMessage: 'Belum ada data peternak.',
        defaultSort: (a, b) => a.nama_peternak.localeCompare(b.nama_peternak),

        renderRow: function (d, index) {
            const safeId = String(d.id);
            return '<tr>' +
                '<td class="ua-clickable-cell" onclick="openDetailModalById(\'' + safeId + '\')">' + (index + 1) + '</td>' +
                '<td class="ua-clickable-cell" style="text-align:left;font-weight:600;" onclick="openDetailModalById(\'' + safeId + '\')">' + d.nama_peternak + '</td>' +
                '<td class="ua-clickable-cell" style="text-align:left;color:#6b7280;max-width:280px;" onclick="openDetailModalById(\'' + safeId + '\')">' + uaLimit(d.alamat, 80) + '</td>' +
                '<td class="ua-clickable-cell" style="text-align:left;" onclick="openDetailModalById(\'' + safeId + '\')">' + d.no_telp + '</td>' +
                '<td><div class="ua-action-group">' +
                    '<button class="ua-action-btn edit" title="Edit" onclick="event.stopPropagation();openEditModalById(\'' + safeId + '\')"><i class="ti ti-pencil"></i></button>' +
                    '<button class="ua-action-btn delete" title="Hapus" onclick="event.stopPropagation();confirmDeletePeternak(\'' + safeId + '\', \'' + String(d.nama_peternak).replace(/'/g, "\\'") + '\')"><i class="ti ti-trash"></i></button>' +
                '</div></td>' +
            '</tr>';
        },

        getFilters: function () {
            return {
                search: document.getElementById('peternakTableSearch').value.toLowerCase(),
                nama:   document.getElementById('filterNamaPeternak').value.toLowerCase(),
                telp:   document.getElementById('filterTelpPeternak').value.toLowerCase(),
            };
        },

        filterFn: function (d, f) {
            return (d.nama_peternak.toLowerCase().includes(f.search) || d.no_telp.toLowerCase().includes(f.search)) &&
                d.nama_peternak.toLowerCase().includes(f.nama) &&
                d.no_telp.toLowerCase().includes(f.telp);
        },
    });

    function clearPeternakFilter() {
        document.getElementById('peternakTableSearch').value = '';
        document.getElementById('filterNamaPeternak').value = '';
        document.getElementById('filterTelpPeternak').value = '';
        peternakTable.filter();
    }

    // ================= MODAL DETAIL (klik baris) =================
    function openDetailModalById(id) {
        const item = peternakTable.getData().find(x => String(x.id) === String(id));
        if (!item) return;

        selectedDetailId = item.id;

        document.getElementById('detailNama').innerText = item.nama_peternak;
        document.getElementById('detailTelp').innerText = item.no_telp;
        document.getElementById('detailAlamat').innerText = item.alamat && item.alamat.length ? item.alamat : '-';

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
        document.getElementById('modalTitle').innerText = 'Tambah Peternak';
        form.action = "{{ route('peternak.store') }}";
        methodField.innerHTML = '';
        form.reset();
        modal.style.display = 'flex';
    }

    function openEditModalById(id) {
        const item = peternakTable.getData().find(x => String(x.id) === String(id));
        if (!item) return;
        document.getElementById('modalTitle').innerText = 'Edit Peternak';
        form.action = "{{ url('peternak') }}/" + item.id;
        methodField.innerHTML = '@method("PUT")';
        document.getElementById('input_nama').value = item.nama_peternak;
        document.getElementById('input_alamat').value = item.alamat;
        document.getElementById('input_telp').value = item.no_telp;
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    // ================= DELETE via UAAlert + form tersembunyi =================
    function confirmDeletePeternak(id, nama) {
        UAAlert.confirm({
            title: 'Hapus Peternak?',
            text: 'Yakin ingin menghapus "' + nama + '"? Data ini tidak bisa dikembalikan.',
            confirmText: 'Yes, Delete',
            cancelText: 'Cancel',
            onConfirm: function () {
                const deleteForm = document.getElementById('deleteForm');
                deleteForm.action = "{{ url('peternak') }}/" + id;
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