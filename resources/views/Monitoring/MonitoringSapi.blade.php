@extends('layouts.app')

@section('title', 'Monitoring Sapi')

@section('content')

{{-- ================= FILTER BOX (tampilan identik dgn filter bawaan x-table,
     tapi berdiri sendiri di luar komponen supaya bisa berlaku utk tab Sehat & Tidak Sehat sekaligus) ================= --}}
<div class="ua-card" id="monUnifiedCard">
    <div class="ua-topbar">
        <div class="ua-search-combo">
            <span class="ua-search-icon"><i class="ti ti-search"></i></span>
            <input type="text" id="monQuickSearch" placeholder="Cari nama sapi..." oninput="applyMonitoringFilter()">
            <button class="ua-filter-toggle" id="monFilterToggleBtn" onclick="UATable.toggleFilterPanel('mon')" title="Filter">
                <i class="ti ti-filter"></i>
            </button>
        </div>
    </div>

    <div class="ua-filter-panel open" id="monFilterPanel">
        <div class="ua-filter-grid">
            <div>
                <label class="ua-form-label">Nama Sapi</label>
                <input type="text" class="ua-form-input" id="filterNamaMonitoring" placeholder="Nama sapi...">
            </div>
            <div class="ua-filter-actions">
                <button class="ua-btn-search" title="Cari" onclick="applyMonitoringFilter()">
                    <i class="ti ti-search"></i>
                </button>
                <button class="ua-btn-clear" title="Reset" onclick="clearMonitoringFilter()">
                    <i class="ti ti-eraser"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="mon-tabs">
    <button type="button" class="mon-tab-btn active" id="tabBtnReview" onclick="switchTab('review')">
        <span>Perlu Ditinjau</span>
        @if ($perluDitinjau->count() > 0)
            <span class="mon-count mon-count-alert">{{ $perluDitinjau->count() }}</span>
        @endif
    </button>
    <button type="button" class="mon-tab-btn" id="tabBtnSehat" onclick="switchTab('sehat')">
        <span>Sehat</span>
        <span class="mon-count">{{ $sehat->count() }}</span>
    </button>
    <button type="button" class="mon-tab-btn" id="tabBtnSakit" onclick="switchTab('sakit')">
        <span>Tidak Sehat</span>
        <span class="mon-count">{{ $sakit->count() }}</span>
    </button>
</div>

{{-- ================= TAB: PERLU DITINJAU (card grid) ================= --}}
<div id="tabReview" class="mon-tab-content">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Perlu Ditinjau</div>
        </div>

        @if ($perluDitinjau->count() === 0)
            <div class="mon-empty">
                <i class="ti ti-clipboard-check"></i>
                Tidak ada hasil diagnosa yang perlu ditinjau saat ini.
            </div>
        @else
            <div class="mon-review-grid">
                @foreach ($perluDitinjau as $item)
                    <div class="mon-review-card">
                        <div class="mon-review-card-header">
                            <div>
                                <div class="mon-review-nama">{{ $item->nm_sapi ?? $item->nama_pasien ?? 'Tanpa nama' }}</div>
                                <div class="mon-review-sub">
                                    {{ $item->jenis_kelamin ? ucfirst($item->jenis_kelamin) : '-' }}
                                    @if ($item->warna) &middot; {{ $item->warna }} @endif
                                </div>
                            </div>
                            <div class="mon-cf-badge">{{ number_format($item->nilai_cf * 100, 0) }}%</div>
                        </div>

                        <div class="mon-review-body">
                            <div class="mon-review-label">Terdeteksi</div>
                            <div class="mon-review-penyakit">{{ $item->nm_penyakit }}</div>

                            @if ($item->solusi)
                                <div class="mon-review-label" style="margin-top:10px;">Saran Penanganan</div>
                                <div class="mon-review-solusi">{{ \Illuminate\Support\Str::limit($item->solusi, 100) }}</div>
                            @endif

                            <div class="mon-review-tanggal">
                                <i class="ti ti-calendar"></i>
                                {{ \Carbon\Carbon::parse($item->tanggal_diagnosa)->translatedFormat('d M Y, H:i') }}
                            </div>
                        </div>

                        <div class="mon-review-actions">
                            <form action="{{ route('monitoring.abaikan', $item->hasil_id) }}" method="POST" style="flex:1;">
                                @csrf
                                <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;"
                                        onclick="return confirm('Abaikan hasil diagnosa ini? Status sapi tidak akan berubah.')">
                                    Abaikan
                                </button>
                            </form>
                            <form action="{{ route('monitoring.tandaiSakit', $item->hasil_id) }}" method="POST" style="flex:1;">
                                @csrf
                                <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;"
                                        {{ !$item->sapi_id ? 'disabled title=Sapi tidak terdaftar' : '' }}
                                        onclick="return confirm('Tandai sapi ini sebagai sakit?')">
                                    <i class="ti ti-alert-triangle"></i> Tandai Sakit
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- ================= TAB: SEHAT (x-table, filter sudah dipindah ke atas) ================= --}}
<div id="tabSehat" class="mon-tab-content" style="display:none;">
    <x-table
        id="sapiSehatTable"
        :columns="[
            ['key' => 'nm_sapi',       'label' => 'Nama Sapi',     'sortable' => true, 'align' => 'left'],
            ['key' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'sortable' => true, 'align' => 'left', 'width' => '130px'],
            ['key' => 'warna',         'label' => 'Warna',         'sortable' => false, 'align' => 'left'],
            ['key' => 'umur',          'label' => 'Umur',          'sortable' => false, 'align' => 'left', 'width' => '100px'],
        ]"
        search-placeholder="Cari nama sapi..."
    />
</div>

{{-- ================= TAB: TIDAK SEHAT (x-table, filter sudah dipindah ke atas) ================= --}}
<div id="tabSakit" class="mon-tab-content" style="display:none;">
    <x-table
        id="sapiSakitTable"
        :columns="[
            ['key' => 'nm_sapi',       'label' => 'Nama Sapi',     'sortable' => true, 'align' => 'left'],
            ['key' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'sortable' => true, 'align' => 'left', 'width' => '130px'],
            ['key' => 'warna',         'label' => 'Warna',         'sortable' => false, 'align' => 'left'],
            ['key' => 'umur',          'label' => 'Umur',          'sortable' => false, 'align' => 'left', 'width' => '100px'],
        ]"
        search-placeholder="Cari nama sapi..."
    />
</div>

</div>

<form id="sembuhForm" method="POST" style="display:none;">
    @csrf
</form>

@endsection

@push('styles')
<style>
    .mon-tabs {
        display: flex;
        align-items: center;
        gap: 26px;
        border-bottom: 1px solid #e5e7eb;
        padding: 0 4px;
        margin-top: 16px;
        margin-bottom: 0;
    }
    .mon-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 13px 2px;
        border: none;
        border-bottom: 2px solid transparent;
        background: transparent;
        color: var(--text-secondary);
        font-family: inherit;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: color 0.15s, border-color 0.15s;
        white-space: nowrap;
        margin-bottom: -1px;
    }
    .mon-tab-btn:hover { color: var(--text-primary); }
    .mon-tab-btn.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
    }

    /* card/ua-card bawaan di tiap tab (termasuk yg otomatis dirender komponen x-table)
       diplototin jadi polos karena sekarang sudah nempel di dalam #monUnifiedCard,
       jadi nggak perlu bikin box baru lagi */
    .mon-tab-content .ua-card,
    .mon-tab-content .card {
        border: none;
        box-shadow: none;
        border-radius: 0;
        margin: 0;
        background: transparent;
    }
    .mon-tab-content {
        padding-top: 4px;
    }

    .mon-count {
        background: var(--border);
        color: var(--text-secondary);
        font-size: 11px;
        font-weight: 600;
        padding: 1px 7px;
        border-radius: 20px;
        min-width: 18px;
        text-align: center;
    }
    .mon-tab-btn.active .mon-count { background: rgba(59,130,246,0.14); color: #3b82f6; }
    .mon-tab-btn.active .mon-count-alert { background: rgba(239,68,68,0.14); color: var(--danger); }
    .mon-count-alert {
        background: rgba(239,68,68,0.14);
        color: var(--danger);
    }

    .mon-empty {
        text-align: center;
        padding: 50px 20px;
        color: var(--text-secondary);
    }
    .mon-empty i { font-size: 40px; opacity: 0.4; margin-bottom: 10px; display: block; }

    .mon-review-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
    }

    .mon-review-card {
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .mon-review-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        padding: 16px 18px;
        background: var(--main-bg);
        border-bottom: 1px solid var(--border);
    }

    .mon-review-nama { font-size: 14.5px; font-weight: 600; }
    .mon-review-sub { font-size: 12px; color: var(--text-secondary); margin-top: 2px; }

    .mon-cf-badge {
        background: rgba(239,68,68,0.12);
        color: var(--danger);
        font-size: 13px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        flex-shrink: 0;
    }

    .mon-review-body { padding: 16px 18px; flex: 1; }
    .mon-review-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }
    .mon-review-penyakit { font-size: 14px; font-weight: 600; margin-bottom: 2px; }
    .mon-review-solusi { font-size: 12.5px; color: var(--text-secondary); line-height: 1.5; }

    .mon-review-tanggal {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11.5px;
        color: var(--text-secondary);
        margin-top: 14px;
    }
    .mon-review-tanggal i { font-size: 14px; }

    .mon-review-actions {
        display: flex;
        gap: 8px;
        padding: 14px 18px;
        border-top: 1px solid var(--border);
    }
    .mon-review-actions .btn:disabled { opacity: 0.5; cursor: not-allowed; }

    #sapiSakitTable .ua-action-btn.sembuh {
        color: #16a34a;
        border-color: rgba(34,197,94,0.3);
    }
    #sapiSakitTable .ua-action-btn.sembuh:hover {
        background: rgba(34,197,94,0.1);
    }

    /* search box & toolbar bawaan x-table disembunyikan/dirapatkan di tab Sehat & Sakit
       karena fungsi search sudah digantikan oleh box "Cari Nama Sapi" di atas tab,
       dan tombol add ("Kelola Data Sapi") sudah tidak dipakai lagi. */
    #sapiSehatTable .ua-search-combo,
    #sapiSakitTable .ua-search-combo {
        display: none;
    }
    #sapiSehatTable .ua-topbar,
    #sapiSakitTable .ua-topbar {
        min-height: 0;
        padding-top: 0;
        padding-bottom: 6px;
        margin-bottom: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    // ================= TAB SWITCH =================
    function switchTab(tab) {
        document.querySelectorAll('.mon-tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.mon-tab-btn').forEach(el => el.classList.remove('active'));

        document.getElementById('tab' + tab.charAt(0).toUpperCase() + tab.slice(1)).style.display = 'block';
        document.getElementById('tabBtn' + tab.charAt(0).toUpperCase() + tab.slice(1)).classList.add('active');
    }

    // ================= FILTER (dipakai bareng oleh tabel Sehat & Sakit) =================
    function applyMonitoringFilter() {
        sapiSehatTable.filter();
        sapiSakitTable.filter();
    }

    function clearMonitoringFilter() {
        document.getElementById('monQuickSearch').value = '';
        document.getElementById('filterNamaMonitoring').value = '';
        applyMonitoringFilter();
    }

    // ================= TABLE: SEHAT =================
    const sapiSehatTable = UATable.init({
        tableId: 'sapiSehatTable',
        data: {!! json_encode($sehat) !!},
        perPage: 10,
        emptyColspan: 6,
        emptyMessage: 'Belum ada data sapi berstatus sehat.',
        defaultSort: (a, b) => a.nm_sapi.localeCompare(b.nm_sapi),

        renderRow: function (d, index) {
            return '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td style="text-align:left;font-weight:600;">' + d.nm_sapi + '</td>' +
                '<td style="text-align:left;">' + (d.jenis_kelamin.charAt(0).toUpperCase() + d.jenis_kelamin.slice(1)) + '</td>' +
                '<td style="text-align:left;">' + d.warna + '</td>' +
                '<td style="text-align:left;">' + (d.umur ?? '-') + '</td>' +
                '<td></td>' +
            '</tr>';
        },

        getFilters: function () {
            return {
                search: document.getElementById('monQuickSearch').value.toLowerCase(),
                nama:   document.getElementById('filterNamaMonitoring').value.toLowerCase(),
            };
        },

        filterFn: function (d, f) {
            return d.nm_sapi.toLowerCase().includes(f.search) &&
                d.nm_sapi.toLowerCase().includes(f.nama);
        },
    });

    // ================= TABLE: SAKIT =================
    const sapiSakitTable = UATable.init({
        tableId: 'sapiSakitTable',
        data: {!! json_encode($sakit) !!},
        perPage: 10,
        emptyColspan: 6,
        emptyMessage: 'Tidak ada sapi berstatus sakit saat ini.',
        defaultSort: (a, b) => a.nm_sapi.localeCompare(b.nm_sapi),

        renderRow: function (d, index) {
            const safeId = String(d.id);
            return '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td style="text-align:left;font-weight:600;">' + d.nm_sapi + '</td>' +
                '<td style="text-align:left;">' + (d.jenis_kelamin.charAt(0).toUpperCase() + d.jenis_kelamin.slice(1)) + '</td>' +
                '<td style="text-align:left;">' + d.warna + '</td>' +
                '<td style="text-align:left;">' + (d.umur ?? '-') + '</td>' +
                '<td><div class="ua-action-group">' +
                    '<button class="ua-action-btn sembuh" title="Tandai Sembuh" onclick="tandaiSembuh(\'' + safeId + '\', \'' + String(d.nm_sapi).replace(/'/g, "\\'") + '\')"><i class="ti ti-circle-check"></i></button>' +
                '</div></td>' +
            '</tr>';
        },

        getFilters: function () {
            return {
                search: document.getElementById('monQuickSearch').value.toLowerCase(),
                nama:   document.getElementById('filterNamaMonitoring').value.toLowerCase(),
            };
        },

        filterFn: function (d, f) {
            return d.nm_sapi.toLowerCase().includes(f.search) &&
                d.nm_sapi.toLowerCase().includes(f.nama);
        },
    });

    function tandaiSembuh(id, nama) {
        UAAlert.confirm({
            title: 'Tandai Sembuh?',
            text: '"' + nama + '" akan dipindahkan ke daftar sapi sehat.',
            confirmText: 'Yes, Confirm',
            cancelText: 'Cancel',
            onConfirm: function () {
                const form = document.getElementById('sembuhForm');
                form.action = "{{ url('monitoring') }}/" + id + "/sembuh";
                form.submit();
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