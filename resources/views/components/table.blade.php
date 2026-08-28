{{--
    ============================================================
    <x-data-table /> — Reusable Table Card (search + filter +
    sortable table + pagination), berpasangan dengan window.UATable
    ============================================================
    Taruh di: resources/views/components/table.blade.php

    CARA PAKAI DI MODUL BARU (contoh: useraccess)
    ------------------------------------------------------------
    1) Panggil component di Blade, kasih daftar kolom + slot filter:

        <x-data-table
            id="userAccessTable"
            :columns="[
                ['key' => 'nama',  'label' => 'Nama Pengguna', 'sortable' => true, 'align' => 'left'],
                ['key' => 'email', 'label' => 'Email',         'sortable' => true, 'align' => 'left'],
                ['key' => 'role',  'label' => 'Role',          'sortable' => true, 'width' => '120px'],
                ['key' => 'status','label' => 'Status',        'sortable' => true, 'width' => '110px'],
            ]"
            add-label="Add"
            add-onclick="openModal('add')"
            search-placeholder="Search ID, Nama, Email..."
            clear-onclick="clearUserAccessFilter()"
        >
            <x-slot:filters>
                <div>
                    <label class="ua-form-label">Nama</label>
                    <input type="text" class="ua-form-input" id="filterNama" placeholder="Nama pengguna...">
                </div>
                <div>
                    <label class="ua-form-label">Role</label>
                    <select class="ua-form-select" id="filterRole">
                        <option value="">Semua Role</option>
                        <option value="0">Users</option>
                        <option value="1">Dokter</option>
                        <option value="2">Admin</option>
                    </select>
                </div>
            </x-slot:filters>
        </x-data-table>

    2) Di @push('scripts'), init sekali lewat UATable.init(...):

        const table = UATable.init({
            tableId: 'userAccessTable',
            data: {!! json_encode($users) !!},
            perPage: 5,
            emptyColspan: 6,
            emptyMessage: 'Tidak ada data ditemukan',

            // WAJIB: bangun 1 baris <tr> penuh (termasuk kolom No. & Actions)
            renderRow: function (d, index) {
                const rc = roleConfig[d.role] ?? roleConfig[0];
                return '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td style="text-align:left">' + d.nama + '</td>' +
                    '<td style="text-align:left">' + d.email + '</td>' +
                    '<td><span class="ua-role-badge" style="background:' + rc.bg + ';color:' + rc.color + ';">' +
                        '<i class="ti ' + rc.icon + '" style="font-size:11px;"></i> ' + rc.label + '</span></td>' +
                    '<td>' + (d.status === 'active'
                        ? '<span class="ua-status-badge active">Aktif</span>'
                        : '<span class="ua-status-badge inactive">Nonaktif</span>') + '</td>' +
                    '<td><div class="ua-action-group">' +
                        '<button class="ua-action-btn edit" title="Edit Data" onclick="openModal(\'edit\',\'' + d.id + '\')"><i class="ti ti-pencil"></i></button>' +
                        '<button class="ua-action-btn delete" title="Hapus Akses" onclick="openDelete(\'' + d.id + '\')"><i class="ti ti-trash"></i></button>' +
                    '</div></td>' +
                '</tr>';
            },

            // WAJIB kalau mau search/filter jalan: baca semua input filter jadi 1 object
            getFilters: function () {
                return {
                    search: document.getElementById('userAccessTableSearch').value.toLowerCase(),
                    nama:   document.getElementById('filterNama').value.toLowerCase(),
                    role:   document.getElementById('filterRole').value,
                };
            },

            // WAJIB: logika cocok/tidaknya 1 row terhadap filters di atas
            filterFn: function (d, f) {
                return (d.nama.toLowerCase().includes(f.search) || d.email.toLowerCase().includes(f.search)) &&
                    d.nama.toLowerCase().includes(f.nama) &&
                    (f.role === '' || String(d.role) === f.role);
            },

            defaultSort: (a, b) => a.nama.localeCompare(b.nama),
        });

        // dipanggil dari tombol "Reset" (clear-onclick di atas)
        function clearUserAccessFilter() {
            document.getElementById('userAccessTableSearch').value = '';
            document.getElementById('filterNama').value = '';
            document.getElementById('filterRole').value = '';
            table.filter();
        }

    3) Update data tanpa reload:
        table.addItem(newRow);
        table.updateItem(id, { nama, email, role });
        table.removeItem(id);

    Element id yang di-generate otomatis dari prop `id` (mis. "userAccessTable"):
        {id}Body, {id}Search, {id}FilterPanel, {id}FilterToggleBtn,
        {id}Info, {id}Pagination, {id}PageInfo

    ------------------------------------------------------------
    PERUBAHAN (update layout):
    Tombol Search (biru) & Clear/Reset (merah) sekarang ditaruh di
    topbar, sejajar dengan tombol funnel/filter toggle (bukan lagi
    di bawah grid filter). Panel filter jadi cuma berisi field-field
    filter saja tanpa tombol aksi.
    ============================================================
--}}

@props([
    'id'                => 'uaTable',
    'columns'           => [],
    'addLabel'          => 'Add',
    'addOnclick'        => null,
    'searchPlaceholder' => 'Cari...',
    'searchOnclick'     => null,   // default: UATable.filter(id)
    'clearOnclick'      => null,   // wajib diisi kalau ada custom filter fields
    'emptyColspan'      => null,
])

@php
    $emptyColspan = $emptyColspan ?? (count($columns) + 2);
    $hasFilters   = isset($filters) && trim($filters) !== '';
@endphp

@once
@push('styles')
<style>
    button.ua-tbl-reset, input.ua-tbl-reset, select.ua-tbl-reset { box-shadow: none !important; }

    .ua-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 10px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03); padding: 20px 22px; margin-bottom: 18px;
    }

    .ua-topbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .ua-search-combo {
        display: flex; align-items: center; border: 1px solid #e2e8f0; border-radius: 8px;
        background: #fff; flex: 1; min-width: 240px; max-width: 520px; overflow: hidden;
    }
    .ua-search-combo .ua-search-icon { padding-left: 14px; color: #9ca3af; font-size: 15px; display: flex; align-items: center; }
    .ua-search-combo input {
        border: none; outline: none; font-size: 13.5px; padding: 10px 12px;
        font-family: inherit; flex: 1; background: transparent; color: #374151;
    }
    .ua-filter-toggle {
        width: 42px; height: 42px; border: none; flex-shrink: 0;
        background: #3b82f6; color: #fff; cursor: pointer; font-size: 19px;
        display: flex; align-items: center; justify-content: center; transition: background 0.15s;
    }
    .ua-filter-toggle:hover { background: #2563eb; }
    .ua-filter-toggle.active { background: #2563eb; }

    .ua-btn-add {
        margin-left: auto; display: inline-flex; align-items: center; gap: 7px;
        padding: 10px 18px; border-radius: 8px; border: none; flex-shrink: 0;
        background: #3b82f6; color: #fff; font-size: 13.5px; font-weight: 600;
        font-family: inherit; cursor: pointer; transition: background 0.15s;
    }
    .ua-btn-add i { font-size: 18px; }
    .ua-btn-add:hover { background: #2563eb; }

    .ua-filter-panel { max-height: 0; overflow: hidden; transition: max-height 0.25s ease, margin-top 0.25s ease; margin-top: 0; }
    .ua-filter-panel.open { max-height: 320px; margin-top: 18px; }

    /* Flex, bukan grid tetap 4 kolom, supaya field bisa 3/4/berapapun
       dan tombol aksi selalu nempel di paling kanan baris yang sama
       (atau ke bawah kalau space kurang, tapi tetap rata kanan) */
    .ua-filter-grid {
        display: flex; flex-wrap: wrap; align-items: flex-end;
        gap: 14px 16px; padding-top: 16px; border-top: 1px solid #eef0f2;
    }
    .ua-filter-grid > div { flex: 0 1 220px; min-width: 160px; max-width: 260px; }

    .ua-form-label { font-size: 12px; font-weight: 600; color: #6b7280; margin-bottom: 6px; display: block; }
    .ua-form-select, .ua-form-input {
        width: 100%; padding: 9px 11px; border: 1px solid #e2e8f0; border-radius: 8px;
        font-size: 13px; font-family: inherit; color: #374151; background: #fff; outline: none; transition: border-color 0.15s;
    }
    .ua-form-select:focus, .ua-form-input:focus { border-color: #3b82f6; }

    /* Wrapper tombol Search & Clear, selalu didorong ke ujung kanan baris */
    .ua-filter-actions {
        flex: 0 0 auto; display: flex; gap: 8px;
    }
    .ua-btn-search, .ua-btn-clear {
        width: 42px; height: 42px; padding: 0; border-radius: 8px; border: none; color: #fff; font-size: 19px;
        font-family: inherit; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: background 0.15s;
    }
    .ua-btn-search { background: #3b82f6; } .ua-btn-search:hover { background: #2563eb; }
    .ua-btn-clear  { background: #ef4444; } .ua-btn-clear:hover  { background: #dc2626; }

    .ua-info-row { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-top: 18px; margin-bottom: 0; }
    .ua-info-text { font-size: 12.5px; color: #9ca3af; display: flex; align-items: center; gap: 8px; }

    .ua-table-wrap { margin: 18px -22px -20px -22px; border-top: 1px solid #eef0f2; overflow: hidden; border-radius: 0 0 10px 10px; background: #fff; }
    .ua-data-table { width: 100%; border-collapse: collapse; font-size: 13.5px; font-family: inherit; }
    .ua-data-table thead th {
        padding: 13px 16px; font-size: 12.5px; font-weight: 700; color: #6b7280; background: #f9fafb;
        border-bottom: 1px solid #eef0f2; border-right: 1px solid #eef0f2; text-align: center; white-space: nowrap; letter-spacing: 0.2px;
    }
    .ua-data-table thead th:last-child { border-right: none; }
    .ua-data-table thead th[data-align="left"] { text-align: left; }
    .ua-data-table thead th.sortable { cursor: pointer; user-select: none; position: relative; }
    .ua-data-table thead th.sortable:hover { background: #f3f4f6; }
    .ua-sort-icon { margin-left: 5px; font-size: 11px; color: #c4c9d0; }
    .ua-data-table thead th.sort-asc .ua-sort-icon, .ua-data-table thead th.sort-desc .ua-sort-icon { color: #3b82f6; }

    .ua-data-table tbody td { padding: 12px 16px; border-bottom: 1px solid #eef0f2; border-right: 1px solid #eef0f2; vertical-align: middle; text-align: center; color: #4b5563; }
    .ua-data-table tbody td:last-child { border-right: none; }
    .ua-data-table tbody tr:nth-child(even) { background: #fbfcfd; }
    .ua-data-table tbody tr:hover { background: #f3f7ff; }
    .ua-data-table tbody tr:last-child td { border-bottom: none; }

    .ua-role-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; }
    .ua-status-badge { display: inline-flex; align-items: center; padding: 3px 11px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.3px; text-transform: uppercase; color: #fff; }
    .ua-status-badge.active { background: #0d9488; } .ua-status-badge.inactive { background: #ef4444; }

    .ua-table-footer { padding: 12px 16px; border-top: 1px solid #eef0f2; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; background: #fff; }
    .ua-pagination { display: flex; align-items: center; gap: 4px; }
    .ua-page-btn {
        min-width: 32px; height: 32px; padding: 0 8px; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff;
        font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #718096; transition: all 0.15s; font-family: inherit;
    }
    .ua-page-btn:hover:not(:disabled) { border-color: #3b82f6; color: #3b82f6; }
    .ua-page-btn.active { background: #3b82f6; border-color: #3b82f6; color: #fff; font-weight: 600; }
    .ua-page-btn:disabled { opacity: 0.35; cursor: not-allowed; }

    @media (max-width: 560px) {
        .ua-filter-grid > div { flex-basis: 100%; }
        .ua-filter-actions { width: 100%; justify-content: flex-end; }
        .ua-btn-add { margin-left: 0; }
        .ua-table-wrap { margin: 18px -14px -20px -14px; }
    }
</style>
@endpush

@push('scripts')
<script>
window.UATable = window.UATable || (function () {
    const instances = {};

    function init(config) {
        const id = config.tableId;
        instances[id] = {
            id,
            allData: config.data || [],
            filteredData: [...(config.data || [])],
            perPage: config.perPage || 5,
            currentPage: 1,
            sortKey: config.defaultSortKey || null,
            sortDir: 'asc',
            renderRow: config.renderRow,
            filterFn: config.filterFn || function () { return true; },
            getFilters: config.getFilters || function () { return {}; },
            defaultSort: config.defaultSort || null,
            emptyMessage: config.emptyMessage || 'Tidak ada data ditemukan',
            emptyColspan: config.emptyColspan || 6,
        };
        filter(id);
        return {
            setData:    (data) => { instances[id].allData = data; filter(id); },
            addItem:    (item) => { instances[id].allData.push(item); filter(id); },
            updateItem: (rowId, patch) => {
                const st = instances[id];
                const idx = st.allData.findIndex(x => x.id === rowId);
                if (idx !== -1) st.allData[idx] = Object.assign({}, st.allData[idx], patch);
                filter(id);
            },
            removeItem: (rowId) => {
                instances[id].allData = instances[id].allData.filter(x => x.id !== rowId);
                filter(id);
            },
            getData: () => instances[id].allData,
            filter:  () => filter(id),
            refresh: () => renderTable(id),
        };
    }

    function filter(id) {
        const st = instances[id];
        const filters = st.getFilters();
        st.filteredData = st.allData.filter(d => st.filterFn(d, filters));
        if (st.sortKey) applySort(id);
        else if (st.defaultSort) st.filteredData.sort(st.defaultSort);
        st.currentPage = 1;
        renderTable(id);
    }

    function sort(id, key) {
        const st = instances[id];
        if (st.sortKey === key) st.sortDir = st.sortDir === 'asc' ? 'desc' : 'asc';
        else { st.sortKey = key; st.sortDir = 'asc'; }
        applySort(id);
        st.currentPage = 1;
        renderTable(id);
        updateSortIcons(id);
    }

    function applySort(id) {
        const st = instances[id];
        st.filteredData.sort((a, b) => {
            let va = a[st.sortKey], vb = b[st.sortKey];
            if (typeof va === 'number' && typeof vb === 'number') return st.sortDir === 'asc' ? va - vb : vb - va;
            va = String(va ?? '').toLowerCase();
            vb = String(vb ?? '').toLowerCase();
            return st.sortDir === 'asc' ? va.localeCompare(vb) : vb.localeCompare(va);
        });
    }

    function updateSortIcons(id) {
        const st = instances[id];
        document.querySelectorAll('#' + id + ' thead th.sortable').forEach(function (th) {
            th.classList.remove('sort-asc', 'sort-desc');
            const icon = th.querySelector('.ua-sort-icon');
            if (th.dataset.key === st.sortKey) {
                th.classList.add(st.sortDir === 'asc' ? 'sort-asc' : 'sort-desc');
                if (icon) icon.textContent = st.sortDir === 'asc' ? '▲' : '▼';
            } else if (icon) icon.textContent = '⇅';
        });
    }

    function renderTable(id) {
        const st = instances[id];
        const tbody = document.getElementById(id + 'Body');
        if (!tbody) return;
        const total = st.filteredData.length;
        const start = (st.currentPage - 1) * st.perPage;
        const end = start + st.perPage;
        const pageData = st.filteredData.slice(start, end);

        const infoEl = document.getElementById(id + 'Info');
        if (infoEl) {
            infoEl.textContent = total > 0
                ? 'Showing ' + (start + 1) + ' to ' + Math.min(end, total) + ' of ' + total + ' entries'
                : 'Tidak ada data';
        }

        if (!pageData.length) {
            tbody.innerHTML = '<tr><td colspan="' + st.emptyColspan + '" style="text-align:center;padding:44px;color:#a0aec0;">' +
                '<i class="ti ti-inbox" style="font-size:34px;display:block;margin-bottom:10px;opacity:0.4;"></i>' +
                st.emptyMessage + '</td></tr>';
            renderPagination(id, 0);
            return;
        }

        tbody.innerHTML = pageData.map((d, i) => st.renderRow(d, start + i)).join('');
        renderPagination(id, total);
    }

    function renderPagination(id, total) {
        const st = instances[id];
        const tp = Math.ceil(total / st.perPage);
        const con = document.getElementById(id + 'Pagination');
        const pageInfo = document.getElementById(id + 'PageInfo');
        if (pageInfo) pageInfo.textContent = tp > 0 ? 'Halaman ' + st.currentPage + ' dari ' + tp : '';
        if (!con) return;
        if (tp <= 1) { con.innerHTML = ''; return; }

        let html = '<button class="ua-page-btn" onclick="UATable.goPage(\'' + id + '\',' + (st.currentPage - 1) + ')" ' + (st.currentPage === 1 ? 'disabled' : '') + '>' +
            '<i class="ti ti-chevron-left" style="font-size:13px;"></i></button>';
        for (let p = 1; p <= tp; p++) {
            html += '<button class="ua-page-btn' + (p === st.currentPage ? ' active' : '') + '" onclick="UATable.goPage(\'' + id + '\',' + p + ')">' + p + '</button>';
        }
        html += '<button class="ua-page-btn" onclick="UATable.goPage(\'' + id + '\',' + (st.currentPage + 1) + ')" ' + (st.currentPage === tp ? 'disabled' : '') + '>' +
            '<i class="ti ti-chevron-right" style="font-size:13px;"></i></button>';
        con.innerHTML = html;
    }

    function goPage(id, p) {
        const st = instances[id];
        const tp = Math.ceil(st.filteredData.length / st.perPage);
        if (p < 1 || p > tp) return;
        st.currentPage = p;
        renderTable(id);
    }

    function toggleFilterPanel(id) {
        const panel = document.getElementById(id + 'FilterPanel');
        const btn = document.getElementById(id + 'FilterToggleBtn');
        if (panel) panel.classList.toggle('open');
        if (btn) btn.classList.toggle('active');
    }

    return { init, filter, sort, goPage, toggleFilterPanel, updateSortIcons };
})();
</script>
@endpush
@endonce

<div class="ua-card" id="{{ $id }}">
    <div class="ua-topbar">
        <div class="ua-search-combo">
            <span class="ua-search-icon"><i class="ti ti-search"></i></span>
            <input type="text" id="{{ $id }}Search" placeholder="{{ $searchPlaceholder }}"
                oninput="{{ $searchOnclick ?? "UATable.filter('{$id}')" }}">
            @if($hasFilters)
                <button class="ua-filter-toggle" id="{{ $id }}FilterToggleBtn"
                    onclick="UATable.toggleFilterPanel('{{ $id }}')" title="Filter">
                    <i class="ti ti-filter"></i>
                </button>
            @endif
        </div>

        @if($addOnclick)
            <button class="ua-btn-add" onclick="{{ $addOnclick }}">
                <i class="ti ti-plus"></i> {{ $addLabel }}  
            </button>
        @endif
    </div>

    @if($hasFilters)
        <div class="ua-filter-panel open" id="{{ $id }}FilterPanel">
            <div class="ua-filter-grid">
                {{ $filters }}
                <div class="ua-filter-actions">
                    <button class="ua-btn-search" title="Cari" onclick="{{ $searchOnclick ?? "UATable.filter('{$id}')" }}">
                        <i class="ti ti-search"></i>
                    </button>
                    <button class="ua-btn-clear" title="Reset" onclick="{{ $clearOnclick ?? "UATable.filter('{$id}')" }}">
                        <i class="ti ti-eraser"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="ua-info-row">
        <span class="ua-info-text" id="{{ $id }}Info">Showing data...</span>
    </div>

    <div class="ua-table-wrap">
        <div style="overflow-x:auto;">
            <table class="ua-data-table">
                <thead>
                    <tr>
                        <th style="width:50px;">No.</th>
                        @foreach($columns as $col)
                            <th
                                @if($col['sortable'] ?? false) class="sortable" data-key="{{ $col['key'] }}" onclick="UATable.sort('{{ $id }}','{{ $col['key'] }}')" @endif
                                @if(($col['align'] ?? null) === 'left') data-align="left" @endif
                                @if(isset($col['width'])) style="width:{{ $col['width'] }};" @endif
                            >
                                {{ $col['label'] }}
                                @if($col['sortable'] ?? false)<span class="ua-sort-icon">⇅</span>@endif
                            </th>
                        @endforeach
                        <th style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="{{ $id }}Body"></tbody>
            </table>
        </div>
        <div class="ua-table-footer">
            <span style="font-size:12px; color:#a0aec0;" id="{{ $id }}PageInfo">–</span>
            <div class="ua-pagination" id="{{ $id }}Pagination"></div>
        </div>  
    </div>
</div>  