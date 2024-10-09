<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Keperluan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class KeperluanController extends Controller
{

	public function index(Request $request)
	{
		$query = Keperluan::query();

		if ($search = $request->input('search.value')) {
			$query->where('nama', 'like', "%{$search}%");
		}

		return datatables($query)->toJson();
	}

	public function store(Request $request): JsonResponse
	{
		$request->validate([
			'nama' => 'required|string|max:30',
			'nama_tanggal_akhir' => 'nullable|string|max:30',
			'extend' => 'nullable|boolean',
			'batas_hari' => 'nullable|numeric|min:1|max:90',
		], [
			'nama.required' => 'Nama jenis barang harus diisi.',
			'nama.string' => 'Nama jenis barang harus berupa teks.',
			'nama.max' => 'Nama jenis barang tidak boleh lebih dari 30 karakter.',
			//'nama_tanggal_akhir.required' => 'Nama tanggal akhir harus diisi.',
			'nama_tanggal_akhir.string' => 'Nama tanggal akhir harus berupa teks.',
			'nama_tanggal_akhir.max' => 'Nama tanggal akhir tidak boleh lebih dari 30 karakter.',
			'extend.boolean' => 'Extend harus berupa nilai boolean (false/true).',
			'batas_hari.numeric' => 'Batas hari harus berupa angka.',
			'batas_hari.min' => 'Batas hari minimal 1 hari.',
			'batas_hari.max' => 'Batas hari maksimal 90 hari.',
		]);

		$data = Keperluan::create([
			'nama' => $request->nama,
			'nama_tanggal_akhir' => $request->nama_tanggal_akhir,
			'extend' => $request->extend ?? 0,
			'batas_hari' => $request->batas_hari ?? 1,
		]);

		return response()->json(['success' => true, 'message' => 'Data berhasil ditambahkan!']);	
	}

	public function edit($id)
	{
		$data = Keperluan::find($id);
		return response()->json($data);
	}

	public function update($id, Request $request): JsonResponse
	{
		$request->validate([
			'nama' => 'required|string|max:30',
			'nama_tanggal_akhir' => 'nullable|string|max:30',
			'extend' => 'nullable|boolean',
			'batas_hari' => 'nullable|numeric|min:1|max:90',
		], [
			'nama.required' => 'Nama jenis barang harus diisi.',
			'nama.string' => 'Nama jenis barang harus berupa teks.',
			'nama.max' => 'Nama jenis barang tidak boleh lebih dari 30 karakter.',
			'nama_tanggal_akhir.string' => 'Nama tanggal akhir harus berupa teks.',
			'nama_tanggal_akhir.max' => 'Nama tanggal akhir tidak boleh lebih dari 30 karakter.',
			'extend.boolean' => 'Extend harus berupa nilai boolean (false/true).',
			'batas_hari.numeric' => 'Batas hari harus berupa angka.',
			'batas_hari.min' => 'Batas hari minimal 1 hari.',
			'batas_hari.max' => 'Batas hari maksimal 90 hari.',
		]);

		$data = Keperluan::find($id);
		if (!$data) {
			return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
		}

		$data->nama = $request->nama;
		$data->nama_tanggal_akhir = $request->nama_tanggal_akhir;
		$data->extend = $request->extend ?? 0;
		$data->batas_hari = $request->batas_hari ?? 1;
		$data->save();

		return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui!']);
	}
	
	public function delete($id)
	{
		$keperluan = Keperluan::find($id);
		if ($keperluan) {
			$keperluan->delete();
			
			return response()->json(['success' => true, 'message' => 'Data berhasil dihapus!']);
		}

		return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.']);
	}

	public function deleteSelected(Request $request)
	{
		$ids = $request->input('ids');
		foreach ($ids as $id) {
			$keperluan = Keperluan::find($id);
			if ($keperluan) {
				$keperluan->delete();
			}
		}
		return response()->json(['success' => true, 'message' => 'Data terpilih berhasil dihapus!']);
	}
}
