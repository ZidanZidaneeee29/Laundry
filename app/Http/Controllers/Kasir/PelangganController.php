<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $query = Pelanggan::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('no_telepon', 'like', "%{$search}%");
        }

        $pelangganList = $query->paginate(10);
        return view('kasir.pelanggan.index', compact('pelangganList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'no_telepon' => 'required|string|max:15',
            'alamat' => 'required|string|max:255',
        ]);

        $user = User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'pelanggan',
        ]);

        Pelanggan::create([
            'id_user' => $user->id_user,
            'no_telepon' => $request->no_telepon,
            'alamat' => $request->alamat,
        ]);

        return back()->with('success', 'Data pelanggan berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $pelanggan = Pelanggan::with('user')->findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $pelanggan->user->id_user . ',id_user',
            'no_telepon' => 'required|string|max:15',
            'alamat' => 'required|string|max:255',
        ]);

        $pelanggan->user->nama = $request->nama;
        $pelanggan->user->email = $request->email;
        if ($request->filled('password')) {
            $pelanggan->user->password = Hash::make($request->password);
        }
        $pelanggan->user->save();

        $pelanggan->no_telepon = $request->no_telepon;
        $pelanggan->alamat = $request->alamat;
        $pelanggan->save();

        return back()->with('success', 'Data pelanggan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $pelanggan = Pelanggan::with('user')->findOrFail($id);
        $user = $pelanggan->user;
        $pelanggan->delete();
        if ($user) {
            $user->delete();
        }

        return back()->with('success', 'Data pelanggan berhasil dihapus.');
    }
}
