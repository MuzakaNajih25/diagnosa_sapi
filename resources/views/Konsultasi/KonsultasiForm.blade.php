@extends('layouts.app')

@section('title', 'Konsultasi Baru')

@section('content')

    @if (session('error'))
        <div style="background:#fdecea;border:1px solid #f5b5ae;color:#a12f27;padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:13.5px;">
            <i class="ti ti-alert-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Card: form pertanyaan, scroll normal seperti biasa --}}
    <div id="page-content" class="card">
        <div class="card-header">
            <div class="card-title">Form Konsultasi Gejala</div>
        </div>

        @if ($gejala->isEmpty())
            <p style="padding:24px 8px;text-align:center;color:var(--text-secondary);">
                Belum ada data gejala. Tambahkan gejala dulu di menu Manajemen Gejala.
            </p>
        @else

            <p style="font-size:13px;color:var(--text-secondary);margin-bottom:18px;">
                Jawab sesuai kondisi sapi yang kamu amati ya. Kalau sapinya nggak menunjukkan gejala itu, biarkan aja di pilihan "Tidak".
            </p>

            <form action="{{ route('konsultasi.proses') }}" method="POST" id="konsultasi-form">
                @csrf

                @php $opsi = ['0' => 'Tidak', '0.2' => 'Tidak Tahu', '0.4' => 'Mungkin', '0.6' => 'Kemungkinan Besar', '0.8' => 'Hampir Pasti', '1' => 'Pasti']; @endphp

                @foreach ($gejala as $i => $item)
                    <div class="gform-card" data-question data-touched="false">
                        <div class="gform-question">
                            <span class="gform-index">{{ $i + 1 }}.</span> Apakah sapinya mengalami {{ $item->nm_gejala }}?
                        </div>

                        <div class="gform-options">
                            @foreach ($opsi as $val => $lbl)
                                <label class="gform-option">
                                    <input
                                        type="radio"
                                        name="cf[{{ $item->id }}]"
                                        value="{{ $val }}"
                                        data-question-input
                                        {{ $val === '0' ? 'checked' : '' }}
                                    >
                                    <span class="gform-radio"></span>
                                    <span>{{ $lbl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div style="margin-top:20px;display:flex;justify-content:flex-end;gap:10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-stethoscope"></i> Proses Diagnosa
                    </button>
                </div>
            </form>

            {{-- Widget progress mengambang di pojok kanan bawah --}}
            <div id="progress-float">
                <div id="progress-label">0 dari {{ $gejala->count() }} terjawab</div>
                <div class="progress-track">
                    <div id="progress-bar" class="progress-fill" style="width:0%;"></div>
                </div>
            </div>

            {{-- Modal notifikasi manual (mandiri, tidak pakai component lain) --}}
            <div id="kf-confirm-overlay" onclick="if(event.target.id==='kf-confirm-overlay') kfCloseConfirm();">
                <div class="kf-confirm-box">
                    <div class="kf-confirm-ring"><i class="ti ti-alert-triangle"></i></div>
                    <p class="kf-confirm-title">Pertanyaan belum sepenuhnya terjawab</p>
                    <p class="kf-confirm-text" id="kf-confirm-text"></p>
                    <div class="kf-confirm-actions">
                        <button type="button" class="kf-confirm-btn cancel" onclick="kfCloseConfirm()">Oke, Saya Cek</button>
                    </div>
                </div>
            </div>

            <style>
                .gform-card {
                    background: var(--bg, #fff);
                    border: 1px solid var(--border);
                    border-radius: 12px;
                    padding: 20px 22px;
                    margin-bottom: 14px;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
                    transition: border-color .15s ease;
                }
                .gform-card:focus-within {
                    border-color: var(--primary, #2563eb);
                    box-shadow: 0 0 0 3px rgba(37,99,235,0.08);
                }
                .gform-card.unanswered {
                    border-color: #ef4444;
                    box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
                }
                .gform-question {
                    font-size: 14.5px;
                    font-weight: 600;
                    margin-bottom: 14px;
                    line-height: 1.4;
                }
                .gform-index {
                    color: var(--text-secondary);
                    font-weight: 500;
                    margin-right: 2px;
                }
                .gform-options {
                    display: flex;
                    flex-direction: column;
                    gap: 4px;
                }
                .gform-option {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    padding: 9px 10px;
                    border-radius: 8px;
                    font-size: 13.5px;
                    cursor: pointer;
                    color: var(--text-secondary);
                }
                .gform-option:hover {
                    background: rgba(37,99,235,0.05);
                }
                .gform-option input {
                    position: absolute;
                    opacity: 0;
                    width: 0;
                    height: 0;
                }
                .gform-radio {
                    width: 18px;
                    height: 18px;
                    border-radius: 50%;
                    border: 2px solid var(--border);
                    flex-shrink: 0;
                    position: relative;
                    transition: border-color .15s ease;
                }
                .gform-option input:checked + .gform-radio {
                    border-color: var(--primary, #2563eb);
                }
                .gform-option input:checked + .gform-radio::after {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    width: 9px;
                    height: 9px;
                    border-radius: 50%;
                    background: var(--primary, #2563eb);
                    transform: translate(-50%, -50%);
                }
                .gform-option input:checked ~ span:last-child {
                    color: var(--text-primary, #111);
                    font-weight: 600;
                }

                /* ---------- Widget progress mengambang ---------- */
                #progress-float {
                    position: fixed;
                    right: 22px;
                    bottom: 96px;
                    width: 190px;
                    background: var(--bg, #fff);
                    border: 1px solid var(--border);
                    border-radius: 12px;
                    padding: 12px 14px;
                    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
                    z-index: 999;
                }
                #progress-label {
                    font-size: 12px;
                    color: var(--text-secondary);
                    margin-bottom: 8px;
                }
                .progress-track {
                    background: var(--border);
                    border-radius: 999px;
                    height: 10px;
                    overflow: hidden;
                }
                .progress-fill {
                    height: 100%;
                    border-radius: 999px;
                    background-image:
                        repeating-linear-gradient(45deg, rgba(255,255,255,0.35) 0 6px, transparent 6px 14px),
                        linear-gradient(90deg, var(--sidebar-bg, #1d4ed8), var(--sidebar-accent, #60a5fa));
                    background-size: 26px 26px, 100% 100%;
                    animation: progress-stripe 0.9s linear infinite;
                    transition: width .25s ease;
                }
                @keyframes progress-stripe {
                    from { background-position: 0 0, 0 0; }
                    to { background-position: 26px 0, 0 0; }
                }

                /* ---------- Modal konfirmasi manual ---------- */
                #kf-confirm-overlay {
                    position: fixed;
                    inset: 0;
                    background: rgba(0,0,0,0.4);
                    z-index: 9998;
                    display: none;
                    align-items: center;
                    justify-content: center;
                    backdrop-filter: blur(2px);
                }
                #kf-confirm-overlay.show { display: flex; }
                .kf-confirm-box {
                    background: #fff;
                    border-radius: 18px;
                    width: 100%;
                    max-width: 380px;
                    margin: 20px;
                    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.1);
                    padding: 32px 26px 26px;
                    text-align: center;
                }
                .kf-confirm-ring {
                    width: 74px;
                    height: 74px;
                    border-radius: 50%;
                    border: 3px solid #f59e0b;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 18px;
                    font-size: 30px;
                    color: #f59e0b;
                }
                .kf-confirm-title {
                    font-size: 18px;
                    font-weight: 700;
                    color: #1f2937;
                    margin: 0 0 8px;
                }
                .kf-confirm-text {
                    font-size: 14px;
                    color: #6b7280;
                    line-height: 1.55;
                    margin: 0;
                }
                .kf-confirm-actions {
                    display: flex;
                    justify-content: center;
                    gap: 10px;
                    margin-top: 24px;
                }
                .kf-confirm-btn {
                    border: none;
                    border-radius: 10px;
                    padding: 11px 24px;
                    font-size: 14px;
                    font-weight: 600;
                    font-family: inherit;
                    cursor: pointer;
                    transition: filter .15s ease, transform .1s ease;
                }
                .kf-confirm-btn:hover { filter: brightness(0.93); }
                .kf-confirm-btn:active { transform: scale(0.97); }
                .kf-confirm-btn.cancel { background: #eef0f4; color: #374151; }
                .kf-confirm-btn.danger { background: #e11d3f; color: #fff; }
            </style>

            <script>
                (function () {
                    const form = document.getElementById('konsultasi-form');
                    const cards = document.querySelectorAll('[data-question]');
                    const totalQuestions = cards.length;
                    const bar = document.getElementById('progress-bar');
                    const label = document.getElementById('progress-label');

                    function countTouched() {
                        let touched = 0;
                        cards.forEach(card => {
                            if (card.dataset.touched === 'true') touched++;
                        });
                        return touched;
                    }

                    function updateProgress() {
                        const touched = countTouched();
                        const pct = totalQuestions === 0 ? 0 : Math.round((touched / totalQuestions) * 100);
                        bar.style.width = pct + '%';
                        label.textContent = `${touched} dari ${totalQuestions} terjawab`;
                    }

                    cards.forEach(card => {
                        card.querySelectorAll('[data-question-input]').forEach(inp => {
                            inp.addEventListener('change', function () {
                                card.dataset.touched = 'true';
                                card.classList.remove('unanswered');
                                updateProgress();
                            });
                        });
                    });

                    updateProgress();

                    window.kfCloseConfirm = function () {
                        document.getElementById('kf-confirm-overlay').classList.remove('show');
                    };

                    form.addEventListener('submit', function (e) {
                        const belumDijawab = Array.from(cards).filter(c => c.dataset.touched !== 'true');

                        if (belumDijawab.length > 0) {
                            e.preventDefault();

                            belumDijawab.forEach(c => c.classList.add('unanswered'));
                            belumDijawab[0].scrollIntoView({ behavior: 'smooth', block: 'center' });

                            document.getElementById('kf-confirm-text').textContent =
                                `Masih ada ${belumDijawab.length} pertanyaan yang belum kamu tinjau (masih pada nilai default). Cek dan jawab dulu ya sebelum lanjut.`;
                            document.getElementById('kf-confirm-overlay').classList.add('show');
                        }
                    });
                })();
            </script>

        @endif
    </div>

@endsection