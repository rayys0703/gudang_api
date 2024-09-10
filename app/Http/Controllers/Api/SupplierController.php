<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Barang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($search = $request->input('search.value')) {
            $query->where('nama', 'like', "%{$search}%")
                ->orWhere('alamat', 'like', "%{$search}%")
                ->orWhere('telepon', 'like', "%{$search}%");
        }

        return datatables($query)->toJson();
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'telepon' => 'required|numeric|digits_between:10,15',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nama.required' => 'Nama harus diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'nama.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'alamat.required' => 'Alamat harus diisi.',
            'alamat.string' => 'Alamat harus berupa teks.',
            'alamat.max' => 'Alamat tidak boleh lebih dari 255 karakter.',
            'telepon.required' => 'Nomor telepon harus diisi.',
            'telepon.numeric' => 'Nomor telepon harus berupa angka.',
            'telepon.digits_between' => 'Nomor telepon harus memiliki panjang antara 10 sampai 15 digit.',
            'keterangan.string' => 'Keterangan harus berupa teks.',
            'keterangan.max' => 'Keterangan tidak boleh lebih dari 255 karakter.',
        ]);

        $data = Supplier::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
            'keterangan' => $request->keterangan,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Data berhasil ditambahkan!']);
    }

    public function edit($id)
    {
        $data = Supplier::find($id);
        return response()->json($data);
    }

    public function update($id, Request $request): JsonResponse
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'telepon' => 'required|numeric|digits_between:10,15',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nama.required' => 'Nama harus diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'nama.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'alamat.required' => 'Alamat harus diisi.',
            'alamat.string' => 'Alamat harus berupa teks.',
            'alamat.max' => 'Alamat tidak boleh lebih dari 255 karakter.',
            'telepon.required' => 'Nomor telepon harus diisi.',
            'telepon.numeric' => 'Nomor telepon harus berupa angka.',
            'telepon.digits_between' => 'Nomor telepon harus memiliki panjang antara 10 sampai 15 digit.',
            'keterangan.string' => 'Keterangan harus berupa teks.',
            'keterangan.max' => 'Keterangan tidak boleh lebih dari 255 karakter.',
        ]);

        $data = Supplier::find($id);
        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        if ($data) {
            $data->nama = $request->nama;
            $data->alamat = $request->alamat;
            $data->telepon = $request->telepon;
            $data->keterangan = $request->keterangan;
            $data->updated_by = Auth::id();
            $data->save();

            return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui!']);
        }
        
        return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.']);
    }

    public function delete($id)
    {
        $supplier = Supplier::find($id);

        if ($supplier) {
            $barang = Barang::where('supplier_id', $id)->get();

            foreach ($barang as $item) {
                $item->delete();
            }

            $supplier->delete();
            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus!']);
        }

        return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.']);
    }

    public function deleteSelected(Request $request)
    {
        $ids = $request->input('ids');
        foreach ($ids as $id) {
            $supplier = Supplier::find($id);

            if ($supplier) {
                $barang = Barang::where('supplier_id', $id)->get();

                foreach ($barang as $item) {
                    $item->delete();
                }

                $supplier->delete();
            }
        }
        return response()->json(['success' => true, 'message' => 'Data terpilih berhasil dihapus!']);
    }
}
