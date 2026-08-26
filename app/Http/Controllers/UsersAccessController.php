<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersAccessController extends Controller
{
    public function index()
    {
        $users = DB::table('tb_users')
            ->select('id_users', 'nm_users', 'email_users', 'role_users', 'status')
            ->orderBy('nm_users')
            ->get()
            ->map(fn($u) => [
                'id'     => $u->id_users,
                'nama'   => $u->nm_users,
                'email'  => $u->email_users,
                'role'   => (int) $u->role_users,
                'status' => $u->status,
            ])
            ->values()
            ->toArray();

        return view('Administrator.UsersAccess', compact('users'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama'     => 'required|string|max:100',
                'email'    => 'required|email|unique:tb_users,email_users',
                'password' => 'required|string|min:6',
                'role'     => 'required|integer|in:0,1,2',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        try {
            $last  = DB::table('tb_users')->orderByDesc('id_users')->value('id_users');
            $num   = $last ? (int) substr($last, 3) + 1 : 1;
            $newId = 'USR' . str_pad($num, 4, '0', STR_PAD_LEFT);

            $role = (string) $request->input('role');

            DB::table('tb_users')->insert([
                'id_users'    => $newId,
                'nm_users'    => $request->nama,
                'email_users' => $request->email,
                'pwd_users'   => Hash::make($request->password),
                'role_users'  => $role,
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan! ID: ' . $newId,
                'user'    => [
                    'id'     => $newId,
                    'nama'   => $request->nama,
                    'email'  => $request->email,
                    'role'   => (int) $request->role,
                    'status' => 'active',
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'nama'  => 'required|string|max:100',
                'email' => 'required|email|unique:tb_users,email_users,' . $id . ',id_users',
                'role'  => 'required|integer|in:0,1,2',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        try {
            $exists = DB::table('tb_users')->where('id_users', $id)->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'message' => 'User tidak ditemukan!'], 404);
            }

            $role = (string) $request->input('role');

            DB::table('tb_users')->where('id_users', $id)->update([
                'nm_users'    => $request->nama,
                'email_users' => $request->email,
                'role_users'  => $role,
                'updated_at'  => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate!',
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $exists = DB::table('tb_users')->where('id_users', $id)->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'message' => 'User tidak ditemukan!'], 404);
            }

            DB::table('tb_users')->where('id_users', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus!',
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}