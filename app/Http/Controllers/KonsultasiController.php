<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KonsultasiController extends Controller 
{
    private string $tableKonsultasi       = 'tb_konsultasi';
    private string $tableDetail           = 'tb_konsultasi_detail';
    private string $tableHasil            = 'tb_konsultasi_hasil';
    private string $tablePenyakit         = 'tb_penyakit';
    private string $tableGejala           = 'tb_gejala';
    private string $tableRelasi           = 'tb_relasi';
 
    /**
     * Cache counter per prefix supaya generateId tidak query ulang
     * ke DB tiap dipanggil dalam satu request (mencegah ID duplikat
     * saat insert banyak baris sekaligus/batch).
     */
    private array $idCounters = []; 

    private function generateId(string $table, string $prefix): string
    {
        if (!isset($this->idCounters[$prefix])) {
            $last = DB::table($table)->orderByDesc('id')->value('id');
            $this->idCounters[$prefix] = $last ? ((int) substr($last, strlen($prefix))) : 0;
        }

        $this->idCounters[$prefix]++; 

        return $prefix . str_pad($this->idCounters[$prefix], 4, '0', STR_PAD_LEFT);
    }

    /**
     * Riwayat semua konsultasi.
     */
    public function index()
    {
        $riwayat = DB::table($this->tableKonsultasi . ' as k')
            ->leftJoin($this->tableHasil . ' as h', 'h.konsultasi_id', '=', 'k.id')
            ->select('k.*', DB::raw('COUNT(h.id) as jumlah_diagnosa'))
            ->groupBy('k.id', 'k.nama_pasien', 'k.created_at', 'k.updated_at')
            ->orderByDesc('k.created_at')
            ->get();

        return view('Konsultasi.KonsultasiRiwayat', compact('riwayat'));
    }

    /**
     * Tampilkan form diagnosa (daftar gejala dinamis dari tb_gejala).
     */
    public function create()
    {
        $gejala = DB::table($this->tableGejala)
            ->orderBy('code_gejala')
            ->get();

        return view('Konsultasi.KonsultasiForm', compact('gejala'));
    }

    /**
     * Proses hitung CF dan simpan hasil.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pasien' => 'nullable|string|max:150',
            'cf'          => 'nullable|array',
            'cf.*'        => 'numeric|min:0|max:1',
        ]);

        $cfUser = collect($validated['cf'] ?? [])
            ->filter(fn ($v) => (float) $v > 0);

        if ($cfUser->isEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Pilih minimal satu gejala dengan tingkat keyakinan di atas "Tidak".');
        }

        // ==== Simpan header konsultasi ====
        $konsultasiId = $this->generateId($this->tableKonsultasi, 'KSL');

        DB::table($this->tableKonsultasi)->insert([
            'id'          => $konsultasiId,
            'nama_pasien' => $validated['nama_pasien'] ?? null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // ==== Simpan detail gejala yang dipilih user ====
        $detailRows = [];
        foreach ($cfUser as $gejalaId => $nilai) {
            $detailRows[] = [
                'id'            => $this->generateId($this->tableDetail, 'KDT'),
                'konsultasi_id' => $konsultasiId,
                'gejala_id'     => $gejalaId,
                'cf_user'       => (float) $nilai,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }
        DB::table($this->tableDetail)->insert($detailRows);

        // ==== Hitung CF combine per penyakit ====
        $relasi = DB::table($this->tableRelasi)->get();

        $penyakitGroups = $relasi->groupBy('penyakit_id');

        $hasilRows = [];
        foreach ($penyakitGroups as $penyakitId => $daftarRelasi) {
            $cfCombine = 0;
            $adaGejalaCocok = false;

            foreach ($daftarRelasi as $r) {
                if (!$cfUser->has($r->gejala_id)) {
                    continue;
                }

                $adaGejalaCocok = true;
                $cfHE = (float) $r->cf_pakar * (float) $cfUser[$r->gejala_id];

                // Rumus combine CF: CFcombine = CFold + CFnew * (1 - CFold)
                $cfCombine = $cfCombine + $cfHE * (1 - $cfCombine);
            }

            if ($adaGejalaCocok && $cfCombine > 0) {
                $hasilRows[] = [
                    'id'            => $this->generateId($this->tableHasil, 'KHS'),
                    'konsultasi_id' => $konsultasiId,
                    'penyakit_id'   => $penyakitId,
                    'nilai_cf'      => round($cfCombine, 4),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }

        if (!empty($hasilRows)) {
            DB::table($this->tableHasil)->insert($hasilRows);
        }

        return redirect()
            ->route('konsultasi.hasil', $konsultasiId)
            ->with('success', 'Diagnosa berhasil diproses.');
    }

    /**
     * Tampilkan hasil ranking diagnosa untuk satu konsultasi.
     */
    public function show($id)
    {
        $konsultasi = DB::table($this->tableKonsultasi)->where('id', $id)->first();

        if (!$konsultasi) {
            abort(404);
        }

        $hasil = DB::table($this->tableHasil . ' as h')
            ->join($this->tablePenyakit . ' as p', 'p.id', '=', 'h.penyakit_id')
            ->where('h.konsultasi_id', $id)
            ->select('p.code_penyakit', 'p.nm_penyakit', 'p.deskripsi', 'p.solusi', 'h.nilai_cf')
            ->orderByDesc('h.nilai_cf')
            ->get();

        return view('Konsultasi.KonsultasiHasil', compact('konsultasi', 'hasil'));
    }
}