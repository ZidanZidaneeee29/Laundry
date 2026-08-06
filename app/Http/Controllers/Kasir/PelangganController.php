<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $query = Pelanggan::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            })->orWhere('no_telepon', 'like', "%{$search}%");
        }

        $pelangganList = $query->paginate(10);
        return view('kasir.pelanggan.index', compact('pelangganList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'no_telepon' => 'required|string|max:15',
            'alamat' => 'required|string|max:255',
        ]);

        $uniq = time() . '_' . rand(100, 999);
        $email = 'pelanggan_' . $uniq . '@sindory.local';
        $username = 'pelanggan_' . $uniq;

        $user = User::create([
            'nama' => $request->nama,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make(Str::random(16)),
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
            'no_telepon' => 'required|string|max:15',
            'alamat' => 'required|string|max:255',
        ]);

        if ($pelanggan->user) {
            $pelanggan->user->nama = $request->nama;
            $pelanggan->user->save();
        }

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
