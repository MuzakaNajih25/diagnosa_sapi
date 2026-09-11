<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagementPeternakController extends Controller
{
    private string $table = 'tb_peternak';
    private string $prefix = 'PTN';
 
    private function generateId(): string 
    {
        $last = DB::table($this->table)->orderByDesc('id')->value('id');
        $number = $last ? ((int) substr($last, strlen($this->prefix)) + 1) : 1;
        return $this->prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tampilkan daftar peternak all
     */
    public function index()
    {
        $peternak = DB::table($this->table)
            ->orderBy('nama_peternak')
            ->get();

        return view('Peternak.ManagementPeternak', compact('peternak'));
    }

    /**
     * Simpan peternak baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_peternak' => 'required|string|max:100',
            'alamat'        => 'required|string|max:255',
            'no_telp'       => 'required|string|max:20',
        ]);

        $validated['id'] = $this->generateId();
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        DB::table($this->table)->insert($validated);

        return redirect()
            ->route('peternak.index')
            ->with('success', 'Data peternak berhasil ditambahkan.');
    }

    /**
     * Perbarui data peternak.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_peternak' => 'required|string|max:100',
            'alamat'        => 'required|string|max:255',
            'no_telp'       => 'required|string|max:20',
        ]);

        $validated['updated_at'] = now();

        DB::table($this->table)
            ->where('id', $id)
            ->update($validated);

        return redirect()
            ->route('peternak.index')
            ->with('success', 'Data peternak berhasil diperbarui.');
    }

    /**
     * Hapus data peternak.
     */
    public function destroy($id)
    {
        DB::table($this->table)
            ->where('id', $id)
            ->delete();

        return redirect()
            ->route('peternak.index')
            ->with('success', 'Data peternak berhasil dihapus.');
    }
}