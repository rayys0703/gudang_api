<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Barang;
use App\Models\BarangMasuk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{

	public function index(Request $request)
	{
		$query = Customer::query();

        if ($search = $request->input('search.value')) {
            $query->where('nama', 'like', "%{$search}%")
                ->orWhere('alamat', 'like', "%{$search}%")
                ->orWhere('telepon', 'like', "%{$search}%")
                ->orWhere('keterangan', 'like', "%{$search}%");
        }

        return datatables($query)->toJson();

		// $search = $request->input('search');

        // $data = Customer::when($search, function ($query) use ($search) {
        //     return $query->where('nama', 'like', '%' . $search . '%')
		// 	->orWhere('alamat', 'like', '%' . $search . '%')
		// 	->orWhere('telepon', 'like', '%' . $search . '%')
		// 	->orWhere('keterangan', 'like', '%' . $search . '%');
        // })->orderBy('nama', 'asc')->paginate(7);	
					
        // return view('customer.index', compact('data'));
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

		$data = Customer::create([
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
		$data = Customer::find($id);
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

		$data = Customer::find($id);
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
		$customer = Customer::find($id);

		$customer->delete();
		return response()->json(['success' => true, 'message' => 'Data berhasil dihapus!']);
	}

	public function deleteSelected(Request $request)
	{
		$ids = $request->input('ids');
		foreach ($ids as $id) {
			$customer = Customer::find($id);

			if ($customer) {
				/*$barangMasuk = BarangMasuk::where('customer_id', $id)->get();

				foreach ($barangMasuk as $item) {
					$barang = Barang::find($item->barang_id);
					if ($barang) {
						$barang->jumlah -= $item->jumlah;
						$barang->save();
					}
					$item->delete();
				}*/

				$customer->delete();
			}
		}
		return response()->json(['success' => true, 'message' => 'Data terpilih berhasil dihapus!']);
	}
}
