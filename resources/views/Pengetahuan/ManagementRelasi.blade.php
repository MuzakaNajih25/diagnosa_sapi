@extends('layouts.app')

@section('title', 'Relasi Penyakit-Gejala')

@section('content')

<p style="font-size:13px;color:var(--text-secondary);margin-bottom:14px;">
    Pilih penyakit untuk mengatur gejala terkait beserta nilai CF pakarnya.
</p>

<x-table
    id="relasiTable"
    :columns="[
        ['key' => 'code_penyakit',  'label' => 'Kode',             'sortable' => true, 'align' => 'left', 'width' => '90px'],
        ['key' => 'nm_penyakit',    'label' => 'Nama Penyakit',    'sortable' => true, 'align' => 'left'],
        ['key' => 'jumlah_gejala',  'label' => 'Gejala Terelasi',  'sortable' => true, 'width' => '160px'],
    ]"
    search-placeholder="Cari kode atau nama penyakit..."
    clear-onclick="clearRelasiFilter()"
>
    <x-slot:filters>
        <div>
            <label class="ua-form-label">Nama Penyakit</label>
            <input type="text" class="ua-form-input" id="filterNamaRelasi" placeholder="Nama penyakit...">
        </div>
        <div>
            <label class="ua-form-label">Status Relasi</label>
            <select class="ua-form-select" id="filterStatusRelasi">
                <option value="">Semua Status</option>
                <option value="set">Sudah diatur</option>
                <option value="unset">Belum diatur</option>
            </select>
        </div>
    </x-slot:filters>
</x-table>

@endsection

@push('styles')
<style>
    /* badge "X gejala" / "Belum diatur" — pill solid gradient, teks putih, senada gaya .ua-status-badge */
    .relasi-count-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 14px; border-radius: 999px;
        font-size: 11.5px; font-weight: 800; letter-spacing: 0.2px;
        color: #fff; white-space: nowrap;
        box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    }
    .relasi-count-badge i { font-size: 13px; }
    .relasi-count-badge.set {
        background: linear-gradient(135deg, #14b8a6, #0d9488);
        color: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    }
    .relasi-count-badge.unset {
        background: #f5f6fa; color: #9ca3af; box-shadow: none;
    }

    /* tombol "Atur Gejala & CF" — pill button biru, ada efek hover lift, icon di depan */
    .relasi-action-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 16px; border-radius: 999px;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #fff; font-size: 12.5px; font-weight: 600;
        white-space: nowrap; text-decoration: none;
        box-shadow: 0 2px 6px rgba(37,99,235,0.28);
        transition: transform 0.12s ease, box-shadow 0.12s ease, background 0.15s ease;
    }
    .relasi-action-btn i { font-size: 15px; }
    .relasi-action-btn:hover {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        box-shadow: 0 3px 10px rgba(37,99,235,0.4);
        transform: translateY(-1px);
        color: #fff;
    }
    .relasi-action-btn:active { transform: translateY(0); }
</style>
@endpush

@push('scripts')
<script>
    // trik: generate template URL route Laravel di server, isi id-nya belakangan di JS.
    // Aman dipakai berapapun param di URI-nya, gak perlu tau struktur route persis.
    const relasiEditUrlTemplate = "{{ route('pengetahuan.relasi.edit', ':id') }}";

    // ================= INIT TABLE =================
    const relasiTable = UATable.init({
        tableId: 'relasiTable',
        data: {!! json_encode($penyakit) !!},
        perPage: 10,
        emptyColspan: 5,
        emptyMessage: 'Belum ada data penyakit. Tambahkan penyakit dulu di menu Manajemen Penyakit.',
        defaultSort: (a, b) => a.code_penyakit.localeCompare(b.code_penyakit),

        renderRow: function (d, index) {
            const badge = d.jumlah_gejala > 0
                ? '<span class="relasi-count-badge set"><i class="ti ti-circle-check-filled"></i>' + d.jumlah_gejala + ' gejala</span>'
                : '<span class="relasi-count-badge unset"><i class="ti ti-circle-dashed"></i>Belum diatur</span>';

            const editUrl = relasiEditUrlTemplate.replace(':id', d.id);

            return '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td style="text-align:left;font-weight:600;">' + d.code_penyakit + '</td>' +
                '<td style="text-align:left;">' + d.nm_penyakit + '</td>' +
                '<td>' + badge + '</td>' +
                '<td><a href="' + editUrl + '" class="relasi-action-btn">' +
                    '<i class="ti ti-adjustments"></i> Atur Gejala & CF</a></td>' +
            '</tr>';
        },

        getFilters: function () {
            return {
                search: document.getElementById('relasiTableSearch').value.toLowerCase(),
                nama:   document.getElementById('filterNamaRelasi').value.toLowerCase(),
                status: document.getElementById('filterStatusRelasi').value,
            };
        },

        filterFn: function (d, f) {
            const matchesStatus = f.status === ''
                || (f.status === 'set'   && d.jumlah_gejala > 0)
                || (f.status === 'unset' && d.jumlah_gejala == 0);

            return (d.code_penyakit.toLowerCase().includes(f.search) || d.nm_penyakit.toLowerCase().includes(f.search)) &&
                d.nm_penyakit.toLowerCase().includes(f.nama) &&
                matchesStatus;
        },
    });

    function clearRelasiFilter() {
        document.getElementById('relasiTableSearch').value = '';
        document.getElementById('filterNamaRelasi').value = '';
        document.getElementById('filterStatusRelasi').value = '';
        relasiTable.filter();
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