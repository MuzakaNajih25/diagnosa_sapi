@extends('layouts.app')

@section('title', 'User Access Management')

@push('styles')
<style>
    /* Reset & Base */
    button, input, select {
        box-shadow: none !important;
    }
    button:focus, input:focus, select:focus, button:active {
        box-shadow: none !important; outline: none !important;
    }

    .ua-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        padding: 20px 22px;
        margin-bottom: 18px;
    }

    /* Top bar: search combo + add button */
    .ua-topbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }

    .ua-search-combo {
        display: flex; align-items: center;
        border: 1px solid #e2e8f0; border-radius: 8px;
        background: #fff; flex: 1; min-width: 240px; max-width: 520px;
        overflow: hidden;
    }
    .ua-search-combo .ua-search-icon {
        padding-left: 14px; color: #9ca3af; font-size: 15px;
        display: flex; align-items: center;
    }
    .ua-search-combo input {
        border: none; outline: none; font-size: 13.5px;
        padding: 10px 12px; font-family: inherit; flex: 1;
        background: transparent; color: #374151;
    }
    .ua-filter-toggle {
        width: 42px; height: 42px; border: none; flex-shrink: 0;
        background: #3b82f6; color: #fff; cursor: pointer; font-size: 19px;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.15s;
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

    /* Filter panel (collapsible) */
    .ua-filter-panel {
        max-height: 0; overflow: hidden;
        transition: max-height 0.25s ease, margin-top 0.25s ease;
        margin-top: 0;
    }
    .ua-filter-panel.open { max-height: 260px; margin-top: 18px; }

    .ua-filter-grid {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px 16px;
        padding-top: 16px; border-top: 1px solid #eef0f2;
    }
    .ua-form-label {
        font-size: 12px; font-weight: 600; color: #6b7280;
        margin-bottom: 6px; display: block;
    }
    .ua-form-select, .ua-form-input {
        width: 100%; padding: 9px 11px; border: 1px solid #e2e8f0; border-radius: 8px;
        font-size: 13px; font-family: inherit; color: #374151; background: #fff;
        outline: none; transition: border-color 0.15s;
    }
    .ua-form-select:focus, .ua-form-input:focus { border-color: #3b82f6; }

    .ua-filter-actions {
        grid-column: 1 / -1;
        display: flex; justify-content: flex-end; gap: 8px; margin-top: 2px;
    }
    .ua-btn-search {
        width: 42px; height: 42px; padding: 0; border-radius: 8px; border: none;
        background: #3b82f6; color: #fff; font-size: 19px;
        font-family: inherit; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
        transition: background 0.15s;
    }
    .ua-btn-search:hover { background: #2563eb; }
    .ua-btn-clear {
        width: 42px; height: 42px; padding: 0; border-radius: 8px; border: none;
        background: #ef4444; color: #fff; font-size: 19px;
        font-family: inherit; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
        transition: background 0.15s;
    }
    .ua-btn-clear:hover { background: #dc2626; }

    /* Info row */
    .ua-info-row {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 8px; margin-top: 18px; margin-bottom: 0;
    }
    .ua-info-text { font-size: 12.5px; color: #9ca3af; display: flex; align-items: center; gap: 8px; }

    /* Table (now nested inside .ua-card, full-bleed to the card edges) */
    .ua-table-wrap {
        margin: 18px -22px -20px -22px;
        border-top: 1px solid #eef0f2;
        overflow: hidden;
        border-radius: 0 0 10px 10px;
        background: #fff;
    }
    .ua-data-table { width: 100%; border-collapse: collapse; font-size: 13.5px; font-family: inherit; }

    .ua-data-table thead th {
        padding: 13px 16px; font-size: 12.5px; font-weight: 700; color: #6b7280;
        background: #f9fafb;
        border-bottom: 1px solid #eef0f2;
        border-right: 1px solid #eef0f2;
        text-align: center; white-space: nowrap;
        letter-spacing: 0.2px;
    }
    .ua-data-table thead th:last-child { border-right: none; }
    .ua-data-table thead th:first-child,
    .ua-data-table thead th:nth-child(2),
    .ua-data-table thead th:nth-child(3) { text-align: left; }

    .ua-data-table thead th.sortable {
        cursor: pointer; user-select: none; position: relative;
    }
    .ua-data-table thead th.sortable:hover { background: #f3f4f6; }
    .ua-sort-icon { margin-left: 5px; font-size: 11px; color: #c4c9d0; }
    .ua-data-table thead th.sort-asc .ua-sort-icon,
    .ua-data-table thead th.sort-desc .ua-sort-icon { color: #3b82f6; }

    .ua-data-table tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid #eef0f2;
        border-right: 1px solid #eef0f2;
        vertical-align: middle; text-align: center; color: #4b5563;
    }
    .ua-data-table tbody td:last-child { border-right: none; }
    .ua-data-table tbody td:first-child,
    .ua-data-table tbody td:nth-child(2),
    .ua-data-table tbody td:nth-child(3) { text-align: left; }

    .ua-data-table tbody tr:nth-child(even) { background: #fbfcfd; }
    .ua-data-table tbody tr:hover { background: #f3f7ff; }
    .ua-data-table tbody tr:last-child td { border-bottom: none; }

    .ua-cell-text { font-size: 13.5px; color: #4b5563; }

    /* Pills */
    .ua-role-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700;
    }
    .ua-status-badge {
        display: inline-flex; align-items: center;
        padding: 3px 11px; border-radius: 999px; font-size: 11px; font-weight: 700;
        letter-spacing: 0.3px; text-transform: uppercase; color: #fff;
    }
    .ua-status-badge.active   { background: #0d9488; }
    .ua-status-badge.inactive { background: #ef4444; }

    /* Action buttons */
    .ua-action-group { display: inline-flex; align-items: center; justify-content: center; gap: 12px; }
    .ua-action-btn {
        display: inline-flex; align-items: center; justify-content: center;
        background: transparent; border: none; padding: 0;
        cursor: pointer; font-size: 21px; color: #a0aec0;
        transition: color 0.15s ease;
    }
    .ua-action-btn.edit:hover { color: #3b82f6; }
    .ua-action-btn.delete:hover { color: #ef4444; }

    /* Table footer / pagination */
    .ua-table-footer {
        padding: 12px 16px; border-top: 1px solid #eef0f2;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px; background: #fff;
    }
    .ua-pagination { display: flex; align-items: center; gap: 4px; }
    .ua-page-btn {
        min-width: 32px; height: 32px; padding: 0 8px;
        border: 1px solid #e2e8f0; border-radius: 6px; background: #fff;
        font-size: 13px; cursor: pointer; display: flex; align-items: center;
        justify-content: center; color: #718096; transition: all 0.15s; font-family: inherit;
    }
    .ua-page-btn:hover:not(:disabled) { border-color: #3b82f6; color: #3b82f6; }
    .ua-page-btn.active { background: #3b82f6; border-color: #3b82f6; color: #fff; font-weight: 600; }
    .ua-page-btn:disabled { opacity: 0.35; cursor: not-allowed; }

    /* Modals */
    .ua-modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.4);
        z-index: 999; display: none; align-items: center; justify-content: center;
        backdrop-filter: blur(2px);
    }
    .ua-modal-overlay.show { display: flex; }
    .ua-modal-box {
        background: #fff; border-radius: 10px; width: 100%; max-width: 500px; margin: 20px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
        overflow: hidden;
        animation: ua-modal-in 0.2s ease;
    }
    @keyframes ua-modal-in {
        from { opacity: 0; transform: translateY(8px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .ua-modal-header {
        padding: 16px 20px; border-bottom: 1px solid #eef0f2;
        display: flex; align-items: center; justify-content: space-between;
    }
    .ua-modal-title { font-size: 15px; font-weight: 700; color: #1f2937; }
    .ua-modal-close {
        width: 28px; height: 28px; border-radius: 6px; border: none;
        background: #f7fafc; cursor: pointer; display: flex; align-items: center;
        justify-content: center; color: #a0aec0; font-size: 15px; transition: background 0.15s;
    }
    .ua-modal-close:hover { background: #edf2f7; }
    .ua-modal-body  { padding: 20px; }
    .ua-modal-footer {
        padding: 14px 20px; border-top: 1px solid #eef0f2;
        display: flex; justify-content: flex-end; gap: 8px; background: #f9fafb;
    }

    .ua-role-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; }
    .ua-role-card {
        border: 2px solid #e2e8f0; border-radius: 8px; padding: 13px 10px;
        text-align: center; cursor: pointer; transition: all 0.15s;
    }
    .ua-role-card:hover { border-color: #3b82f6; background: #eff6ff; }
    .ua-role-card.selected { border-color: #3b82f6; background: #eff6ff; }
    .ua-role-card i { font-size: 22px; display: block; margin-bottom: 6px; }
    .ua-role-card .rn { font-size: 13px; font-weight: 700; }
    .ua-role-card .rl { font-size: 11px; color: #a0aec0; margin-top: 2px; }
    .ua-role-card.user   i { color: #718096; }
    .ua-role-card.doctor i { color: #0d9488; }
    .ua-role-card.admin  i { color: #3b82f6; }

    .ua-btn-secondary {
        padding: 8px 16px; border-radius: 8px; border: 1px solid #e2e8f0;
        background: #fff; color: #4a5568; font-size: 13px; font-family: inherit; cursor: pointer;
        transition: background 0.15s;
    }
    .ua-btn-secondary:hover { background: #f7fafc; }
    .ua-btn-primary {
        display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px;
        border-radius: 8px; border: none; background: #3b82f6; color: #fff;
        font-size: 13px; font-weight: 600; font-family: inherit; cursor: pointer;
        transition: background 0.15s;
    }
    .ua-btn-primary:hover { background: #2563eb; }
    .ua-btn-danger {
        display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;
        border-radius: 8px; border: none; background: #ef4444; color: #fff;
        font-size: 13px; font-weight: 600; font-family: inherit; cursor: pointer;
        transition: background 0.15s;
    }
    .ua-btn-danger:hover { background: #dc2626; }

    /* Toast */
    .ua-toast {
        position: fixed; bottom: 24px; right: 24px; padding: 11px 18px;
        border-radius: 8px; font-size: 13.5px; font-weight: 500;
        display: flex; align-items: center; gap: 9px; z-index: 9999;
        transform: translateY(70px); opacity: 0; min-width: 210px;
        transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    }
    .ua-toast.show { transform: translateY(0); opacity: 1; }
    .ua-toast.success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    .ua-toast.error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

    /* ============================================================
       ALERT MODAL (delete confirm & success/error) — ring-icon style
       ============================================================ */
    .ua-alert-box {
        background: #fff; border-radius: 18px; width: 100%; max-width: 380px; margin: 20px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
        overflow: hidden; animation: ua-modal-in 0.2s ease;
        padding: 32px 26px 26px; text-align: center;
    }
    .ua-alert-ring {
        width: 74px; height: 74px; border-radius: 50%;
        border: 3px solid #f59e0b;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 18px; font-size: 30px; color: #f59e0b;
    }
    .ua-alert-ring.success { border-color: #10b981; color: #10b981; }
    .ua-alert-ring.error   { border-color: #ef4444; color: #ef4444; }
    .ua-alert-title {
        font-size: 19px; font-weight: 700; color: #1f2937; margin: 0 0 8px;
    }
    .ua-alert-text {
        font-size: 14.5px; color: #6b7280; line-height: 1.55; margin: 0;
    }
    .ua-alert-actions {
        display: flex; justify-content: center; gap: 10px; margin-top: 24px;
    }
    .ua-alert-btn {
        border: none; border-radius: 10px; padding: 11px 26px;
        font-size: 14px; font-weight: 600; font-family: inherit;
        cursor: pointer; transition: filter 0.15s ease, transform 0.1s ease;
    }
    .ua-alert-btn:hover  { filter: brightness(0.93); }
    .ua-alert-btn:active { transform: scale(0.97); }
    .ua-alert-btn.cancel  { background: #eef0f4; color: #374151; }
    .ua-alert-btn.danger  { background: #e11d3f; color: #fff; }
    .ua-alert-btn.primary { background: #3b82f6; color: #fff; }

    /* Animated success checkmark (SVG circle + check draw-in, like SweetAlert2) */
    .ua-alert-ring.success { border: none; }
    .ua-check-svg { width: 74px; height: 74px; display: block; }
    .ua-check-circle {
        fill: none; stroke: #10b981; stroke-width: 3;
        stroke-dasharray: 170; stroke-dashoffset: 170;
        animation: ua-check-circle-draw 0.5s ease-out forwards;
    }
    .ua-check-mark {
        fill: none; stroke: #10b981; stroke-width: 4;
        stroke-linecap: round; stroke-linejoin: round;
        stroke-dasharray: 48; stroke-dashoffset: 48;
        animation: ua-check-mark-draw 0.35s 0.45s ease-out forwards;
    }
    @keyframes ua-check-circle-draw { to { stroke-dashoffset: 0; } }
    @keyframes ua-check-mark-draw   { to { stroke-dashoffset: 0; } }

    /* Animated error ring (X draw-in) */
    .ua-alert-ring.error { border: none; }
    .ua-x-svg { width: 74px; height: 74px; display: block; }
    .ua-x-circle {
        fill: none; stroke: #ef4444; stroke-width: 3;
        stroke-dasharray: 170; stroke-dashoffset: 170;
        animation: ua-check-circle-draw 0.5s ease-out forwards;
    }
    .ua-x-mark {
        fill: none; stroke: #ef4444; stroke-width: 4; stroke-linecap: round;
        stroke-dasharray: 30; stroke-dashoffset: 30;
        animation: ua-check-mark-draw 0.3s 0.45s ease-out forwards;
    }

    @media (max-width: 900px) {
        .ua-filter-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 560px) {
        .ua-filter-grid { grid-template-columns: 1fr; }
        .ua-btn-add { margin-left: 0; }
        .ua-table-wrap { margin: 18px -14px -20px -14px; }
    }
</style>
@endpush

@section('content')

<div class="ua-card">
    <div class="ua-topbar">
        <div class="ua-search-combo">
            <span class="ua-search-icon"><i class="ti ti-search"></i></span>
            <input type="text" id="searchInput" placeholder="Search ID, Nama, Email..." oninput="filterTable()">
            <button class="ua-filter-toggle" id="filterToggleBtn" onclick="toggleFilterPanel()" title="Filter">
                <i class="ti ti-filter"></i>
            </button>
        </div>
        <button class="ua-btn-add" onclick="openModal('add')">
            <i class="ti ti-plus"></i> Add
        </button>
    </div>

    <div class="ua-filter-panel open" id="filterPanel">
        <div class="ua-filter-grid">
            <div>
                <label class="ua-form-label">Nama</label>
                <input type="text" class="ua-form-input" id="filterNama" placeholder="Nama pengguna...">
            </div>
            <div>
                <label class="ua-form-label">Email</label>
                <input type="text" class="ua-form-input" id="filterEmail" placeholder="Email...">
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
            <div>
                <label class="ua-form-label">Status</label>
                <select class="ua-form-select" id="filterStatus">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
            <div class="ua-filter-actions">
                <button class="ua-btn-search" onclick="filterTable()" title="Cari"><i class="ti ti-search"></i></button>
                <button class="ua-btn-clear" onclick="clearFilter()" title="Reset"><i class="ti ti-eraser"></i></button>
            </div>
        </div>
    </div>

    <div class="ua-info-row">
        <span class="ua-info-text" id="tableInfo">Showing data...</span>
    </div>

    <div class="ua-table-wrap">
        <div style="overflow-x:auto;">
            <table class="ua-data-table">
                <thead>
                    <tr>
                        <th style="width:50px;">No.</th>
                        <th class="sortable" data-key="nama" onclick="sortTable('nama')">Nama Pengguna<span class="ua-sort-icon">⇅</span></th>
                        <th class="sortable" data-key="email" onclick="sortTable('email')">Email<span class="ua-sort-icon">⇅</span></th>
                        <th class="sortable" data-key="role" onclick="sortTable('role')" style="width:120px;">Role<span class="ua-sort-icon">⇅</span></th>
                        <th class="sortable" data-key="status" onclick="sortTable('status')" style="width:110px;">Status<span class="ua-sort-icon">⇅</span></th>
                        <th style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>
        <div class="ua-table-footer">
            <span style="font-size:12px; color:#a0aec0;" id="pageInfo">–</span>
            <div class="ua-pagination" id="paginationContainer"></div>
        </div>
    </div>
</div>

{{-- MODAL ADD/EDIT --}}
<div class="ua-modal-overlay" id="modalOverlay" onclick="closeModalOutside(event)">
    <div class="ua-modal-box">
        <div class="ua-modal-header">
            <span class="ua-modal-title" id="modalTitle">Tambah User Access</span>
            <button class="ua-modal-close" onclick="closeModal()"><i class="ti ti-x"></i></button>
        </div>
        <div class="ua-modal-body">
            <input type="hidden" id="editId">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="ua-form-label">Nama <span style="color:#ef4444;">*</span></label>
                    <input type="text" class="ua-form-input" id="inputNama" placeholder="Nama pengguna">
                </div>
                <div>
                    <label class="ua-form-label">Email <span style="color:#ef4444;">*</span></label>
                    <input type="email" class="ua-form-input" id="inputEmail" placeholder="email@contoh.com">
                </div>
            </div>

            <div style="margin-bottom:16px;" id="passwordField">
                <label class="ua-form-label">Password <span style="color:#ef4444;">*</span></label>
                <div style="position:relative;">
                    <input type="password" class="ua-form-input" id="inputPassword"
                        placeholder="Min. 6 karakter" style="padding-right:40px;">
                    <button type="button" onclick="togglePass()"
                        style="position:absolute;right:10px;top:50%;transform:translateY(-50%);
                        background:none;border:none;cursor:pointer;color:#a0aec0;font-size:15px;">
                        <i class="ti ti-eye" id="passEyeIcon"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="ua-form-label" style="margin-bottom:8px;">Role <span style="color:#ef4444;">*</span></label>
                <div class="ua-role-grid" id="roleGrid">
                    <div class="ua-role-card user" data-role="0" onclick="selectRole(0)">
                        <i class="ti ti-user"></i>
                        <div class="rn">Users</div><div class="rl">Level 0</div>
                    </div>
                    <div class="ua-role-card doctor" data-role="1" onclick="selectRole(1)">
                        <i class="ti ti-stethoscope"></i>
                        <div class="rn">Dokter</div><div class="rl">Level 1</div>
                    </div>
                    <div class="ua-role-card admin" data-role="2" onclick="selectRole(2)">
                        <i class="ti ti-shield-check"></i>
                        <div class="rn">Admin</div><div class="rl">Level 2</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ua-modal-footer">
            <button class="ua-btn-secondary" onclick="closeModal()">Batal</button>
            <button class="ua-btn-primary" onclick="saveData()">
                <i class="ti ti-device-floppy" style="font-size:14px;"></i> Simpan
            </button>
        </div>
    </div>
</div>

{{-- ALERT: DELETE CONFIRM --}}
<div class="ua-modal-overlay" id="deleteOverlay" onclick="closeDeleteOutside(event)">
    <div class="ua-alert-box">
        <div class="ua-alert-ring"><i class="ti ti-alert-triangle"></i></div>
        <p class="ua-alert-title">Delete User Access?</p>
        <p class="ua-alert-text" id="deleteTargetName">Are you sure? This action cannot be undone.</p>
        <div class="ua-alert-actions">
            <button class="ua-alert-btn cancel" onclick="closeDelete()">Cancel</button>
            <button class="ua-alert-btn danger" onclick="confirmDelete()">Yes, Delete</button>
        </div>
    </div>
</div>

{{-- ALERT: SUCCESS / ERROR --}}
<div class="ua-modal-overlay" id="infoAlertOverlay">
    <div class="ua-alert-box">
        <div class="ua-alert-ring" id="infoAlertRing"><i class="ti" id="infoAlertRingIcon"></i></div>
        <p class="ua-alert-title" id="infoAlertTitle">Success!</p>
        <p class="ua-alert-text" id="infoAlertMsg"></p>
        <div class="ua-alert-actions">
            <button class="ua-alert-btn primary" onclick="closeInfoAlert()">OK</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

let allData        = {!! json_encode($users) !!};
let filteredData   = [...allData];
let currentPage    = 1;
const perPage      = 5;
let selectedRole   = null;
let deleteTargetId = null;
let modalMode      = 'add';
let sortKey        = null;
let sortDir        = 'asc';

const roleConfig = {
    0: { label:'Users',  icon:'ti-user',         color:'#718096', bg:'#f7fafc' },
    1: { label:'Dokter', icon:'ti-stethoscope',  color:'#0d9488', bg:'#f0fdfa' },
    2: { label:'Admin',  icon:'ti-shield-check', color:'#3b82f6', bg:'#eff6ff' },
};

window.addEventListener('DOMContentLoaded', () => renderTable());

function toggleFilterPanel() {
    const panel = document.getElementById('filterPanel');
    const btn   = document.getElementById('filterToggleBtn');
    panel.classList.toggle('open');
    btn.classList.toggle('active');
}

function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const nama   = document.getElementById('filterNama').value.toLowerCase();
    const email  = document.getElementById('filterEmail').value.toLowerCase();
    const role   = document.getElementById('filterRole').value;
    const status = document.getElementById('filterStatus').value;

    filteredData = allData.filter(d =>
        (d.nama.toLowerCase().includes(search) || d.email.toLowerCase().includes(search)) &&
        d.nama.toLowerCase().includes(nama) &&
        d.email.toLowerCase().includes(email) &&
        (role   === '' || String(d.role) === role) &&
        (status === '' || d.status === status)
    );

    if (sortKey) {
        applySort();
    } else {
        filteredData.sort((a, b) => a.nama.localeCompare(b.nama));
    }

    currentPage = 1;
    renderTable();
}

function clearFilter() {
    document.getElementById('searchInput').value  = '';
    document.getElementById('filterNama').value   = '';
    document.getElementById('filterEmail').value  = '';
    document.getElementById('filterRole').value   = '';
    document.getElementById('filterStatus').value = '';
    filteredData = [...allData];
    currentPage  = 1;
    renderTable();
}

function sortTable(key) {
    if (sortKey === key) {
        sortDir = sortDir === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey = key;
        sortDir = 'asc';
    }
    applySort();
    currentPage = 1;
    renderTable();
    updateSortIcons();
}

function applySort() {
    filteredData.sort((a, b) => {
        let va = a[sortKey], vb = b[sortKey];
        if (typeof va === 'number' && typeof vb === 'number') {
            return sortDir === 'asc' ? va - vb : vb - va;
        }
        va = String(va).toLowerCase();
        vb = String(vb).toLowerCase();
        return sortDir === 'asc' ? va.localeCompare(vb) : vb.localeCompare(va);
    });   
}

function updateSortIcons() {
    document.querySelectorAll('.ua-data-table thead th.sortable').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
        const icon = th.querySelector('.ua-sort-icon');
        if (th.dataset.key === sortKey) {
            th.classList.add(sortDir === 'asc' ? 'sort-asc' : 'sort-desc');
            icon.textContent = sortDir === 'asc' ? '▲' : '▼';
        } else {
            icon.textContent = '⇅';
        }
    }); 
}

function renderTable() {
    const tbody    = document.getElementById('tableBody');
    const start    = (currentPage - 1) * perPage;
    const end      = start + perPage;
    const pageData = filteredData.slice(start, end);
    const total    = filteredData.length;

    document.getElementById('tableInfo').textContent = total > 0
        ? 'Showing ' + (start + 1) + ' to ' + Math.min(end, total) + ' of ' + total + ' entries'
        : 'Tidak ada data';

    if (!pageData.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:44px;color:#a0aec0;">' +
            '<i class="ti ti-inbox" style="font-size:34px;display:block;margin-bottom:10px;opacity:0.4;"></i>' +
            'Tidak ada data ditemukan</td></tr>';
        renderPagination(0);
        return;
    }

    tbody.innerHTML = pageData.map(function(d, i) {
        const rc = roleConfig[d.role] ?? roleConfig[0];
        return '<tr>' +
            '<td><span class="ua-cell-text">' + (start + i + 1) + '</span></td>' +
            '<td><span class="ua-cell-text">' + d.nama + '</span></td>' +
            '<td><span class="ua-cell-text">' + d.email + '</span></td>' +
            '<td>' +
                '<span class="ua-role-badge" style="background:' + rc.bg + ';color:' + rc.color + ';">' +
                    '<i class="ti ' + rc.icon + '" style="font-size:11px;"></i> ' + rc.label +
                '</span>' +
            '</td>' +
            '<td>' +
                (d.status === 'active'
                    ? '<span class="ua-status-badge active">Aktif</span>'
                    : '<span class="ua-status-badge inactive">Nonaktif</span>'
                ) +
            '</td>' +
            '<td>' +
                '<div class="ua-action-group">' +
                    '<button class="ua-action-btn edit" title="Edit Data" onclick="openModal(\'edit\',\'' + d.id + '\')">' +
                        '<i class="ti ti-pencil"></i>' +
                    '</button>' +
                    '<button class="ua-action-btn delete" title="Hapus Akses" onclick="openDelete(\'' + d.id + '\')">' +
                        '<i class="ti ti-trash"></i>' +
                    '</button>' +
                '</div>' +
            '</td>' +
        '</tr>';
    }).join('');

    renderPagination(total);
}

function renderPagination(total) {
    const tp  = Math.ceil(total / perPage);
    const con = document.getElementById('paginationContainer');
    document.getElementById('pageInfo').textContent = tp > 0 ? 'Halaman ' + currentPage + ' dari ' + tp : '';
    if (tp <= 1) { con.innerHTML = ''; return; }

    let html = '<button class="ua-page-btn" onclick="goPage(' + (currentPage - 1) + ')" ' + (currentPage === 1 ? 'disabled' : '') + '>' +
        '<i class="ti ti-chevron-left" style="font-size:13px;"></i></button>';
    for (let p = 1; p <= tp; p++) {
        html += '<button class="ua-page-btn' + (p === currentPage ? ' active' : '') + '" onclick="goPage(' + p + ')">' + p + '</button>';
    }
    html += '<button class="ua-page-btn" onclick="goPage(' + (currentPage + 1) + ')" ' + (currentPage === tp ? 'disabled' : '') + '>' +
        '<i class="ti ti-chevron-right" style="font-size:13px;"></i></button>';
    con.innerHTML = html;
}

function goPage(p) {
    const tp = Math.ceil(filteredData.length / perPage);
    if (p < 1 || p > tp) return;
    currentPage = p;
    renderTable();
}

function togglePass() {
    const input = document.getElementById('inputPassword');
    const icon  = document.getElementById('passEyeIcon');
    if (input.type === 'password') {
        input.type     = 'text';
        icon.className = 'ti ti-eye-off';
    } else {
        input.type     = 'password';
        icon.className = 'ti ti-eye';
    }
}

function openModal(mode, id) {
    modalMode    = mode;
    selectedRole = null;
    document.getElementById('inputNama').value     = '';
    document.getElementById('inputEmail').value    = '';
    document.getElementById('inputPassword').value = '';
    document.getElementById('passEyeIcon').className = 'ti ti-eye';
    document.getElementById('inputPassword').type    = 'password';
    document.querySelectorAll('#roleGrid .ua-role-card').forEach(function(c) {
        c.classList.remove('selected');
    });

    document.getElementById('passwordField').style.display = mode === 'add' ? 'block' : 'none';

    if (mode === 'edit' && id) {
        const d = allData.find(function(x) { return x.id === id; });
        if (!d) return;
        document.getElementById('modalTitle').textContent = 'Edit User Access';
        document.getElementById('editId').value           = d.id;
        document.getElementById('inputNama').value        = d.nama;
        document.getElementById('inputEmail').value       = d.email;
        selectRole(d.role);
    } else {
        document.getElementById('modalTitle').textContent = 'Tambah User Access';
        document.getElementById('editId').value           = '';
    }

    document.getElementById('modalOverlay').classList.add('show');
}

function closeModal()         { document.getElementById('modalOverlay').classList.remove('show'); }
function closeModalOutside(e) { if (e.target.id === 'modalOverlay') closeModal(); }

function selectRole(role) {
    selectedRole = role;
    document.querySelectorAll('#roleGrid .ua-role-card').forEach(function(c) {
        c.classList.toggle('selected', parseInt(c.dataset.role) === role);
    });
}

async function saveData() {
    const nama     = document.getElementById('inputNama').value.trim();
    const email    = document.getElementById('inputEmail').value.trim();
    const password = document.getElementById('inputPassword').value.trim();
    const editId   = document.getElementById('editId').value;
    const isEdit   = !!editId;

    if (!nama || !email || selectedRole === null) {
        showInfoAlert('error', 'Nama, email, dan role wajib diisi!');
        return;
    }

    if (!isEdit && !password) {
        showInfoAlert('error', 'Password wajib diisi!');
        return;
    }

    if (!isEdit && password.length < 6) {
        showInfoAlert('error', 'Password minimal 6 karakter!');
        return;
    }

    const url    = isEdit ? '/administrator/useraccess/' + editId : '/administrator/useraccess';
    const method = isEdit ? 'PUT' : 'POST';
    const body   = isEdit
        ? { nama, email, role: selectedRole }
        : { nama, email, password, role: selectedRole };

    try {
        const res  = await fetch(url, {
            method  : method,
            headers : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body    : JSON.stringify(body),
        });
        const data = await res.json();

        if (!data.success) { showInfoAlert('error', data.message ?? 'Gagal menyimpan!'); return; }

        if (isEdit) {
            const idx = allData.findIndex(function(x) { return x.id === editId; });
            if (idx !== -1) allData[idx] = Object.assign({}, allData[idx], { nama, email, role: selectedRole });
        } else {
            allData.push(data.user);
        }

        filteredData = [...allData];
        filterTable();
        closeModal();
        showInfoAlert('success', data.message);
    } catch(e) {
        showInfoAlert('error', 'Terjadi kesalahan!');
    }
}

function openDelete(id) {
    deleteTargetId = id;
    const d = allData.find(function(x) { return x.id === id; });
    document.getElementById('deleteTargetName').textContent = d
        ? 'Yakin ingin menghapus akses ' + d.nama + ' (' + d.email + ')? Tindakan ini tidak dapat dibatalkan.'
        : 'Are you sure? This action cannot be undone.';
    document.getElementById('deleteOverlay').classList.add('show');
}

function closeDelete()         { document.getElementById('deleteOverlay').classList.remove('show'); }
function closeDeleteOutside(e) { if (e.target.id === 'deleteOverlay') closeDelete(); }

async function confirmDelete() {
    try {
        const res  = await fetch('/administrator/useraccess/' + deleteTargetId, {
            method  : 'DELETE',
            headers : { 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();

        if (!data.success) { closeDelete(); showInfoAlert('error', 'Gagal menghapus!'); return; }

        allData      = allData.filter(function(x) { return x.id !== deleteTargetId; });
        filteredData = [...allData];
        filterTable();
        closeDelete();
        showInfoAlert('success', data.message);
    } catch(e) {
        closeDelete();
        showInfoAlert('error', 'Terjadi kesalahan!');
    }
}

function showInfoAlert(type, msg) {
    const ring = document.getElementById('infoAlertRing');
    if (type === 'success') {
        ring.className = 'ua-alert-ring success';
        ring.innerHTML = '<svg class="ua-check-svg" viewBox="0 0 52 52">' +
            '<circle class="ua-check-circle" cx="26" cy="26" r="23"/>' +
            '<path class="ua-check-mark" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>' +
            '</svg>';
    } else {
        ring.className = 'ua-alert-ring error';
        ring.innerHTML = '<svg class="ua-x-svg" viewBox="0 0 52 52">' +
            '<circle class="ua-x-circle" cx="26" cy="26" r="23"/>' +
            '<path class="ua-x-mark" d="M17 17l18 18"/>' +
            '<path class="ua-x-mark" d="M35 17l-18 18"/>' +
            '</svg>';
    }
    document.getElementById('infoAlertTitle').textContent = type === 'success' ? 'Success!' : 'Error!';
    document.getElementById('infoAlertMsg').textContent   = msg;
    document.getElementById('infoAlertOverlay').classList.add('show');
}

function closeInfoAlert() {
    document.getElementById('infoAlertOverlay').classList.remove('show');
}
</script>
@endpush 