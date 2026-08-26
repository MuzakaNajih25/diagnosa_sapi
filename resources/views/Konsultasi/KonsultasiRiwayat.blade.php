@extends('layouts.app')

@section('title', 'Riwayat Konsultasi')

@section('content')

    <style>
        /* Tombol "Mulai Konsultasi" di header card */
        .btn-mulai {
            display: inline-flex;
            align-items: center;
            gap: 0;
            background: #2563eb;
            color: #fff !important;
            text-decoration: none !important;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            border: none;
            transition: background 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
        }
        .btn-mulai:hover {
            background: #1d4ed8;
            box-shadow: 0 6px 14px -4px rgba(29,78,216,0.45);
            text-decoration: none !important;
        }
        .btn-mulai:active {
            transform: scale(0.97);
        }

        /* Card riwayat konsultasi */
        .konsul-card {
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            display: flex;
            flex-direction: column;
            transition: transform 0.22s ease, box-shadow 0.22s ease;
        }
        .konsul-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 14px 28px -10px rgba(0,0,0,0.22);
        }

        .konsul-body {
            padding: 16px;
            flex: 1;
            position: relative;
            min-height: 70px;
        }

        /* Tombol "mata" untuk lihat hasil */
        .btn-eye {
            position: absolute;
            right: 16px;
            bottom: 16px;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: #6b7280;
            text-decoration: none;
            border: 1px solid #e5e7eb;
            box-shadow: 0 3px 8px -2px rgba(0,0,0,0.12);
            transition: width 0.28s ease, border-radius 0.28s ease, background 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
            overflow: hidden;
            white-space: nowrap;
        }
        .btn-eye i {
            font-size: 17px;
            flex-shrink: 0;
        }
        .btn-eye span {
            font-size: 13px;
            font-weight: 600;
            color: #4b5563;
            max-width: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-width 0.28s ease, opacity 0.2s ease, margin 0.28s ease;
        }
        .btn-eye:hover {
            width: 140px;
            border-radius: 24px;
            background: #f9fafb;
            color: #374151;
            box-shadow: 0 6px 16px -3px rgba(0,0,0,0.16);
        }
        .btn-eye:hover span {
            max-width: 100px;
            opacity: 1;
            margin-left: 8px;
        }
        .btn-eye:active {
            transform: scale(0.94);
        }
    </style>

    @if (session('success'))
        <div style="background:#e7f9ee;border:1px solid #22c55e;color:#166534;padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:13.5px;">
            <i class="ti ti-check"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="card-title">Riwayat Konsultasi</div>
            <a href="{{ route('konsultasi.baru') }}" class="btn-mulai">
                Mulai Konsultasi
            </a>
        </div>

        <div style="padding:16px;">
            @if (count($riwayat) === 0)
                <div style="padding:40px 8px;text-align:center;color:var(--text-secondary);">
                    Belum ada riwayat konsultasi.
                </div>
            @else
                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));gap:16px;">
                    @foreach ($riwayat as $item)
                        @php
                            // Tentukan warna berdasarkan jumlah penyakit yang ditemukan
                            $jumlah = $item->jumlah_diagnosa ?? 0;

                            if ($jumlah == 0) {
                                // Tidak ada hasil - abu netral
                                $gradFrom = '#6b7280'; $gradTo = '#4b5563';
                                $badgeBg  = '#f3f4f6'; $badgeColor = '#4b5563';
                                $label = 'Tidak ada hasil';
                            } elseif ($jumlah <= 2) {
                                // Sedikit - hijau
                                $gradFrom = '#22c55e'; $gradTo = '#15803d';
                                $badgeBg  = '#e7f9ee'; $badgeColor = '#166534';
                                $label = $jumlah . ' penyakit';
                            } elseif ($jumlah <= 5) {
                                // Sedang - kuning/oranye
                                $gradFrom = '#f59e0b'; $gradTo = '#b45309';
                                $badgeBg  = '#fef3c7'; $badgeColor = '#92400e';
                                $label = $jumlah . ' penyakit';
                            } else {
                                // Banyak - merah
                                $gradFrom = '#ef4444'; $gradTo = '#b91c1c';
                                $badgeBg  = '#fee2e2'; $badgeColor = '#991b1b';
                                $label = $jumlah . ' penyakit';
                            }
                        @endphp

                        <div class="konsul-card">

                            {{-- Header gradient --}}
                            <div style="background:linear-gradient(135deg, {{ $gradFrom }}, {{ $gradTo }});padding:18px;">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                    <div>
                                        <div style="color:#fff;font-weight:700;font-size:15.5px;">
                                            {{ $item->nama_pasien ?? 'Pasien' }}
                                        </div>
                                        <div style="color:rgba(255,255,255,0.85);font-size:12.5px;margin-top:2px;">
                                            {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y, H:i') }}
                                        </div>
                                    </div>
                                    <span style="background:rgba(255,255,255,0.18);color:#fff;font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;white-space:nowrap;">
                                        ID {{ $item->id }}
                                    </span>
                                </div>
                            </div>

                            {{-- Body --}}
                            <div class="konsul-body">
                                <span style="background:{{ $badgeBg }};color:{{ $badgeColor }};padding:4px 12px;border-radius:20px;font-size:12.5px;font-weight:600;">
                                    {{ $label }}
                                </span>

                                <a href="{{ route('konsultasi.hasil', $item->id) }}" class="btn-eye">
                                    <i class="ti ti-eye"></i>
                                    <span>Lihat Hasil</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

@endsection