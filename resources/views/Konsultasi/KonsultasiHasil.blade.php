@extends('layouts.app')

@section('title', 'Hasil Diagnosa')

@section('content')

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Hasil Diagnosa</div>
                <div style="font-size:12.5px;color:var(--text-secondary);margin-top:4px;">
                    {{ $konsultasi->nama_pasien ?? 'Tanpa nama' }} &middot; {{ \Carbon\Carbon::parse($konsultasi->created_at)->format('d M Y, H:i') }}
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                <x-button type="save" icon="ti-plus" label="Konsultasi Baru"
                    onclick="window.location='{{ route('konsultasi.baru') }}'" />
            </div>
        </div>

        @forelse ($hasil as $index => $item)

            @php
                $persen = round($item->nilai_cf * 100, 1);
                $skorTertinggi = round(($hasil->first()->nilai_cf ?? 0) * 100, 1);
                $isTop = $persen == $skorTertinggi;

                if ($persen >= 70) {
                    $label = 'Keyakinan Tinggi';
                    $warna = ['bar' => '#818cf8', 'grad1' => '#4f46e5', 'grad2' => '#818cf8', 'badgeBg' => '#eef2ff', 'badgeColor' => '#4338ca'];
                } elseif ($persen >= 40) {
                    $label = 'Keyakinan Sedang';
                    $warna = ['bar' => '#7dd3fc', 'grad1' => '#0284c7', 'grad2' => '#7dd3fc', 'badgeBg' => '#e0f2fe', 'badgeColor' => '#0369a1'];
                } else {
                    $label = 'Keyakinan Rendah';
                    $warna = ['bar' => '#cbd5e1', 'grad1' => '#64748b', 'grad2' => '#cbd5e1', 'badgeBg' => '#f1f5f9', 'badgeColor' => '#475569'];
                }
            @endphp

            @if ($isTop)
                {{-- CARD BESAR UNTUK RANK TERATAS (lengkap dengan deskripsi & solusi) --}}
                <div style="border-radius:14px;overflow:hidden;margin-bottom:14px;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,0.06);">
                    <div style="background:linear-gradient(135deg,{{ $warna['grad1'] }},{{ $warna['grad2'] }});padding:20px 22px;position:relative;overflow:hidden;">
                        <i class="ti ti-stethoscope" style="position:absolute;right:-10px;top:-10px;font-size:110px;color:rgba(255,255,255,0.12);"></i>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;position:relative;z-index:1;">
                            <div>
                                <span style="background:rgba(255,255,255,0.22);color:#fff;font-size:10.5px;font-weight:600;letter-spacing:0.4px;text-transform:uppercase;padding:4px 12px;border-radius:20px;">
                                    Hasil Utama &middot; {{ $label }}
                                </span>
                                <div style="font-size:21px;font-weight:700;letter-spacing:-0.2px;color:#fff;margin-top:12px;">
                                    {{ $item->nm_penyakit }}
                                </div>
                                <div style="font-size:12px;font-weight:500;color:rgba(255,255,255,0.8);margin-top:3px;">
                                    {{ $item->code_penyakit }}
                                </div>
                            </div>
                            <div style="font-size:30px;font-weight:800;letter-spacing:-0.5px;color:#fff;">
                                {{ $persen }}%
                            </div>
                        </div>
                    </div>

                    <div style="padding:20px 22px;background:var(--card-bg,#fff);">
                        <div style="background:var(--main-bg);border-radius:6px;height:8px;overflow:hidden;margin-bottom:18px;">
                            <div style="background:{{ $warna['bar'] }};height:100%;width:{{ $persen }}%;transition:width .6s ease;"></div>
                        </div>

                        <div style="font-size:13.5px;color:var(--text-secondary);margin-bottom:12px;line-height:1.7;">
                            <span style="display:block;color:var(--text-primary);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.3px;margin-bottom:4px;">Deskripsi</span>
                            {{ $item->deskripsi ?? '-' }}
                        </div>
                        <div style="font-size:13.5px;color:var(--text-secondary);line-height:1.7;">
                            <span style="display:block;color:var(--text-primary);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.3px;margin-bottom:4px;">Solusi / Penanganan</span>
                            {{ $item->solusi ?? '-' }}
                        </div>
                    </div>
                </div>
            @else
                {{-- CARD PENDEK, HEADER GRADIENT + BODY PUTIH SAMA KAYA DI ATAS, DETAIL DI DROPDOWN --}}
                <div style="border-radius:14px;overflow:hidden;margin-bottom:10px;border:1px solid var(--border);">
                    <div style="background:linear-gradient(135deg,{{ $warna['grad1'] }},{{ $warna['grad2'] }});padding:12px 18px;position:relative;overflow:hidden;">
                        <i class="ti ti-stethoscope" style="position:absolute;right:-14px;top:-14px;font-size:70px;color:rgba(255,255,255,0.12);"></i>
                        <div style="display:flex;justify-content:space-between;align-items:center;position:relative;z-index:1;">
                            <div>
                                <div style="font-size:14.5px;font-weight:700;letter-spacing:-0.1px;color:#fff;">
                                    {{ $item->nm_penyakit }}
                                </div>
                                <div style="font-size:11px;font-weight:500;color:rgba(255,255,255,0.8);margin-top:2px;">
                                    {{ $item->code_penyakit }} &middot; {{ $label }}
                                </div>
                            </div>
                            <div style="font-size:19px;font-weight:800;letter-spacing:-0.3px;color:#fff;">
                                {{ $persen }}%
                            </div>
                        </div>
                    </div>

                    <div style="padding:12px 18px;background:var(--card-bg,#fff);">
                        <div style="background:var(--main-bg);border-radius:6px;height:6px;overflow:hidden;">
                            <div style="background:{{ $warna['bar'] }};height:100%;width:{{ $persen }}%;"></div>
                        </div>

                        <button type="button"
                            onclick="const b=this.nextElementSibling;const open=b.style.display!=='none';b.style.display=open?'none':'block';this.querySelector('.chev-{{ $index }}').style.transform=open?'rotate(0deg)':'rotate(180deg)';"
                            style="width:100%;display:flex;justify-content:space-between;align-items:center;background:none;border:none;padding:10px 0 0;cursor:pointer;color:var(--text-secondary);font-size:12px;font-weight:500;">
                            <span>Lihat deskripsi & penanganan</span>
                            <i class="ti ti-chevron-down chev-{{ $index }}" style="transition:transform .15s;font-size:16px;"></i>
                        </button>

                        <div style="display:none;padding-top:10px;margin-top:6px;border-top:1px dashed var(--border);">
                            <div style="font-size:13px;color:var(--text-secondary);margin-bottom:9px;line-height:1.65;">
                                <span style="display:block;color:var(--text-primary);font-weight:600;font-size:11.5px;text-transform:uppercase;letter-spacing:0.3px;margin-bottom:3px;">Deskripsi</span>
                                {{ $item->deskripsi ?? '-' }}
                            </div>
                            <div style="font-size:13px;color:var(--text-secondary);line-height:1.65;">
                                <span style="display:block;color:var(--text-primary);font-weight:600;font-size:11.5px;text-transform:uppercase;letter-spacing:0.3px;margin-bottom:3px;">Penanganan</span>
                                {{ $item->solusi ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        @empty
            <div style="padding:32px 8px;text-align:center;color:var(--text-secondary);">
                Tidak ditemukan penyakit yang cocok berdasarkan gejala yang dipilih.
            </div>
        @endforelse
    </div>

@endsection