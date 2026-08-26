@extends('layouts.app')

@section('title', 'Manajemen Sapi')

@section('content')

<x-table
    id="sapiTable"
    :columns="[
        ['key' => 'nm_sapi',        'label' => 'Nama Sapi',       'sortable' => true, 'align' => 'left'],
        ['key' => 'jenis_kelamin',  'label' => 'Jenis Kelamin',   'sortable' => true, 'align' => 'left', 'width' => '130px'],
        ['key' => 'warna',          'label' => 'Warna',           'sortable' => false, 'align' => 'left'],
        ['key' => 'umur',           'label' => 'Umur',            'sortable' => false, 'align' => 'left', 'width' => '100px'],
        ['key' => 'ciri_ciri',      'label' => 'Ciri-Ciri',       'sortable' => false, 'align' => 'left'],
        ['key' => 'nama_peternak',  'label' => 'Peternak',        'sortable' => true, 'align' => 'left'],
    ]"
    add-label="Tambah Sapi"
    add-onclick="openCreateModal()"
    search-placeholder="Cari nama sapi..."
    clear-onclick="clearSapiFilter()"
>
    <x-slot:filters>
        <div>
            <label class="ua-form-label">Nama Sapi</label>
            <input type="text" class="ua-form-input" id="filterNamaSapi" placeholder="Nama sapi...">
        </div>
        <div>
            <label class="ua-form-label">Jenis Kelamin</label>
            <select class="ua-form-input" id="filterJenisKelamin">
                <option value="">Semua</option>
                <option value="jantan">Jantan</option>
                <option value="betina">Betina</option>
            </select>
        </div>
        <div>
            <label class="ua-form-label">Peternak</label>
            <select class="ua-form-input" id="filterPeternak">
                <option value="">Semua</option>
                @foreach ($peternak as $p)
                    <option value="{{ strtolower($p->nama_peternak) }}">{{ $p->nama_peternak }}</option>
                @endforeach
            </select>
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

{{-- ================= MODAL DETAIL SAPI (read-only, muncul saat klik baris) ================= --}}
<div id="modalDetailSapi" style="display:none;position:fixed;inset:0;background:rgba(15,17,26,0.5);z-index:200;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg);border-radius:12px;width:520px;max-width:92vw;max-height:88vh;overflow-y:auto;">
        <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div id="detailNama" style="font-size:15px;font-weight:600;">Nama Sapi</div>
                <div id="detailJenisKelamin" style="font-size:12px;color:var(--text-secondary);margin-top:2px;">Jenis Kelamin</div>
            </div>
            <i class="ti ti-x" style="cursor:pointer;color:var(--text-secondary);" onclick="closeDetailModal()"></i>
        </div>

        <div style="padding:24px;">
            <div style="margin-bottom:18px;">
                <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-secondary);margin-bottom:6px;">Warna</div>
                <div id="detailWarna" style="font-size:13.5px;line-height:1.7;">-</div>
            </div>
            <div style="margin-bottom:18px;">
                <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-secondary);margin-bottom:6px;">Umur</div>
                <div id="detailUmur" style="font-size:13.5px;line-height:1.7;">-</div>
            </div>
            <div style="margin-bottom:18px;">
                <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-secondary);margin-bottom:6px;">Ciri-Ciri</div>
                <div id="detailCiriCiri" style="font-size:13.5px;line-height:1.7;white-space:pre-line;">-</div>
            </div>
            <div>
                <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-secondary);margin-bottom:6px;">Peternak</div>
                <div id="detailPeternak" style="font-size:13.5px;line-height:1.7;">-</div>
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
<div id="modalSapi" style="display:none;position:fixed;inset:0;background:rgba(15,17,26,0.5);z-index:200;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg);border-radius:12px;width:480px;max-width:92vw;max-height:88vh;overflow-y:auto;">
        <form id="formSapi" method="POST">
            @csrf
            <div id="methodField"></div>

            <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                <div id="modalTitle" style="font-size:15px;font-weight:600;">Tambah Sapi</div>
                <i class="ti ti-x" style="cursor:pointer;color:var(--text-secondary);" onclick="closeModal()"></i>
            </div>

            <div style="padding:24px;">
                <div class="form-group">
                    <label class="form-label">Nama Sapi</label>
                    <input type="text" name="nm_sapi" id="input_nama" class="form-control" placeholder="Contoh: Jali" required maxlength="100">
                </div>

                <div class="form-group">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" id="input_jenis_kelamin" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        <option value="jantan">Jantan</option>
                        <option value="betina">Betina</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Warna</label>
                    <input type="text" name="warna" id="input_warna" class="form-control" placeholder="Contoh: Coklat belang putih" required maxlength="50">
                </div>

                <div class="form-group">
                    <label class="form-label">Umur</label>
                    <input type="text" name="umur" id="input_umur" class="form-control" placeholder="Contoh: 2 tahun" maxlength="20">
                </div>

                <div class="form-group">
                    <label class="form-label">Ciri-Ciri</label>
                    <textarea name="ciri_ciri" id="input_ciri_ciri" class="form-control" rows="3" placeholder="Ciri fisik tambahan biar gampang dikenali"></textarea>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Peternak</label>
                    <select name="peternak_id" id="input_peternak" class="form-control">
                        <option value="">-- Belum ada peternak --</option>
                        @foreach ($peternak as $p)
                            <option value="{{ $p->id }}">{{ $p->nama_peternak }}</option>
                        @endforeach
                    </select>
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
    #modalSapi .btn-primary,
    #modalSapi .btn-secondary,
    #modalDetailSapi .btn-primary,
    #modalDetailSapi .btn-secondary {
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

    #modalSapi .btn-primary i,
    #modalSapi .btn-secondary i,
    #modalDetailSapi .btn-primary i,
    #modalDetailSapi .btn-secondary i {
        font-size: 16px;
        line-height: 1;
    }

    #modalSapi .btn-primary,
    #modalSapi .btn-primary:focus,
    #modalSapi .btn-primary:active,
    #modalDetailSapi .btn-primary,
    #modalDetailSapi .btn-primary:focus,
    #modalDetailSapi .btn-primary:active {
        background: #3b82f6; border-color: #3b82f6; color: #fff;
    }
    #modalSapi .btn-primary:hover,
    #modalDetailSapi .btn-primary:hover {
        background: #2563eb; border-color: #2563eb;
    }
    #modalSapi .btn-secondary,
    #modalSapi .btn-secondary:focus,
    #modalSapi .btn-secondary:active,
    #modalDetailSapi .btn-secondary,
    #modalDetailSapi .btn-secondary:focus,
    #modalDetailSapi .btn-secondary:active {
        background: #fff; border: 1px solid #e2e8f0; color: #4a5568;
    }
    #modalSapi .btn-secondary:hover,
    #modalDetailSapi .btn-secondary:hover { background: #f7fafc; color: #ef4444; }

    /* .form-control:focus global di layouts/app.blade.php pakai var(--content-accent)
       yang warnanya hijau — di-override khusus di dalam modal ini biar konsisten biru */
    #modalSapi .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.14);
    }

    /* baris tabel sapi terasa clickable, kecuali kolom action */
    #sapiTable tbody tr td.ua-clickable-cell {
        cursor: pointer;
    }
    #sapiTable tbody tr:hover td.ua-clickable-cell {
        background: rgba(59,130,246,0.05);
    }
</style>
@endpush

@push('scripts')
<script>
    const modal        = document.getElementById('modalSapi');
    const form          = document.getElementById('formSapi');
    const methodField   = document.getElementById('methodField');
    const detailModal   = document.getElementById('modalDetailSapi');

    let selectedDetailId = null;

    // helper: potong teks panjang kayak Str::limit(), dipakai buat kolom ciri-ciri
    function uaLimit(text, len) {
        text = text ?? '';
        return text.length > len ? text.substring(0, len).trim() + '...' : text;
    }

    function uaCapitalize(text) {
        text = text ?? '';
        return text.charAt(0).toUpperCase() + text.slice(1);
    }

    // ================= INIT TABLE =================
    const sapiTable = UATable.init({
        tableId: 'sapiTable',
        data: {!! json_encode($sapi) !!},
        perPage: 10,
        emptyColspan: 6,
        emptyMessage: 'Belum ada data sapi.',
        defaultSort: (a, b) => a.nm_sapi.localeCompare(b.nm_sapi),

        renderRow: function (d, index) {
            const safeId = String(d.id);
            return '<tr>' +
                '<td class="ua-clickable-cell" onclick="openDetailModalById(\'' + safeId + '\')">' + (index + 1) + '</td>' +
                '<td class="ua-clickable-cell" style="text-align:left;font-weight:600;" onclick="openDetailModalById(\'' + safeId + '\')">' + d.nm_sapi + '</td>' +
                '<td class="ua-clickable-cell" style="text-align:left;" onclick="openDetailModalById(\'' + safeId + '\')">' + uaCapitalize(d.jenis_kelamin) + '</td>' +
                '<td class="ua-clickable-cell" style="text-align:left;" onclick="openDetailModalById(\'' + safeId + '\')">' + d.warna + '</td>' +
                '<td class="ua-clickable-cell" style="text-align:left;" onclick="openDetailModalById(\'' + safeId + '\')">' + (d.umur ?? '-') + '</td>' +
                '<td class="ua-clickable-cell" style="text-align:left;color:#6b7280;max-width:220px;" onclick="openDetailModalById(\'' + safeId + '\')">' + uaLimit(d.ciri_ciri, 60) + '</td>' +
                '<td class="ua-clickable-cell" style="text-align:left;" onclick="openDetailModalById(\'' + safeId + '\')">' + (d.nama_peternak ?? '-') + '</td>' +
                '<td><div class="ua-action-group">' +
                    '<button class="ua-action-btn edit" title="Edit" onclick="event.stopPropagation();openEditModalById(\'' + safeId + '\')"><i class="ti ti-pencil"></i></button>' +
                    '<button class="ua-action-btn delete" title="Hapus" onclick="event.stopPropagation();confirmDeleteSapi(\'' + safeId + '\', \'' + String(d.nm_sapi).replace(/'/g, "\\'") + '\')"><i class="ti ti-trash"></i></button>' +
                '</div></td>' +
            '</tr>';
        },

        getFilters: function () {
            return {
                search:   document.getElementById('sapiTableSearch').value.toLowerCase(),
                nama:     document.getElementById('filterNamaSapi').value.toLowerCase(),
                jenis:    document.getElementById('filterJenisKelamin').value.toLowerCase(),
                peternak: document.getElementById('filterPeternak').value.toLowerCase(),
            };
        },

        filterFn: function (d, f) {
            const namaPeternak = (d.nama_peternak ?? '').toLowerCase();
            return (d.nm_sapi.toLowerCase().includes(f.search) || d.warna.toLowerCase().includes(f.search)) &&
                d.nm_sapi.toLowerCase().includes(f.nama) &&
                d.jenis_kelamin.toLowerCase().includes(f.jenis) &&
                namaPeternak.includes(f.peternak);
        },
    });

    function clearSapiFilter() {
        document.getElementById('sapiTableSearch').value = '';
        document.getElementById('filterNamaSapi').value = '';
        document.getElementById('filterJenisKelamin').value = '';
        document.getElementById('filterPeternak').value = '';
        sapiTable.filter();
    }

    // ================= MODAL DETAIL (klik baris) =================
    function openDetailModalById(id) {
        const item = sapiTable.getData().find(x => String(x.id) === String(id));
        if (!item) return;

        selectedDetailId = item.id;

        document.getElementById('detailNama').innerText = item.nm_sapi;
        document.getElementById('detailJenisKelamin').innerText = uaCapitalize(item.jenis_kelamin);
        document.getElementById('detailWarna').innerText = item.warna;
        document.getElementById('detailUmur').innerText = item.umur && item.umur.length ? item.umur : '-';
        document.getElementById('detailCiriCiri').innerText = item.ciri_ciri && item.ciri_ciri.length ? item.ciri_ciri : '-';
        document.getElementById('detailPeternak').innerText = item.nama_peternak ?? '-';

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
        document.getElementById('modalTitle').innerText = 'Tambah Sapi';
        form.action = "{{ route('sapi.store') }}";
        methodField.innerHTML = '';
        form.reset();
        modal.style.display = 'flex';
    }

    function openEditModalById(id) {
        const item = sapiTable.getData().find(x => String(x.id) === String(id));
        if (!item) return;
        document.getElementById('modalTitle').innerText = 'Edit Sapi';
        form.action = "{{ url('sapi') }}/" + item.id;
        methodField.innerHTML = '@method("PUT")';
        document.getElementById('input_nama').value = item.nm_sapi;
        document.getElementById('input_jenis_kelamin').value = item.jenis_kelamin;
        document.getElementById('input_warna').value = item.warna;
        document.getElementById('input_umur').value = item.umur ?? '';
        document.getElementById('input_ciri_ciri').value = item.ciri_ciri ?? '';
        document.getElementById('input_peternak').value = item.peternak_id ?? '';
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    // ================= DELETE via UAAlert + form tersembunyi =================
    function confirmDeleteSapi(id, nama) {
        UAAlert.confirm({
            title: 'Hapus Sapi?',
            text: 'Yakin ingin menghapus "' + nama + '"? Data ini tidak bisa dikembalikan.',
            confirmText: 'Yes, Delete',
            cancelText: 'Cancel',
            onConfirm: function () {
                const deleteForm = document.getElementById('deleteForm');
                deleteForm.action = "{{ url('sapi') }}/" + id;
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