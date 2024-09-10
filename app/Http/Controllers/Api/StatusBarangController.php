<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\StatusBarang;
use App\Models\BarangMasuk;
use App\Models\Barang;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class StatusBarangController extends Controller
{

	public function index(Request $request)
	{
		$query = StatusBarang::query();

        if ($search = $request->input('search.value')) {
            $query->where('nama', 'like', "%{$search}%");
        }

        return datatables($query)->toJson();
	}

	public function store(Request $request): JsonResponse
	{
		$request->validate([
			'nama' => 'required|string|max:255',
			'warna' => 'required|string|regex:/^#[A-Fa-f0-9]{6}$/',
		], [
			'nama.required' => 'Nama status barang harus diisi.',
			'nama.string' => 'Nama status barang harus berupa teks.',
			'nama.max' => 'Nama status barang tidak boleh lebih dari 255 karakter.',
			'warna.required' => 'Warna harus diisi.',
			'warna.string' => 'Warna harus berupa teks.',
			'warna.regex' => 'Format warna tidak valid. Gunakan format hex (contoh: #FFFFFF).',
		]);

		$data = StatusBarang::create([
			'nama' => $request->nama,
			'warna' => $request->warna,
		]);

		return response()->json(['success' => true, 'message' => 'Data berhasil ditambahkan!']);
	}

	public function edit($id)
	{
		$data = StatusBarang::find($id);
		return response()->json($data);
	}

	public function update($id, Request $request): JsonResponse
	{
		$request->validate([
			'nama' => 'required|string|max:255',
			'warna' => 'required|string|regex:/^#[A-Fa-f0-9]{6}$/',
		], [
			'nama.required' => 'Nama status barang harus diisi.',
			'nama.string' => 'Nama status barang harus berupa teks.',
			'nama.max' => 'Nama status barang tidak boleh lebih dari 255 karakter.',
			'warna.required' => 'Warna harus diisi.',
			'warna.string' => 'Warna harus berupa teks.',
			'warna.regex' => 'Format warna tidak valid. Gunakan format hex (contoh: #FFFFFF).',
		]);

		$data = StatusBarang::find($id);
		if (!$data) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

		if ($data) {
			$data->nama = $request->nama;
			$data->warna = $request->warna;
			$data->save();

			return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui!']);
        }

		return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.']);
	}

	public function delete($id)
	{
		$statusBarang = StatusBarang::find($id);
		/*$barangMasuk = BarangMasuk::where('status_barang_id', $id)->get();

		foreach ($barangMasuk as $item) {
			$barang = Barang::find($item->barang_id);
			if ($barang) {
				$barang->jumlah -= $item->jumlah;
				$barang->save();
			}
			$item->delete();
		}*/

		$statusBarang->delete();
		return response()->json(['success' => true, 'message' => 'Data berhasil dihapus!']);
	}

	public function deleteSelected(Request $request)
	{
		$ids = $request->input('ids');
		foreach ($ids as $id) {
			$statusBarang = StatusBarang::find($id);
			/*$barangMasuk = BarangMasuk::where('status_barang_id', $id)->get();

			foreach ($barangMasuk as $item) {
				$barang = Barang::find($item->barang_id);
				if ($barang) {
					$barang->jumlah -= $item->jumlah;
					$barang->save();
				}
				$item->delete();
			}*/

			$statusBarang->delete();
		}
		return response()->json(['success' => true, 'message' => 'Data terpilih berhasil dihapus!']);
	}
}
