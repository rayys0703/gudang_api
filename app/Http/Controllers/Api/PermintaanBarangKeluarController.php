<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\SerialNumber;
use App\Models\PermintaanBarangKeluar;
use App\Models\DetailPermintaanBarangKeluar;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PermintaanBarangKeluarController extends Controller
{

	public function index(Request $request)
	{
		if (!$request->user()->can('item request.viewAll')) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

		$search = $request->input('search.value');
		$query = DB::table('permintaan_barang_keluar')
			->leftJoin('customer', 'permintaan_barang_keluar.customer_id', '=', 'customer.id')
			->leftJoin('keperluan', 'permintaan_barang_keluar.keperluan_id', '=', 'keperluan.id')
			->select(
				'permintaan_barang_keluar.*',
				'customer.nama as nama_customer',
				'keperluan.nama as nama_keperluan',
				'permintaan_barang_keluar.id as permintaan_barang_keluar_id',
				'permintaan_barang_keluar.jumlah as jumlah_permintaan',
				'keperluan.extend as extend',
				DB::raw("REPLACE(keperluan.nama_tanggal_akhir, 'Tanggal ', '') as nama_tanggal_akhir"),
				DB::raw("DATE_FORMAT(permintaan_barang_keluar.tanggal_awal, '%d %b %Y') as tanggal_awal_permintaan"),
				DB::raw("DATE_FORMAT(permintaan_barang_keluar.tanggal_akhir, '%d %b %Y') as tanggal_akhir_permintaan")			)
			->orderBy('permintaan_barang_keluar.created_at', 'desc')
			->orderBy('permintaan_barang_keluar.status', 'asc');

		if ($search) {
			$query->where(function($q) use ($search) {
				$q->where('customer.nama', 'like', '%' . $search . '%')
					->orWhere('keperluan.nama', 'like', '%' . $search . '%')
					->orWhere('permintaan_barang_keluar.jumlah', 'like', '%' . $search . '%')
					->orWhere('permintaan_barang_keluar.status', 'like', '%' . $search . '%')
					->orWhere('permintaan_barang_keluar.tanggal_awal', 'like', '%' . $search . '%');
			});
		}

		return DataTables::of($query)
			->editColumn('tanggal_awal', function ($item) {
				return \Carbon\Carbon::parse($item->tanggal_awal_permintaan)->isoFormat('D MMMM YYYY');
			})
			->editColumn('tanggal_akhir', function ($item) {
				return \Carbon\Carbon::parse($item->tanggal_akhir_permintaan)->isoFormat('D MMMM YYYY');
			})
			->toJson();
	}

	public function indexForOneUser(Request $request)
	{
		if (!$request->user()->can('item request.viewFilterbyUser')) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

		$user = $request->user();
		$search = $request->input('search.value');

		$query = DB::table('permintaan_barang_keluar')
			->leftJoin('customer', 'permintaan_barang_keluar.customer_id', '=', 'customer.id')
			->leftJoin('keperluan', 'permintaan_barang_keluar.keperluan_id', '=', 'keperluan.id')
			->select(
				'permintaan_barang_keluar.*',
				'customer.nama as nama_customer',
				'keperluan.nama as nama_keperluan',
				'permintaan_barang_keluar.id as permintaan_barang_keluar_id',
				'permintaan_barang_keluar.jumlah as jumlah_permintaan',
				'keperluan.extend as extend',
				DB::raw("REPLACE(keperluan.nama_tanggal_akhir, 'Tanggal ', '') as nama_tanggal_akhir"),
				DB::raw("DATE_FORMAT(permintaan_barang_keluar.tanggal_awal, '%d %b %Y') as tanggal_awal_permintaan"),
				DB::raw("DATE_FORMAT(permintaan_barang_keluar.tanggal_akhir, '%d %b %Y') as tanggal_akhir_permintaan")			
			)
			->where('permintaan_barang_keluar.created_by', $user->id)
			->orderBy('permintaan_barang_keluar.created_at', 'desc')
			->orderBy('permintaan_barang_keluar.status', 'asc');

		if ($search) {
			$query->where(function($q) use ($search) {
				$q->where('customer.nama', 'like', '%' . $search . '%')
					->orWhere('keperluan.nama', 'like', '%' . $search . '%')
					->orWhere('permintaan_barang_keluar.jumlah', 'like', '%' . $search . '%')
					->orWhere('permintaan_barang_keluar.status', 'like', '%' . $search . '%')
					->orWhere('permintaan_barang_keluar.tanggal_awal', 'like', '%' . $search . '%');
			});
		}

		return DataTables::of($query)
			->editColumn('tanggal_awal', function ($item) {
				return \Carbon\Carbon::parse($item->tanggal_awal_permintaan)->isoFormat('D MMMM YYYY');
			})
			->editColumn('tanggal_akhir', function ($item) {
				return \Carbon\Carbon::parse($item->tanggal_akhir_permintaan)->isoFormat('D MMMM YYYY');
			})
			->toJson();
	}	

	public function show($id)
	{	
		$detail = DB::table('detail_permintaan_bk')
			->leftJoin('barang', 'detail_permintaan_bk.barang_id', '=', 'barang.id')
			->leftJoin('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
			->leftJoin('supplier', 'barang.supplier_id', '=', 'supplier.id')
			->select(
				'barang.id as barang_id',
				'barang.nama as nama_barang',
				'jenis_barang.nama as nama_jenis_barang', 
				'supplier.nama as nama_supplier',
				DB::raw('SUM(detail_permintaan_bk.jumlah) as total_barang'),
				//DB::raw('COUNT(detail_permintaan_bk.barang_id) as total_count')
			)
			->where('detail_permintaan_bk.permintaan_barang_keluar_id', $id)
			->groupBy('barang.id', 'barang.nama', 'jenis_barang.nama', 'supplier.nama')
			->orderBy('barang.nama', 'asc')
			->get();		
		
		return response()->json($detail);	
	}

	public function showDetailSN($id)
	{
		$serialNumbers = DB::table('detail_permintaan_bk')
			->leftJoin('serial_number_permintaan', 'detail_permintaan_bk.id', '=', 'serial_number_permintaan.detail_permintaan_bk_id')
			->leftJoin('barang', 'detail_permintaan_bk.barang_id', '=', 'barang.id')
			->leftJoin('serial_number', 'serial_number_permintaan.serial_number_id', '=', 'serial_number.id')
			->select(
				'barang.id as barang_id',
				'barang.nama as nama_barang',
				'serial_number.serial_number'
			)
			->where('detail_permintaan_bk.permintaan_barang_keluar_id', $id)
			->orderBy('serial_number.serial_number', 'asc')
			->get();

		return response()->json($serialNumbers);
	}

	public function create(Request $request)
	{
		// $id = null;

		if (!$request->user()->can('item request.create')) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

		$jenis_barang = DB::table('barang_masuk')
			->join('barang', 'barang_masuk.barang_id', '=', 'barang.id')
			->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
			->join('detail_barang_masuk', 'barang_masuk.id', '=', 'detail_barang_masuk.barangmasuk_id')
			->join('status_barang', 'detail_barang_masuk.status_barang_id', '=', 'status_barang.id')
			->join('serial_number', 'detail_barang_masuk.serial_number_id', '=', 'serial_number.id')
			//->where('barang.jenis_barang_id', $id)
			->where('serial_number.status', false) // Filter SN yang belum digunakan
			//->where('detail_barang_masuk.status_barang_id', 1) // Filter SN dengan kondisi barang 'Baik'
			->where('status_barang.nama', 'Baik')
			->select('jenis_barang.id', 'jenis_barang.nama')
			->distinct()
			->orderBy('jenis_barang.nama', 'asc')
			->get();
		// $jenis_barang = DB::table('jenis_barang')->select('id', 'nama')->orderBy('nama', 'asc')->get();
		$barang = DB::table('barang')
			->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
			->select('barang.id', 'barang.nama', 'jenis_barang.nama as jenis_barang_nama')
			->orderBy('jenis_barang.nama', 'asc')
			->orderBy('barang.nama', 'asc')			
			->get();
		$customer = DB::table('customer')->select('id', 'nama')->orderBy('nama', 'asc')->get();
		$keperluan = DB::table('keperluan')
			->select('id', 'nama', 'extend', 'nama_tanggal_akhir', 'batas_hari')
			->orderBy('nama', 'asc')->get();

		$data = [
			'jenis_barang' => $jenis_barang,
			'barang' => $barang,
			'customer' => $customer,
			'keperluan' => $keperluan,
			'barangMasuk' => null,
			'barangbyjenis' => null,
			'jenis_barang_id' => null
		];

		// if ($id !== null) {
		// 	$barangMasuk = DB::table('barang_masuk')->where('id', $id)->first();
		// 	$jenis_barang_id = DB::table('barang')
		// 		->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
		// 		->where('barang.id', $barangMasuk->barang_id)
		// 		->value('jenis_barang.id');
		// 	$barangbyjenis = DB::table('barang')->where('jenis_barang_id', $jenis_barang_id)->orderBy('nama', 'asc')->get();

		// 	$data['barangMasuk'] = $barangMasuk;
		// 	$data['jenis_barang_id'] = $jenis_barang_id;
		// 	$data['barangbyjenis'] = $barangbyjenis;
		// }

		return response()->json($data);
	}

	public function getBarangByJenis($id, Request $request)
	{
		if (!$request->user()->can('item request.create')) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

		// $barang = DB::table('serial_number')
		// 	->join('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
		// 	->join('detail_barang_masuk', 'barang_masuk.id', '=', 'detail_barang_masuk.barangmasuk_id')
		// 	->join('barang', 'barang_masuk.barang_id', '=', 'barang.id')
		// 	->where('barang.jenis_barang_id', $id)
		// 	->where('serial_number.status', false)
		// 	->where('detail_barang_masuk.status_barang_id', 1)
		// 	->select('barang.id', 'barang.nama')
		// 	->distinct()
		// 	->orderBy('barang.nama', 'asc')
		// 	->get();
		$barang = DB::table('barang_masuk')
			->join('barang', 'barang_masuk.barang_id', '=', 'barang.id')
			->join('detail_barang_masuk', 'barang_masuk.id', '=', 'detail_barang_masuk.barangmasuk_id')
			->join('status_barang', 'detail_barang_masuk.status_barang_id', '=', 'status_barang.id')
			->join('serial_number', 'detail_barang_masuk.serial_number_id', '=', 'serial_number.id')
			->where('barang.jenis_barang_id', $id)
			->where('serial_number.status', false)
			//->where('detail_barang_masuk.status_barang_id', 1)
			->where('status_barang.nama', 'Baik')
			->select('barang.id', 'barang.nama')
			->distinct()
			->orderBy('barang.nama', 'asc')
			->get();
			
		return response()->json($barang);
	}

	public function getStok($barang_id, Request $request)
	{
		if (!$request->user()->can('item request.create')) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

		$stok = DB::table('serial_number')
			->join('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
			->join('barang', 'barang_masuk.barang_id', '=', 'barang.id')
			->join('detail_barang_masuk', 'serial_number.id', '=', 'detail_barang_masuk.serial_number_id')
			->join('status_barang', 'detail_barang_masuk.status_barang_id', '=', 'status_barang.id')
			->where('barang.id', $barang_id)
			->where('serial_number.status', false)
			//->where('detail_barang_masuk.status_barang_id', 1)
			->where('status_barang.nama', 'Baik')
			->count();

		return response()->json(['stok' => $stok]);
	}

	public function getSerialNumberByBarang($id, Request $request)
	{
		if (!$request->user()->can('item request.create')) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

		$serialnumber = DB::table('serial_number')
			->join('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
			->where('barang_masuk.barang_id', $id)
			/*->whereNotExists(function ($query) {
				$query->select(DB::raw(1))
					->from('permintaan_barang_keluar')
					->whereRaw('permintaan_barang_keluar.barangmasuk_id = serial_number.id')
					->where(function ($subQuery) {
						$subQuery->whereNull('permintaan_barang_keluar.status')
							->orWhere('permintaan_barang_keluar.status', '!=', 'Ditolak');
					});
			})*/
			->orderBy('serial_number.serial_number', 'asc')
			->pluck('serial_number.serial_number');

		return response()->json($serialnumber);
	}

	public function store(Request $request): JsonResponse
	{
		if (!$request->user()->can('item request.create')) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

		$request->validate([
			// 'serial_numbers' => 'required|array',
			// 'serial_numbers.*' => 'required|numeric',
			'barang_ids' => 'required|array',
			'barang_ids.*' => 'required|numeric',
			'jumlah_barangs' => 'required|array',
			'jumlah_barangs.*' => 'required|numeric',
			'customer_id' => 'required|numeric',
			'keperluan_id' => 'required|numeric',
			'keterangan' => 'nullable|string|max:255',
			// 'tanggal_awal' => 'required|date_format:Y-m-d',
			// 'tanggal_akhir' => 'nullable|date_format:Y-m-d',
		], [
			// 'serial_numbers.required' => 'Serial Number harus diisi.',
			// 'serial_numbers.array' => 'Serial Number harus berupa array.',
			// 'serial_numbers.*.required' => 'Setiap Serial Number harus diisi.',
			// 'serial_numbers.*.numeric' => 'Serial Number harus berupa angka.',
			'barang_ids.required' => 'Serial Number harus diisi.',
			'barang_ids.array' => 'Barang harus berupa array.',
			'barang_ids.*.required' => 'Setiap Barang harus diisi.',
			'barang_ids.*.numeric' => 'Barang harus berupa angka.',
			'jumlah_barangs.required' => 'Jumlah barang harus diisi.',
			'jumlah_barangs.array' => 'Jumlah barang harus berupa array.',
			'jumlah_barangs.*.required' => 'Setiap Jumlah barang harus diisi.',
			'jumlah_barangs.*.numeric' => 'Jumlah barang harus berupa angka.',
			'customer_id.required' => 'Penerima harus dipilih.',
			'customer_id.numeric' => 'ID Penerima barang harus berupa angka.',
			'keperluan_id.required' => 'Keperluan harus dipilih.',
			'keperluan_id.numeric' => 'ID Keperluan harus berupa angka.',
			'keterangan.string' => 'Keterangan harus berupa teks.',
			'keterangan.max' => 'Keterangan tidak boleh lebih dari 255 karakter.',
			// 'tanggal_awal.required' => 'Tanggal harus diisi.',
			// 'tanggal_awal.date_format' => 'Format tanggal harus YYYY-MM-DD.',
			// 'tanggal_akhir.date_format' => 'Format tanggal harus YYYY-MM-DD.',
		]);

		$jumlah = array_sum($request->jumlah_barangs);

		$permintaan = PermintaanBarangKeluar::create([
			'customer_id' => $request->customer_id,
			'keperluan_id' => $request->keperluan_id,
			'jumlah' => $jumlah,
			'keterangan' => $request->keterangan,
			// 'tanggal_awal' => $request->tanggal_awal,
			'tanggal_awal' => now(),
			'tanggal_akhir' => $request->tanggal_akhir ?? null,
			'created_by' => auth()->id(),
		]);

		foreach ($request->barang_ids as $index => $barangId) {
			$barangIdData = DB::table('barang')
				->where('id', $barangId)
				->first();

			if (!$barangIdData) {
				return response()->json(['success' => false, 'message' => 'Barang ' . $barangId . ' tidak ditemukan.'], 400);
			}

			$detailId = DB::table('detail_permintaan_bk')->insertGetId([
				'permintaan_barang_keluar_id' => $permintaan->id,
				'barang_id' => $barangIdData->id,
				'jumlah' => $request->jumlah_barangs[$index],
				'keterangan' => $request->keterangan,
			]);

			// for ($i = 0; $i < $request->jumlah_barangs[$index]; $i++) {
			// 	DB::table('serial_number_permintaan')->insert([
			// 		'detail_permintaan_bk_id' => $detailId,
			// 		'serial_number_id' => null,
			// 	]);
			// }
		}

		return response()->json(['success' => true, 'message' => 'Berhasil membuat permintaan barang keluar!']);
	}

	public function delete($id, Request $request)
	{
		if (!$request->user()->can('item request.delete')) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

		$data = PermintaanBarangKeluar::find($id);

		if (!$data) {
			return response()->json(['success' => false, 'message' => 'Data permintaan tidak ditemukan.'], 404);
		}

		// Hapus detail permintaan barang keluar
		DB::table('detail_permintaan_bk')
			->where('permintaan_barang_keluar_id', $id)
			->delete();

		$data->delete();

		return response()->json(['success' => true, 'message' => 'Data berhasil dihapus!']);
	}

	public function updateStatus(Request $request)
	{
		$request->validate([
			'id' => 'required|numeric',
			'status' => 'required|string',
			'reason' => 'nullable|string|max:150',
		]);

		$permintaan = PermintaanBarangKeluar::findOrFail($request->id);

		if (in_array($permintaan->status, ['Belum Disetujui'])) {

			if ($request->status === 'Diproses') {
				/////////

				$permintaan->status = $request->status;
				$permintaan->save();

				return response()->json([
					'success' => true,
					'message' => 'Status permintaan berhasil diproses',
					'data' => $permintaan
				]);

			} elseif ($request->status === 'Ditolak') {
				$permintaan->status = $request->status;
				$permintaan->alasan = $request->reason;
				$permintaan->save();

				return response()->json([
					'success' => true,
					'message' => 'Status permintaan berhasil ditolak',
					'data' => $permintaan
				]);
			}
		}

		return response()->json([
			'success' => false,
			'message' => 'Status permintaan tidak dapat diubah karena sudah disetujui atau ditolak',
			'data' => $permintaan
		]);
	}

	public function selectSN($id)
	{
		$detailPermintaan = DB::table('detail_permintaan_bk')
			->where('permintaan_barang_keluar_id', $id)
			->get();

		if ($detailPermintaan->isEmpty()) {
			return response()->json([
				'success' => false,
				'message' => 'Permintaan barang keluar tidak ditemukan'
			], 404);
		}

		$permintaan = PermintaanBarangKeluar::findOrFail($id);

		if ($permintaan->status === 'Disetujui' || $permintaan->status === 'Ditolak') {
			return response()->json([
				'success' => false,
				'message' => 'Permintaan barang keluar sudah disetujui atau ditolak'
			], 404);
		}

		$serialNumbers = DB::table('detail_permintaan_bk')
			->join('barang', 'detail_permintaan_bk.barang_id', '=', 'barang.id')
			->join('barang_masuk', 'barang.id', '=', 'barang_masuk.barang_id')
			->join('serial_number', 'barang_masuk.id', '=', 'serial_number.barangmasuk_id')
			->join('detail_barang_masuk', 'serial_number.id', '=', 'detail_barang_masuk.serial_number_id')
			->join('status_barang', 'detail_barang_masuk.status_barang_id', '=', 'status_barang.id')
			->where('detail_permintaan_bk.permintaan_barang_keluar_id', $id)
			->where('serial_number.status', 0)
			//->where('detail_barang_masuk.status_barang_id', 1)
			->where('status_barang.nama', 'Baik')
			->select(DB::raw('ROW_NUMBER() OVER (PARTITION BY barang.nama ORDER BY serial_number.id) AS id'), 
				'serial_number.id as serial_number_id',
				'serial_number.serial_number',
				'barang.id as barang_id', 
				'barang.nama as nama_barang',
				'detail_permintaan_bk.jumlah')
			->orderBy('barang.nama', 'asc')
			->orderBy('serial_number.serial_number', 'asc')
			->get();

		if ($serialNumbers->isEmpty()) {
			return response()->json([
				'success' => false,
				'message' => 'Serial Number tidak ditemukan atau sudah terpakai'
			], 404);
		}

		return response()->json($serialNumbers);
	}

	public function setSN(Request $request)
	{
		$validated = $request->validate([
			'permintaan_id' => 'required|integer',
			'serial_number_ids' => 'required|array',
			'serial_number_ids.*' => 'required|array',  // Barang ID
			'serial_number_ids.*.*' => 'required|integer',  // SN ID
		]);

		// Cek apakah semua serial number yang dipilih unik
		$allSerialNumbers = collect($validated['serial_number_ids'])->flatten()->toArray();
		$uniqueSerialNumbers = array_unique($allSerialNumbers);

		if (count($allSerialNumbers) !== count($uniqueSerialNumbers)) {
			return response()->json(['success' => false, 'message' => 'Terdapat serial number yang sama. Semua serial number harus unik.'], 422);
		}

		$permintaanBarang = DB::table('permintaan_barang_keluar')
			->where('id', $validated['permintaan_id'])
			->first();

		if (!$permintaanBarang) {
			return response()->json(['success' => false, 'message' => 'Permintaan barang tidak ditemukan.'], 404);
		}

		$detailPermintaan = DB::table('detail_permintaan_bk')
			->where('permintaan_barang_keluar_id', $permintaanBarang->id)
			->get();

		foreach ($detailPermintaan as $detail) {
			// Ambil serial numbers yang dikirim dari request sesuai dengan barang_id
			$serialNumbers = $validated['serial_number_ids'][$detail->barang_id] ?? [];

			// Cek apakah jumlah serial number yang dikirim cukup
			if (count($serialNumbers) < $detail->jumlah) {
				return response()->json(['success' => false, 'message' => 'Jumlah serial number yang dikirim tidak mencukupi.'], 400);
			}

			// Loop serial numbers yang dipilih
			foreach ($serialNumbers as $serialNumberId) {
				// Pastikan serial number tersedia dan belum digunakan
				$serialNumber = DB::table('serial_number')
					->join('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
					->join('detail_barang_masuk', 'barang_masuk.id', '=', 'detail_barang_masuk.barangmasuk_id')
					->join('status_barang', 'detail_barang_masuk.status_barang_id', '=', 'status_barang.id')
					->where('serial_number.id', $serialNumberId)
					->where('barang_masuk.barang_id', $detail->barang_id)
					->where('serial_number.status', 0)  // Serial number belum digunakan
					//->where('detail_barang_masuk.status_barang_id', 1)  // Status barang "Baik"
					->where('status_barang.nama', 'Baik')
					->select('serial_number.*')
					->first();

				if (!$serialNumber) {
					return response()->json(['success' => false, 'message' => 'Terdapat serial number yang sudah digunakan.'], 404);
				}
			}

			// Setelah semua pengecekan selesai, lakukan insert dan update data
			foreach ($serialNumbers as $serialNumberId) {
				$serialNumber = DB::table('serial_number')->where('id', $serialNumberId)->first();

				// Insert serial_number_permintaan
				DB::table('serial_number_permintaan')->insert([
					'detail_permintaan_bk_id' => $detail->id,
					'serial_number_id' => $serialNumber->id,
				]);

				// Update jumlah barang masuk di tabel barang_masuk
				// DB::table('barang_masuk')
				// 	->where('id', $serialNumber->barangmasuk_id)
				// 	->decrement('jumlah', 1);

				// Tandai serial number sebagai sudah digunakan
				DB::table('serial_number')
					->where('id', $serialNumber->id)
					->update(['status' => 1]);
			}
		}

		// Insert data ke barang_keluar setelah semua serial number berhasil diambil
		DB::table('barang_keluar')->insert([
			'permintaan_id' => $permintaanBarang->id,
			'tanggal' => now(),
			'created_at' => now(),
		]);

		DB::table('permintaan_barang_keluar')
			->where('id', $permintaanBarang->id)
			->update(['status' => 'Disetujui']);

		return response()->json(['success' => true, 'message' => 'Serial number berhasil disimpan dan barang berhasil dikeluarkan.'], 200);
	}

}
