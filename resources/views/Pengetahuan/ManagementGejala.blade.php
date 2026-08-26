@extends('layouts.app')

@section('title', 'Manajemen Gejala')

@section('content')

<x-table
    id="gejalaTable"
    :columns="[
        ['key' => 'code_gejala', 'label' => 'Kode',        'sortable' => true, 'align' => 'left', 'width' => '110px'],
        ['key' => 'nm_gejala',   'label' => 'Nama Gejala', 'sortable' => true, 'align' => 'left'],
    ]"
    add-label="Tambah Gejala"
    add-onclick="openCreateModal()"
    search-placeholder="Cari kode atau nama gejala..."
    clear-onclick="clearGejalaFilter()"
>
    <x-slot:filters>
        <div>
            <label class="ua-form-label">Kode Gejala</label>
            <input type="text" class="ua-form-input" id="filterKode" placeholder="Contoh: G06">
        </div>
        <div>
            <label class="ua-form-label">Nama Gejala</label>
            <input type="text" class="ua-form-input" id="filterNamaGejala" placeholder="Nama gejala...">
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

{{-- ================= MODAL TAMBAH / EDIT ================= --}}
<div id="modalGejala" style="display:none;position:fixed;inset:0;background:rgba(15,17,26,0.5);z-index:200;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg);border-radius:12px;width:440px;max-width:92vw;max-height:88vh;overflow-y:auto;">
        <form id="formGejala" method="POST">
            @csrf
            <div id="methodField"></div>

            <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                <div id="modalTitle" style="font-size:15px;font-weight:600;">Tambah Gejala</div>
                <i class="ti ti-x" style="cursor:pointer;color:var(--text-secondary);" onclick="closeModal()"></i>
            </div>

            <div style="padding:24px;">
                <div class="form-group">
                    <label class="form-label">Kode Gejala</label>
                    <input type="text" name="code_gejala" id="input_kode" class="form-control" placeholder="Contoh: G06" required maxlength="20">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Nama Gejala</label>
                    <textarea name="nm_gejala" id="input_nama" class="form-control" rows="2" placeholder="Contoh: Pusing berkepanjangan" required></textarea>
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
       jaga-jaga kalau .btn-primary global di project beda warna (di layouts/app.blade.php dia hijau) */
    #modalGejala .btn-primary,
    #modalGejala .btn-secondary {
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

    #modalGejala .btn-primary i,
    #modalGejala .btn-secondary i {
        font-size: 16px;
        line-height: 1;
    }

    #modalGejala .btn-primary,
    #modalGejala .btn-primary:focus,
    #modalGejala .btn-primary:active {
        background: #3b82f6; border-color: #3b82f6; color: #fff;
    }
    #modalGejala .btn-primary:hover {
        background: #2563eb; border-color: #2563eb;
    }
    #modalGejala .btn-secondary,
    #modalGejala .btn-secondary:focus,
    #modalGejala .btn-secondary:active {
        background: #fff; border: 1px solid #e2e8f0; color: #4a5568;
    }
    #modalGejala .btn-secondary:hover { background: #f7fafc; color: #ef4444; }

    /* .form-control:focus global di layouts/app.blade.php pakai var(--content-accent)
       yang warnanya hijau — di-override khusus di dalam modal ini biar konsisten biru */
    #modalGejala .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.14);
    }
</style>
@endpush

@push('scripts')
<script>
    const modal        = document.getElementById('modalGejala');
    const form          = document.getElementById('formGejala');
    const methodField   = document.getElementById('methodField');

    // ================= INIT TABLE =================
    const gejalaTable = UATable.init({
        tableId: 'gejalaTable',
        data: {!! json_encode($gejala) !!},
        perPage: 10,
        emptyColspan: 4,
        emptyMessage: 'Belum ada data gejala.',
        defaultSort: (a, b) => a.code_gejala.localeCompare(b.code_gejala),

        // 1 baris <tr> lengkap, termasuk kolom No. dan Actions
        renderRow: function (d, index) {
            return '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td style="text-align:left;font-weight:600;">' + d.code_gejala + '</td>' +
                '<td style="text-align:left;">' + d.nm_gejala + '</td>' +
                '<td><div class="ua-action-group">' +
                    '<button class="ua-action-btn edit" title="Edit" onclick="openEditModalById(\'' + d.id + '\')"><i class="ti ti-pencil"></i></button>' +
                    '<button class="ua-action-btn delete" title="Hapus" onclick="confirmDeleteGejala(\'' + d.id + '\', \'' + String(d.nm_gejala).replace(/'/g, "\\'") + '\')"><i class="ti ti-trash"></i></button>' +
                '</div></td>' +
            '</tr>';
        },

        // baca semua input search + filter jadi 1 object
        getFilters: function () {
            return {
                search: document.getElementById('gejalaTableSearch').value.toLowerCase(),
                kode:   document.getElementById('filterKode').value.toLowerCase(),
                nama:   document.getElementById('filterNamaGejala').value.toLowerCase(),
            };
        },

        // cocokkan 1 row terhadap filters di atas
        filterFn: function (d, f) {
            return (d.code_gejala.toLowerCase().includes(f.search) || d.nm_gejala.toLowerCase().includes(f.search)) &&
                d.code_gejala.toLowerCase().includes(f.kode) &&
                d.nm_gejala.toLowerCase().includes(f.nama);
        },
    });

    // tombol "Reset" (eraser merah) di panel filter manggil ini
    function clearGejalaFilter() {
        document.getElementById('gejalaTableSearch').value = '';
        document.getElementById('filterKode').value = '';
        document.getElementById('filterNamaGejala').value = '';
        gejalaTable.filter();
    }

    // ================= MODAL TAMBAH/EDIT =================
    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Tambah Gejala';
        form.action = "{{ route('pengetahuan.gejala.store') }}";
        methodField.innerHTML = '';
        form.reset();
        modal.style.display = 'flex';
    }

    function openEditModalById(id) {
        const item = gejalaTable.getData().find(x => x.id === id);
        if (!item) return;
        document.getElementById('modalTitle').innerText = 'Edit Gejala';
        form.action = "{{ url('pengetahuan/gejala') }}/" + item.id;
        methodField.innerHTML = '@method("PUT")';
        document.getElementById('input_kode').value = item.code_gejala;
        document.getElementById('input_nama').value = item.nm_gejala;
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    // ================= DELETE via UAAlert + form tersembunyi =================
    function confirmDeleteGejala(id, nama) {
        UAAlert.confirm({
            title: 'Hapus Gejala?',
            text: 'Yakin ingin menghapus "' + nama + '"? Tindakan ini tidak dapat dibatalkan.',
            confirmText: 'Ya, Hapus',
            cancelText: 'Batal',
            onConfirm: function () {
                const deleteForm = document.getElementById('deleteForm');
                deleteForm.action = "{{ url('pengetahuan/gejala') }}/" + id;
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