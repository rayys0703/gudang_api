<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\SerialNumber;
use App\Models\PermintaanBarangKeluar;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class PermintaanBarangKeluarController extends Controller
{

	public function index(Request $request)
	{
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
				//DB::raw("REPLACE(keperluan.nama_tanggal_awal, 'Tanggal ', '') as nama_tanggal_awal"),
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
			->get();		
		
		return response()->json($detail);	
	}

	public function create($id = null)
	{
		$jenis_barang = DB::table('jenis_barang')->select('id', 'nama')->orderBy('nama', 'asc')->get();
		$barang = DB::table('barang')
			->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
			->select('barang.id', 'barang.nama', 'jenis_barang.nama as jenis_barang_nama')
			->orderBy('jenis_barang.nama', 'asc')
			->orderBy('barang.nama', 'asc')			
			->get();
		$customer = DB::table('customer')->select('id', 'nama')->orderBy('nama', 'asc')->get();
		$keperluan = DB::table('keperluan')
			->select('id', 'nama', 'extend', 'nama_tanggal_akhir')
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

		if ($id !== null) {
			$barangMasuk = DB::table('barang_masuk')->where('id', $id)->first();
			$jenis_barang_id = DB::table('barang')
				->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
				->where('barang.id', $barangMasuk->barang_id)
				->value('jenis_barang.id');
			$barangbyjenis = DB::table('barang')->where('jenis_barang_id', $jenis_barang_id)->orderBy('nama', 'asc')->get();

			$data['barangMasuk'] = $barangMasuk;
			$data['jenis_barang_id'] = $jenis_barang_id;
			$data['barangbyjenis'] = $barangbyjenis;
		}

		return response()->json($data);
	}

	public function getBarangByJenis($id)
	{
		$barang = DB::table('serial_number')
			->join('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
			->join('barang', 'barang_masuk.barang_id', '=', 'barang.id')
			->where('barang.jenis_barang_id', $id)
			->where('serial_number.status', false)
			->select('barang.id', 'barang.nama')
			->distinct()
			->orderBy('barang.nama', 'asc')
			->get();
		return response()->json($barang);
	}

	public function getStok($barang_id)
	{
		$stok = DB::table('serial_number')
			->join('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
			->join('barang', 'barang_masuk.barang_id', '=', 'barang.id')
			->where('barang.id', $barang_id)
			->where('serial_number.status', false)
			->count();

		return response()->json(['stok' => $stok]);
	}

	public function getSerialNumberByBarang($id)
	{
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
			'tanggal_awal' => 'required|date_format:Y-m-d',
			'tanggal_akhir' => 'nullable|date_format:Y-m-d',
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
			'tanggal_awal.required' => 'Tanggal harus diisi.',
			'tanggal_awal.date_format' => 'Format tanggal harus YYYY-MM-DD.',
			'tanggal_akhir.date_format' => 'Format tanggal harus YYYY-MM-DD.',
		]);

		$jumlah = array_sum($request->jumlah_barangs);

		$permintaan = PermintaanBarangKeluar::create([
			'customer_id' => $request->customer_id,
			'keperluan_id' => $request->keperluan_id,
			'jumlah' => $jumlah,
			'keterangan' => $request->keterangan,
			'tanggal_awal' => $request->tanggal_awal,
			'tanggal_akhir' => $request->tanggal_akhir ?? null,
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

		return response()->json(['success' => true, 'message' => 'Data berhasil ditambahkan!']);
	}

	public function delete($id)
	{
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
		// Validasi input
		$request->validate([
			'id' => 'required|numeric',
			'status' => 'required|string',
		]);

		// Temukan permintaan berdasarkan ID
		$permintaan = PermintaanBarangKeluar::findOrFail($request->id);

		// Cek apakah status permintaan dapat diubah
		if (!in_array($permintaan->status, ['Disetujui', 'Ditolak'])) {
			// Perbarui status permintaan
			$permintaan->status = $request->status;
			$permintaan->save();

			// Jika status permintaan adalah 'Disetujui'
			if ($request->status === 'Disetujui') {
				// Ambil detail permintaan
				$detailPermintaan = DB::table('detail_permintaan_bk')
					->where('permintaan_barang_keluar_id', $permintaan->id)
					->get();

				// Menyimpan id serial number yang telah dipilih
				$serialNumbersUsed = [];

				foreach ($detailPermintaan as $detail) {
					// Cek jumlah barang di barang_masuk yang cukup
					$availableStock = DB::table('barang_masuk')
						->where('barang_id', $detail->barang_id)
						->sum('jumlah');

					// Jika stock tidak cukup, kembalikan respon gagal
					if ($availableStock < $detail->jumlah) {
						return response()->json([
							'success' => false,
							'message' => 'Stok tidak cukup untuk memenuhi permintaan.',
							'data' => $permintaan,
						]);
					}

					// Ambil serial number yang belum digunakan dan sesuai dengan barang_id
					$serialNumbers = DB::table('serial_number')
						->where('barangmasuk_id', $detail->barang_id)
						->where('status', 0) // Hanya ambil yang status 0
						->take($detail->jumlah) // Ambil sesuai dengan jumlah yang diminta
						->get();

					// Update serial_number_permintaan dengan serial number yang diambil
					foreach ($serialNumbers as $serialNumber) {
						// Periksa untuk menghindari duplikasi
						if (!in_array($serialNumber->id, $serialNumbersUsed)) {
							// Menyisipkan ke tabel serial_number_permintaan
							DB::table('serial_number_permintaan')->insert([
								'detail_permintaan_bk_id' => $detail->id,
								'serial_number_id' => $serialNumber->id,
							]);

							// Kurangi jumlah barang di tabel barang_masuk
							DB::table('barang_masuk')
								->where('id', $serialNumber->barangmasuk_id)
								->decrement('jumlah', 1);

							// Tandai serial number sebagai digunakan
							$serialNumbersUsed[] = $serialNumber->id;

							// Perbarui status serial number menjadi 1 (true)
							DB::table('serial_number')
								->where('id', $serialNumber->id)
								->update(['status' => 1]);
						}
					}
				}

				// Insert data ke barang_keluar
				DB::table('barang_keluar')->insert([
					'permintaan_id' => $permintaan->id,
					'tanggal' => now(),
					//'keterangan' => 'Diterima', // bisa diubah sesuai kebutuhan
				]);
			}

			// Mengembalikan respon sukses
			return response()->json([
				'success' => true,
				'message' => 'Status permintaan berhasil diperbarui',
				'data' => $permintaan,
			]);
		}

		// Mengembalikan respon gagal jika status tidak dapat diubah
		return response()->json([
			'success' => false,
			'message' => 'Status permintaan tidak dapat diubah karena sudah disetujui atau ditolak',
			'data' => $permintaan,
		]);
	}

}
