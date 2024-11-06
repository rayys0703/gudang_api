<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{

    public function index(Request $request)
    {
        $query = Customer::leftJoin('users', 'customer.id', '=', 'users.customer_id')
            ->select('customer.*', 'users.email as email');

        if ($search = $request->input('search.value')) {
            $query->where(function($q) use ($search) {
                $q->where('customer.nama', 'like', "%{$search}%")
                  ->orWhere('customer.alamat', 'like', "%{$search}%")
                  ->orWhere('customer.telepon', 'like', "%{$search}%")
                  ->orWhere('customer.keterangan', 'like', "%{$search}%")
                  ->orWhere('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        return datatables($query)->toJson();
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'alamat' => 'required|string|max:255',
            'telepon' => 'required|numeric|digits_between:10,15',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nama.required' => 'Nama harus diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'nama.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email tidak boleh lebih dari 255 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'alamat.required' => 'Alamat harus diisi.',
            'alamat.string' => 'Alamat harus berupa teks.',
            'alamat.max' => 'Alamat tidak boleh lebih dari 255 karakter.',
            'telepon.required' => 'Nomor telepon harus diisi.',
            'telepon.numeric' => 'Nomor telepon harus berupa angka.',
            'telepon.digits_between' => 'Nomor telepon harus memiliki panjang antara 10 sampai 15 digit.',
            'keterangan.string' => 'Keterangan harus berupa teks.',
            'keterangan.max' => 'Keterangan tidak boleh lebih dari 255 karakter.',
        ]);

        $customer = Customer::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
            'keterangan' => $request->keterangan,
            'created_by' => Auth::id(),
        ]);

        // Membuat akun User terkait
        $user = User::create([
            'name' => $customer->nama,
            'email' => $request->email,
            'password' => bcrypt('customer123'),
            'customer_id' => $customer->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Data berhasil ditambahkan!']);
    }

    public function edit($id)
    {
            $data = DB::table('customer')
                ->leftJoin('users', 'customer.id', '=', 'users.customer_id')
                ->where('customer.id', $id)
                ->first();        
			return response()->json($data);
    }

    public function update($id, Request $request): JsonResponse
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            // 'email' => 'required|email|max:255|unique:users,email,' . User::where('customer_id', $id)->first()->id,
            'alamat' => 'required|string|max:255',
            'telepon' => 'required|numeric|digits_between:10,15',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nama.required' => 'Nama harus diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'nama.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            // 'email.required' => 'Email harus diisi.',
            // 'email.email' => 'Format email tidak valid.',
            // 'email.max' => 'Email tidak boleh lebih dari 255 karakter.',
            // 'email.unique' => 'Email sudah terdaftar.',
            'alamat.required' => 'Alamat harus diisi.',
            'alamat.string' => 'Alamat harus berupa teks.',
            'alamat.max' => 'Alamat tidak boleh lebih dari 255 karakter.',
            'telepon.required' => 'Nomor telepon harus diisi.',
            'telepon.numeric' => 'Nomor telepon harus berupa angka.',
            'telepon.digits_between' => 'Nomor telepon harus memiliki panjang antara 10 sampai 15 digit.',
            'keterangan.string' => 'Keterangan harus berupa teks.',
            'keterangan.max' => 'Keterangan tidak boleh lebih dari 255 karakter.',
        ]);

        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        $customer->nama = $request->nama;
        $customer->alamat = $request->alamat;
        $customer->telepon = $request->telepon;
        $customer->keterangan = $request->keterangan;
        $customer->updated_by = Auth::id();
        $customer->save();

        // Memperbarui akun User terkait
        $user = User::where('customer_id', $customer->id)->first();
        if ($user) {
            $user->name = $customer->nama;
            $user->email = $request->email;
            $user->save();
        }

        return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui!']);
    }
	
    public function delete($id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        // Menghapus akun User terkait
        $user = User::where('customer_id', $customer->id)->first();
        if ($user) {
            $user->delete();
        }

        $customer->delete();
        return response()->json(['success' => true, 'message' => 'Data berhasil dihapus!']);
    }

    public function deleteSelected(Request $request)
    {
        $ids = $request->input('ids');
        foreach ($ids as $id) {
            $customer = Customer::find($id);

            if ($customer) {
                // Menghapus akun User terkait
                $user = User::where('customer_id', $customer->id)->first();
                if ($user) {
                    $user->delete();
                }
                $customer->delete();
            }
        }
        return response()->json(['success' => true, 'message' => 'Data terpilih berhasil dihapus!']);
    }
}
