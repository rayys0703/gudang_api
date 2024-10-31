<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\JenisBarang;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class JenisBarangController extends Controller
{

	public function index(Request $request)
	{
		$query = JenisBarang::query();

		if ($search = $request->input('search.value')) {
			$query->where('nama', 'like', "%{$search}%")
				->orWhere('deskripsi', 'like', "%{$search}%");
		}

        return datatables($query)->toJson();
	}

	public function store(Request $request): JsonResponse
	{
		$request->validate([
			'nama' => 'required|string|max:255',
			'deskripsi' => 'nullable|string',
		], [
			'nama.required' => 'Nama jenis barang harus diisi.',
			'nama.string' => 'Nama jenis barang harus berupa teks.',
			'nama.max' => 'Nama jenis barang tidak boleh lebih dari 255 karakter.',
			'deskripsi.string' => 'Deskripsi harus berupa teks.',
		]);
		
		$data = JenisBarang::create([
			'nama' => $request->nama,
			'deskripsi' => $request->deskripsi,
		]);

		return response()->json(['success' => true, 'message' => 'Data berhasil ditambahkan!']);
	}

	public function edit($id)
	{
		$data = JenisBarang::find($id);
		return response()->json($data);
	}

	public function update($id, Request $request): JsonResponse
	{
		$request->validate([
			'nama' => 'required|string|max:255',
			'deskripsi' => 'nullable|string',
		], [
			'nama.required' => 'Nama jenis barang harus diisi.',
			'nama.string' => 'Nama jenis barang harus berupa teks.',
			'nama.max' => 'Nama jenis barang tidak boleh lebih dari 255 karakter.',
			'deskripsi.string' => 'Deskripsi harus berupa teks.',
		]);

		$data = JenisBarang::find($id);
		if (!$data) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

		if ($data) {
			$data->nama = $request->nama;
			$data->deskripsi = $request->deskripsi;
			$data->save();

			return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui!']);
        }

		return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.']);
	}

	public function delete($id)
	{
		$data = JenisBarang::find($id);

		$data->delete();
		return response()->json(['success' => true, 'message' => 'Data berhasil dihapus!']);
	}

	public function deleteSelected(Request $request)
	{
		$ids = $request->input('ids');
		foreach ($ids as $id) {
			$data = JenisBarang::find($id);
			if ($data) {
				$data->delete();
			}
		}
		return response()->json(['success' => true, 'message' => 'Data terpilih berhasil dihapus!']);
	}
}
