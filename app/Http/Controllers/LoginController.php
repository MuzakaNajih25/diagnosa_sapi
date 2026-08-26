<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    // ==== Tampilkan halaman login ==== //
    public function index()
    {
        if (Session::has('user')) {
            return $this->redirectByRole(Session::get('user')['role']);
        }

        return view('auth.Login');
    }

    // ==== Proses login ==== //
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = DB::table('tb_users')
            ->where('email_users', $request->email)
            ->first();

        if (!$user) {
            return back()->with('login_error', 'Email atau password salah.');
        }

        if ($user->status !== 'active') {
            return back()->with('login_error', 'Akun kamu nonaktif. Hubungi administrator.');
        }

        if (!Hash::check($request->password, $user->pwd_users)) {
            return back()->with('login_error', 'Email atau password salah.');
        }

        Session::put('user', [
            'id'    => $user->id_users,
            'nama'  => $user->nm_users,
            'email' => $user->email_users,
            'role'  => $user->role_users,
        ]);

        return $this->redirectByRole($user->role_users);
    }

    // ==== Logout ==== //
    public function logout()
    {
        Session::forget('user');
        return redirect()->route('login');
    }

    // ==== Helper redirect by role ==== //
    private function redirectByRole($role)
    {
        return match ((string) $role) {
            '1'     => redirect()->route('dokter.dashboard'),
            '2'     => redirect()->route('administrator.useraccess'),
            default => redirect()->route('dashboard'),
        };
    }
}