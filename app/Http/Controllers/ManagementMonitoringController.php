<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagementMonitoringController extends Controller
{
    /**
     * Tampilkan halaman monitoring: tab sehat, tidak sehat, dan perlu ditinjau.
     */
    public function index()
    {
        $sehat = DB::table('tb_sapi')
            ->where('status_kesehatan', 'sehat')
            ->orderBy('nm_sapi')
            ->get();

        $sakit = DB::table('tb_sapi')
            ->where('status_kesehatan', 'sakit')
            ->orderBy('nm_sapi')
            ->get();

        // Antrian review: hasil diagnosa CF >= 60% yang belum diputuskan admin
        $perluDitinjau = DB::table('tb_konsultasi_hasil as h')
            ->join('tb_konsultasi as k', 'h.konsultasi_id', '=', 'k.id')
            ->join('tb_penyakit as p', 'h.penyakit_id', '=', 'p.id')
            ->leftJoin('tb_sapi as s', 'k.sapi_id', '=', 's.id')
            ->where('h.status_review', 'pending')
            ->select(
                'h.id as hasil_id',
                'h.nilai_cf',
                'h.created_at as tanggal_diagnosa',
                'k.id as konsultasi_id',
                'k.nama_pasien',
                's.id as sapi_id',
                's.nm_sapi',
                's.jenis_kelamin',
                's.warna',
                'p.nm_penyakit',
                'p.solusi'
            )
            ->orderByDesc('h.created_at')
            ->get();

        return view('Monitoring.MonitoringSapi', compact('sehat', 'sakit', 'perluDitinjau'));
    }

    /**
     * Admin menandai hasil diagnosa sebagai valid -> status sapi jadi sakit.
     */
    public function tandaiSakit(Request $request, $hasilId)
    {
        $hasil = DB::table('tb_konsultasi_hasil as h')
            ->join('tb_konsultasi as k', 'h.konsultasi_id', '=', 'k.id')
            ->where('h.id', $hasilId)
            ->select('h.id', 'k.sapi_id')
            ->first();

        if (!$hasil || !$hasil->sapi_id) {
            return redirect()
                ->route('monitoring.index')
                ->with('error', 'Data diagnosa tidak ditemukan atau sapi tidak terdaftar.');
        }

        DB::transaction(function () use ($hasil, $hasilId) {
            DB::table('tb_sapi')
                ->where('id', $hasil->sapi_id)
                ->update(['status_kesehatan' => 'sakit', 'updated_at' => now()]);

            DB::table('tb_konsultasi_hasil')
                ->where('id', $hasilId)
                ->update(['status_review' => 'dikonfirmasi', 'updated_at' => now()]);
        });

        return redirect()
            ->route('monitoring.index')
            ->with('success', 'Sapi ditandai sakit berdasarkan hasil diagnosa.');
    }

    /**
     * Admin mengabaikan hasil diagnosa -> status sapi tetap seperti semula.
     */
    public function abaikan(Request $request, $hasilId)
    {
        DB::table('tb_konsultasi_hasil')
            ->where('id', $hasilId)
            ->update(['status_review' => 'diabaikan', 'updated_at' => now()]);

        return redirect()
            ->route('monitoring.index')
            ->with('success', 'Hasil diagnosa diabaikan.');
    }

    /**
     * Tandai sapi sembuh, balikin status ke sehat (dipakai dari tab "Tidak Sehat").
     */
    public function tandaiSembuh(Request $request, $sapiId)
    {
        DB::table('tb_sapi')
            ->where('id', $sapiId)
            ->update(['status_kesehatan' => 'sehat', 'updated_at' => now()]);

        return redirect()
            ->route('monitoring.index')
            ->with('success', 'Sapi ditandai sembuh.');
    }
}