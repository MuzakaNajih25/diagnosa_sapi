@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="grid-3" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;">
            <i class="ti ti-users"></i>
        </div>
        <div>
            <div class="stat-label">Total Pakar</div>
            <div class="stat-value">24</div>
            <div class="stat-sub">↑ 3 bulan ini</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dbeafe;color:#1d4ed8;">
            <i class="ti ti-message-dots"></i>
        </div>
        <div>
            <div class="stat-label">Konsultasi</div>
            <div class="stat-value">128</div>
            <div class="stat-sub">↑ 12 minggu ini</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
            <i class="ti ti-check"></i>
        </div>
        <div>
            <div class="stat-label">Terselesaikan</div>
            <div class="stat-value">115</div>
            <div class="stat-sub">89.8% akurasi</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Konsultasi Terbaru</div>
        <a href="#" style="font-size:13px;color:#5b6af8;text-decoration:none;">Lihat semua →</a>
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
        <thead>
            <tr style="border-bottom:1px solid #e5e7eb;">
                <th style="text-align:left;padding:8px 12px;font-weight:600;color:#6b7280;font-size:12px;">No</th>
                <th style="text-align:left;padding:8px 12px;font-weight:600;color:#6b7280;font-size:12px;">Nama</th>
                <th style="text-align:left;padding:8px 12px;font-weight:600;color:#6b7280;font-size:12px;">Topik</th>
                <th style="text-align:left;padding:8px 12px;font-weight:600;color:#6b7280;font-size:12px;">Tanggal</th>
                <th style="text-align:left;padding:8px 12px;font-weight:600;color:#6b7280;font-size:12px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach([
                ['Budi Santoso','Diagnosa Hama Padi','18 Mei 2026','Selesai','#dcfce7','#15803d'],
                ['Siti Rahayu','Penyakit Tanaman','17 Mei 2026','Proses','#fef9c3','#854d0e'],
                ['Ahmad Fauzi','Pemupukan Optimal','17 Mei 2026','Selesai','#dcfce7','#15803d'],
                ['Dewi Lestari','Hama Wereng','16 Mei 2026','Selesai','#dcfce7','#15803d'],
            ] as $i => $row)
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:12px;">{{ $i + 1 }}</td>
                <td style="padding:12px;font-weight:500;">{{ $row[0] }}</td>
                <td style="padding:12px;color:#6b7280;">{{ $row[1] }}</td>
                <td style="padding:12px;color:#6b7280;">{{ $row[2] }}</td>
                <td style="padding:12px;">
                    <span style="background:{{ $row[4] }};color:{{ $row[5] }};padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;">{{ $row[3] }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection