<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagementGejalaController extends Controller
{
    private string $table = 'tb_gejala';
    private string $prefix = 'GJL';

    private function generateId(): string
    {
        $last = DB::table($this->table)->orderByDesc('id')->value('id');
        $number = $last ? ((int) substr($last, strlen($this->prefix)) + 1) : 1;
        return $this->prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tampilkan daftar gejala.
     */
    public function index()
    {
        $gejala = DB::table($this->table)
            ->orderBy('code_gejala')
            ->get();

        return view('Pengetahuan.ManagementGejala', compact('gejala'));
    }

    /**
     * Simpan gejala baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_gejala' => 'required|string|max:20|unique:tb_gejala,code_gejala',
            'nm_gejala'   => 'required|string|max:200',
        ]);

        $validated['id'] = $this->generateId();
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        DB::table($this->table)->insert($validated);

        return redirect()
            ->route('pengetahuan.gejala.index')
            ->with('success', 'Data gejala berhasil ditambahkan.');
    }

    /**
     * Perbarui gejala.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'code_gejala' => 'required|string|max:20|unique:tb_gejala,code_gejala,' . $id,
            'nm_gejala'   => 'required|string|max:200',
        ]);

        $validated['updated_at'] = now();

        DB::table($this->table)
            ->where('id', $id)
            ->update($validated);

        return redirect()
            ->route('pengetahuan.gejala.index')
            ->with('success', 'Data gejala berhasil diperbarui.');
    }

    /**
     * Hapus gejala.
     */
    public function destroy($id)
    {
        DB::table($this->table)
            ->where('id', $id)
            ->delete();

        return redirect()
            ->route('pengetahuan.gejala.index')
            ->with('success', 'Data gejala berhasil dihapus.');
    }
}