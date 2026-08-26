<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagementRelasiController extends Controller
{
    private string $tablePenyakit = 'tb_penyakit';
    private string $tableGejala   = 'tb_gejala';
    private string $tableRelasi   = 'tb_relasi';
    private string $prefix        = 'REL';

    /**
     * Cache counter di memori supaya generateId tidak query ulang ke DB
     * tiap dipanggil dalam satu request (mencegah ID duplikat saat
     * insert banyak baris sekaligus/batch).
     */
    private ?int $idCounter = null;

    private function generateId(): string
    {
        if ($this->idCounter === null) {
            $last = DB::table($this->tableRelasi)->orderByDesc('id')->value('id');
            $this->idCounter = $last ? ((int) substr($last, strlen($this->prefix))) : 0;
        }

        $this->idCounter++;

        return $this->prefix . str_pad($this->idCounter, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tampilkan daftar penyakit beserta jumlah gejala yang sudah direlasikan.
     */
    public function index()
    {
        $penyakit = DB::table($this->tablePenyakit . ' as p')
            ->leftJoin($this->tableRelasi . ' as r', 'r.penyakit_id', '=', 'p.id')
            ->select('p.*', DB::raw('COUNT(r.id) as jumlah_gejala'))
            ->groupBy('p.id', 'p.code_penyakit', 'p.nm_penyakit', 'p.deskripsi', 'p.solusi', 'p.created_at', 'p.updated_at')
            ->orderBy('p.code_penyakit')
            ->get();

        return view('Pengetahuan.ManagementRelasi', compact('penyakit'));
    }

    /**
     * Tampilkan form atur gejala + CF untuk satu penyakit.
     */
    public function edit($penyakitId)
    {
        $penyakit = DB::table($this->tablePenyakit)
            ->where('id', $penyakitId)
            ->first();

        if (!$penyakit) {
            abort(404);
        }

        $relasiPenyakitIni = DB::table($this->tableRelasi)
            ->where('penyakit_id', $penyakitId)
            ->get()
            ->keyBy('gejala_id');

        $gejala = DB::table($this->tableGejala)
            ->orderBy('code_gejala')
            ->get()
            ->map(function ($item) use ($relasiPenyakitIni) {
                $existing = $relasiPenyakitIni->get($item->id);
                $item->checked = (bool) $existing;
                $item->nilai_cf_pakar = $existing->cf_pakar ?? 0;
                return $item;
            });

        return view('Pengetahuan.ManagementRelasiForm', compact('penyakit', 'gejala'));
    }

    /**
     * Simpan relasi gejala + CF pakar untuk satu penyakit.
     * Menghapus relasi lama penyakit ini lalu menulis ulang sesuai centang & CF terbaru.
     */
    public function update(Request $request, $penyakitId)
    {
        $request->validate([
            'checked_gejala'   => 'nullable|array',
            'checked_gejala.*' => 'string|exists:tb_gejala,id',
            'cf'               => 'nullable|array',
            'cf.*'             => 'numeric|min:-1|max:1',
        ]);

        $checkedIds = $request->input('checked_gejala', []);
        $cfValues   = $request->input('cf', []);

        DB::table($this->tableRelasi)
            ->where('penyakit_id', $penyakitId)
            ->delete();

        $rows = [];
        foreach ($checkedIds as $gejalaId) {
            $rows[] = [
                'id'          => $this->generateId(),
                'penyakit_id' => $penyakitId,
                'gejala_id'   => $gejalaId,
                'cf_pakar'    => (float) ($cfValues[$gejalaId] ?? 0),
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        if (!empty($rows)) {
            DB::table($this->tableRelasi)->insert($rows);
        }

        return redirect()
            ->route('pengetahuan.relasi.index')
            ->with('success', 'Relasi penyakit-gejala berhasil disimpan.');
    }
}