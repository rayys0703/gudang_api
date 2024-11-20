<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Aturan validasi untuk foto
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($user->customer_id) {
            $customer = \App\Models\Customer::find($user->customer_id);
            if ($customer) {
                $customer->nama = $request->name;
                $customer->save();
            }
        }

        // Menangani upload foto
        if ($request->hasFile('photo')) {
            // Menghapus foto lama jika ada
            if ($user->photo && file_exists(public_path('assets/photo_profile/' . $user->photo))) {
                unlink(public_path('assets/photo_profile/' . $user->photo));
            }

            $file = $request->file('photo');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/photo_profile'), $filename); // Pindah ke public/assets/photo_profile
            $user->photo = $filename;
        }

        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => $user,
        ]);
    }

    public function updatePhoto(Request $request)
    {
        $user = Auth::user();

        // Log untuk debug
        \Log::info('Memulai proses upload foto', ['user_id' => $user->id]);

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Log request file
            if ($request->hasFile('photo')) {
                \Log::info('File ditemukan', ['filename' => $request->file('photo')->getClientOriginalName()]);
            } else {
                \Log::warning('File tidak ditemukan di request.');
            }

            // Hapus foto lama jika ada
            if ($user->photo && file_exists(public_path('assets/photo_profile/' . $user->photo))) {
                \Log::info('Menghapus foto lama', ['old_photo' => $user->photo]);
                unlink(public_path('assets/photo_profile/' . $user->photo));
            }

            // Simpan foto baru
            $file = $request->file('photo');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/photo_profile'), $filename);

            $user->photo = $filename;
            $user->save();

            \Log::info('Foto berhasil diperbarui', ['new_photo' => $filename]);
            return response()->json(['message' => 'Foto berhasil diperbarui', 'photo' => $filename]);
        } catch (\Exception $e) {
            \Log::error('Error saat mengunggah foto:', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal mengunggah foto.', 'error' => $e->getMessage()], 500);
        }
    }

}
