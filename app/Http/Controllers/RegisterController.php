<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/** Pendaftaran mandiri khusus PEMBELI. Role selalu 'customer', tidak diambil dari input. */
class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'customer', // ditetapkan server; field "role" dari browser diabaikan
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('shop.index')->with('success', 'Akun berhasil dibuat. Selamat berbelanja!');
    }
}
