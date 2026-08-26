<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagementSapiController extends Controller
{
    private string $table = 'tb_sapi';
    private string $prefix = 'SPI';

    private function generateId(): string
    {
        $last = DB::table($this->table)->orderByDesc('id')->value('id');
        $number = $last ? ((int) substr($last, strlen($this->prefix)) + 1) : 1;
        return $this->prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tampilkan daftar sapi (sudah join nama peternak).
     */
    public function index()
    {
        $sapi = DB::table($this->table)
            ->leftJoin('tb_peternak', 'tb_sapi.peternak_id', '=', 'tb_peternak.id')
            ->select(
                'tb_sapi.*',
                'tb_peternak.nama_peternak'
            )
            ->orderBy('tb_sapi.nm_sapi')
            ->get();

        $peternak = DB::table('tb_peternak')
            ->orderBy('nama_peternak')
            ->get();

        return view('Sapi.ManagementSapi', compact('sapi', 'peternak'));
    }

    /**
     * Simpan sapi baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nm_sapi'       => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:jantan,betina',
            'warna'         => 'required|string|max:50',
            'ciri_ciri'     => 'nullable|string',
            'umur'          => 'nullable|string|max:20',
            'peternak_id'   => 'nullable|string|exists:tb_peternak,id',
        ]);

        $validated['id'] = $this->generateId();
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        DB::table($this->table)->insert($validated);

        return redirect()
            ->route('sapi.index')
            ->with('success', 'Data sapi berhasil ditambahkan.');
    }

    /**
     * Perbarui data sapi.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nm_sapi'       => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:jantan,betina',
            'warna'         => 'required|string|max:50',
            'ciri_ciri'     => 'nullable|string',
            'umur'          => 'nullable|string|max:20',
            'peternak_id'   => 'nullable|string|exists:tb_peternak,id',
        ]);

        $validated['updated_at'] = now();

        DB::table($this->table)
            ->where('id', $id)
            ->update($validated);

        return redirect()
            ->route('sapi.index')
            ->with('success', 'Data sapi berhasil diperbarui.');
    }

    /**
     * Hapus data sapi.
     */
    public function destroy($id)
    {
        DB::table($this->table)
            ->where('id', $id)
            ->delete();

        return redirect()
            ->route('sapi.index')
            ->with('success', 'Data sapi berhasil dihapus.');
    }
}