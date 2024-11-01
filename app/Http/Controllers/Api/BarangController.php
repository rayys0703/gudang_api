<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Barang;
use App\Models\BarangMasuk;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Exceptions\UnauthorizedException;

class BarangController extends Controller
{

	public function index(Request $request)
	{		
        // if (!$request->user()->can('item.view')) {
        //     return response()->json(['message' => 'Tidak diizinkan'], 403);
        // }

		$search = $request->input('search.value');

		$query = DB::table('barang')
			->leftJoin('supplier', 'barang.supplier_id', '=', 'supplier.id')
			->leftJoin('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
			->leftJoin('barang_masuk', 'barang.id', '=', 'barang_masuk.barang_id')
			->select('barang.id', 'barang.nama as nama_barang', 'jenis_barang.nama as nama_jenis_barang', 'supplier.nama as nama_supplier', DB::raw('SUM(barang_masuk.jumlah) as jumlah'), 'barang.keterangan as keterangan_barang')
			->groupBy('barang.id', 'barang.nama', 'barang.jenis_barang_id', 'barang.supplier_id', 'barang.keterangan', 'barang.created_at', 'barang.updated_at', 'jenis_barang.nama', 'supplier.nama')
			->orderBy('jumlah', 'desc');

		if ($search) {
			$query->where(function($q) use ($search) {
				$q->where('barang.nama', 'like', "%{$search}%")
					->orWhere('jenis_barang.nama', 'like', "%{$search}%")
					->orWhere('supplier.nama', 'like', "%{$search}%")
					->orWhere('barang.keterangan', 'like', "%{$search}%")
					->orHavingRaw('SUM(barang_masuk.jumlah) = ?', [$search]);
			});
		}

		return datatables($query)->toJson();
	}

	public function create(Request $request)
	{
		if (!$request->user()->can('item.create')) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

		$jenis_barang = DB::table('jenis_barang')->select('id', 'nama')->orderBy('nama')->get();
		$supplier = DB::table('supplier')->select('id', 'nama')->orderBy('nama')->get();
		$data = [
			'jenis_barang' => $jenis_barang,
			'supplier' => $supplier
		];
		return response()->json($data);
	}

	public function store(Request $request): JsonResponse
	{
		if (!$request->user()->can('item.create')) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }
		$request->validate([
			'nama' => 'required|string|max:255',
			'jenis_barang' => 'required|numeric',
			'supplier_id' => 'required|numeric',
			'keterangan' => 'nullable|string|max:255',
		], [
			'nama.required' => 'Nama barang harus diisi.',
			'nama.string' => 'Nama barang harus berupa teks.',
			'nama.max' => 'Nama barang tidak boleh lebih dari 255 karakter.',
			'jenis_barang.required' => 'Jenis barang harus dipilih.',
			'jenis_barang.numeric' => 'Jenis barang harus berupa angka.',
			'supplier_id.required' => 'Supplier harus dipilih.',
			'supplier_id.numeric' => 'Supplier harus dipilih.',
			'keterangan.string' => 'Keterangan harus berupa teks.',
			'keterangan.max' => 'Keterangan tidak boleh lebih dari 255 karakter.',
		]);
		
		$data = Barang::create([
			'nama' => $request->nama,
			'jenis_barang_id' => $request->jenis_barang,
			'supplier_id' => $request->supplier_id,
			'keterangan' => $request->keterangan,
		]);

		return response()->json(['success' => true, 'message' => 'Data berhasil ditambahkan!']);
	}

	public function edit($id, Request $request)
	{
		if (!$request->user()->can('item.edit')) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }
		$jenis_barang = DB::table('jenis_barang')->select('id', 'nama')->orderBy('nama')->get();
		$supplier = DB::table('supplier')->select('id', 'nama')->orderBy('nama')->get();
		$data = Barang::find($id);
		return response()->json([
			'data' => $data,
			'jenis_barang' => $jenis_barang,
			'supplier' => $supplier
		]);
	}

	public function update($id, Request $request): JsonResponse
	{
		if (!$request->user()->can('item.edit')) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }
		$request->validate([
			'nama' => 'required|string|max:255',
			'jenis_barang' => 'required|numeric',
			'supplier_id' => 'required|numeric',
			//'status' => 'required|in:Baik,Rusak',
			'keterangan' => 'nullable|string|max:255',
		], [
			'nama.required' => 'Nama barang harus diisi.',
			'nama.string' => 'Nama barang harus berupa teks.',
			'nama.max' => 'Nama barang tidak boleh lebih dari 255 karakter.',
			'jenis_barang.required' => 'Jenis barang harus dipilih.',
			'jenis_barang.numeric' => 'Jenis barang harus berupa angka.',
			'supplier_id.required' => 'Supplier harus dipilih.',
			'supplier_id.numeric' => 'Supplier harus dipilih.',
			'keterangan.string' => 'Keterangan harus berupa teks.',
			'keterangan.max' => 'Keterangan tidak boleh lebih dari 255 karakter.',
		]);

		$data = Barang::find($id);
		if (!$data) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

		if ($data) {
			$data->nama = $request->nama;
			$data->jenis_barang_id = $request->jenis_barang;
			$data->supplier_id = $request->supplier_id;
			//$data->status = $request->status;
			$data->keterangan = $request->keterangan;

			$data->save();
			return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui!']);
        }

		return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.']);
	}

	public function delete($id, Request $request)
	{
		if (!$request->user()->can('item.delete')) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }
		$data = Barang::find($id);

		BarangMasuk::where('barang_id', $id)->delete();

		$data->delete();
		return response()->json(['success' => true, 'message' => 'Data berhasil dihapus!']);
	}

	public function deleteSelected(Request $request)
	{
		if (!$request->user()->can('item.delete')) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }
		$ids = $request->input('ids');
		foreach ($ids as $id) {
			$data = Barang::find($id);
			if ($data) {
				BarangMasuk::where('barang_id', $id)->delete();
				$data->delete();
			}
		}
		return response()->json(['success' => true, 'message' => 'Data terpilih berhasil dihapus!']);
	}
}
