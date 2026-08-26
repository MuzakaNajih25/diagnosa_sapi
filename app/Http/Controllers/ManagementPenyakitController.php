<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagementPenyakitController extends Controller
{
    private string $table = 'tb_penyakit';
    private string $prefix = 'PYK';

    private function generateId(): string
    {
        $last = DB::table($this->table)->orderByDesc('id')->value('id');
        $number = $last ? ((int) substr($last, strlen($this->prefix)) + 1) : 1;
        return $this->prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tampilkan daftar penyakit.
     */
    public function index()
    {
        $penyakit = DB::table($this->table)
            ->orderBy('code_penyakit')
            ->get();

        return view('Pengetahuan.ManagementPenyakit', compact('penyakit'));
    }

    /**
     * Simpan penyakit baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_penyakit' => 'required|string|max:20|unique:tb_penyakit,code_penyakit',
            'nm_penyakit'   => 'required|string|max:150',
            'deskripsi'     => 'nullable|string',
            'solusi'        => 'nullable|string',
        ]);

        $validated['id'] = $this->generateId();
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        DB::table($this->table)->insert($validated);

        return redirect()
            ->route('pengetahuan.penyakit.index')
            ->with('success', 'Data penyakit berhasil ditambahkan.');
    }

    /**
     * Perbarui penyakit.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'code_penyakit' => 'required|string|max:20|unique:tb_penyakit,code_penyakit,' . $id,
            'nm_penyakit'   => 'required|string|max:150',
            'deskripsi'     => 'nullable|string',
            'solusi'        => 'nullable|string',
        ]);

        $validated['updated_at'] = now();

        DB::table($this->table)
            ->where('id', $id)
            ->update($validated);

        return redirect()
            ->route('pengetahuan.penyakit.index')
            ->with('success', 'Data penyakit berhasil diperbarui.');
    }

    /**
     * Hapus penyakit.
     */
    public function destroy($id)
    {
        DB::table($this->table)
            ->where('id', $id)
            ->delete();

        return redirect()
            ->route('pengetahuan.penyakit.index')
            ->with('success', 'Data penyakit berhasil dihapus.');
    }
}