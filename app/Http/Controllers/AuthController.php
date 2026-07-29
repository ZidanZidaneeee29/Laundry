<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginInput = trim($request->input('login'));
        $password = $request->input('password');
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // 1. Coba Auth via Email / Username
        if (Auth::attempt([$fieldType => $loginInput, 'password' => $password], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return $this->redirectUser(Auth::user());
        }

        // 2. Fallback: Coba Auth via field nama jika username/email berbeda
        $userByName = User::where('nama', $loginInput)->orWhere('username', $loginInput)->orWhere('email', $loginInput)->first();
        if ($userByName && Hash::check($password, $userByName->password)) {
            Auth::login($userByName, $request->boolean('remember'));
            $request->session()->regenerate();
            return $this->redirectUser($userByName);
        }

        return back()->withErrors([
            'login' => 'Username/Email atau password yang Anda masukkan tidak sesuai.',
        ])->onlyInput('login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'no_telepon' => ['required', 'string', 'max:15'],
            'alamat' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'in:pelanggan,kasir'],
        ]);

        $role = $request->input('role', 'pelanggan');

        $user = User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        if ($role === 'pelanggan') {
            Pelanggan::create([
                'id_user' => $user->id_user,
                'no_telepon' => $request->no_telepon,
                'alamat' => $request->alamat,
            ]);
        }

        Auth::login($user);

        return $this->redirectUser($user)->with('success', 'Registrasi berhasil! Selamat datang.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda telah keluar.');
    }

    protected function redirectUser(User $user)
    {
        if ($user->role === 'kasir') {
            return redirect()->route('kasir.transaksi.index');
        } elseif ($user->role === 'pemilik') {
            return redirect()->route('pemilik.dashboard');
        }
        return redirect()->route('monitoring');
    }
}
