<?php



namespace App\Http\Controllers\Api;



use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;

use App\Models\Barang;

use App\Models\BarangMasuk;

use App\Models\DetailBarangMasuk;

use App\Models\SerialNumber;

use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\JsonResponse;

use Yajra\DataTables\Facades\DataTables;



class BarangMasukController extends Controller

{



	public function index(Request $request)

	{

		$search = $request->input('search.value');



				$query = DB::table('barang')
					->leftJoin('supplier', 'barang.supplier_id', '=', 'supplier.id')
					->leftJoin('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
					->leftJoin('barang_masuk', 'barang.id', '=', 'barang_masuk.barang_id')
					->select('barang.*', 'barang.id as barang_id', 'barang_masuk.id as barang_masuk_id', 'barang.nama as nama_barang', 'barang.keterangan as keterangan_barang', DB::raw("to_char(barang_masuk.tanggal, 'Day, DD Month YYYY') as tanggal_barang"), 'jenis_barang.nama as nama_jenis_barang', 'supplier.nama as nama_supplier', 'barang_masuk.jumlah as jumlah', 'barang_masuk.keterangan as keterangan_barangmasuk')					
					->groupBy('barang.id', 'barang_masuk.id', 'nama_barang', 'barang.jenis_barang_id', 'barang.supplier_id', 'keterangan_barang', 'tanggal_barang', 'barang.created_at', 'barang.updated_at', 'jenis_barang.nama', 'supplier.nama', 'barang_masuk.jumlah', 'keterangan_barangmasuk')
					->havingRaw('barang_masuk.jumlah > 0')
					->orderBy('barang_masuk.tanggal', 'desc');


		if ($search) {

			$query->where('barang.nama', 'like', "%{$search}%")

			->orWhere(DB::raw("to_char(barang_masuk.tanggal, 'Day, DD Month YYYY')"), 'like', "%{$search}%")
			->orWhere('supplier.nama', 'like', "%{$search}%");



			$query->where(function($q) use ($search) {

				$q->where('barang.nama', 'like', "%{$search}%");

					//->orWhere('barang.keterangan', 'like', "%{$search}%");

					// ->orHavingRaw('SUM(barang_masuk.jumlah) = ?', [$search]);

			});

		}



		return DataTables::of($query)

			// ->addColumn('detail', function ($item) {

			// 	$detail = DB::table('detail_barang_masuk')

			// 		->leftJoin('serial_number', 'detail_barang_masuk.serial_number_id', '=', 'serial_number.id')

			// 		->leftJoin('status_barang', 'detail_barang_masuk.status_barang_id', '=', 'status_barang.id')

			// 		->select('serial_number.serial_number as serial_number', 'status_barang.nama as status_barang', 'status_barang.warna as warna_status_barang', 'detail_barang_masuk.kelengkapan as kelengkapan_barang')

			// 		->where('detail_barang_masuk.barangmasuk_id', $item->barang_masuk_id)

			// 		->orderBy('serial_number.serial_number', 'asc')

			// 		->get();

			// 	return $detail;

			// })

			->editColumn('tanggal_barang', function ($item) {

				return \Carbon\Carbon::parse($item->tanggal_barang)->isoFormat('dddd, D MMMM YYYY');

			})

			->toJson();

	}	



	public function show($id)

	{

		$detail = DB::table('detail_barang_masuk')

			->leftJoin('serial_number', 'detail_barang_masuk.serial_number_id', '=', 'serial_number.id')

			->leftJoin('status_barang', 'detail_barang_masuk.status_barang_id', '=', 'status_barang.id')

			->select(
				DB::raw('CASE 
					WHEN serial_number.status = 1 THEN CONCAT(serial_number.serial_number, " (Released)")
					ELSE serial_number.serial_number
				END as serial_number'),
				'status_barang.nama as status_barang',
				'status_barang.warna as warna_status_barang',
				'detail_barang_masuk.kelengkapan as kelengkapan_barang'
			)

			->where('detail_barang_masuk.barangmasuk_id', $id)

			->orderBy('serial_number.serial_number', 'asc')

			->get();



    	return response()->json($detail);
	}



	public function create($id = null)

	{

		$jenis_barang = DB::table('jenis_barang')->select('id', 'nama')->orderBy('nama', 'asc')->get();

		$supplier = DB::table('supplier')->select('id', 'nama')->orderBy('nama', 'asc')->get();

		$status_barang = DB::table('status_barang')->select('id', 'nama')->orderBy('nama', 'asc')->get();

		$barang = DB::table('barang')->select('id', 'nama')->orderBy('nama', 'asc')->get();



		$data = [

			'jenis_barang' => $jenis_barang,

			'supplier' => $supplier,

			'status_barang' => $status_barang,

			'barang' => $barang,

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

		$barang = DB::table('barang')->where('jenis_barang_id', $id)->orderBy('nama', 'asc')->get();

		return response()->json($barang);

	}



	/*public function createSelected($id)

	{

		$barangMasuk = BarangMasuk::findOrFail($id);



		$supplier = DB::table('supplier')->select('id', 'nama')->orderBy('nama', 'asc')->get();

		$barang = DB::table('barang')->select('id', 'nama')->orderBy('nama', 'asc')->get();

		$jenis_barang = DB::table('jenis_barang')->select('id', 'nama')->orderBy('nama', 'asc')->get();

		$status_barang = DB::table('status_barang')->select('id', 'nama')->orderBy('nama', 'asc')->get();



		$bm_kode = DB::table('barang_masuk')->orderBy('id', 'desc')->value('bm_kode');



		if ($bm_kode) {

			$angkaTerakhir = intval(substr($bm_kode, 3));

			$angkaSelanjutnya = $angkaTerakhir + 1;

			$bm_kode_value = 'BM_' . str_pad($angkaSelanjutnya, 3, '0', STR_PAD_LEFT);

		} else {

			$bm_kode_value = 'BM_' . str_pad(1, 3, '0', STR_PAD_LEFT);

		}



		return view('barangmasuk.create', compact('barangMasuk', 'supplier', 'barang', 'jenis_barang', 'status_barang', 'bm_kode_value'));

	}*/



	public function store(Request $request): JsonResponse

	{

		$request->validate([

			'barang_id' => 'required|numeric',

			'keterangan' => 'nullable|string|max:255',

			'tanggal' => 'required|date:Y-m-d|before_or_equal:today',

			'serial_numbers.*' => 'required|string|max:255',

			'status_barangs.*' => 'required|exists:status_barang,id',

			'kelengkapans.*' => 'nullable|string|max:255',

		], [

			'barang_id.required' => 'Barang harus dipilih.',

			'barang_id.numeric' => 'Barang harus dipilih.',

			'keterangan.string' => 'Keterangan harus berupa teks.',

			'keterangan.max' => 'Keterangan tidak boleh lebih dari 255 karakter.',

			'tanggal.required' => 'Tanggal harus diisi.',

			'tanggal.date' => 'Format tanggal harus YYYY-MM-DD.',

			'tanggal.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini.',

			'serial_numbers.*.required' => 'Serial Number harus diisi.',

			'serial_numbers.*.string' => 'Serial Number harus berupa teks.',

			'serial_numbers.*.max' => 'Serial Number tidak boleh lebih dari 255 karakter.',

			'status_barangs.*.required' => 'Kondisi Barang harus dipilih.',

			'status_barangs.*.exists' => 'Kondisi Barang yang dipilih tidak valid.',

			'kelengkapans.string' => 'Kelengkapan harus berupa teks.',

			'kelengkapans.max' => 'Kelengkapan tidak boleh lebih dari 255 karakter.',

        ]); 

 

		$existingSerialNumbers = SerialNumber::whereIn('serial_number', $request->serial_numbers)->pluck('serial_number')->toArray();

		if (!empty($existingSerialNumbers)) {

			return response()->json(['success' => false, 'message' => 'Serial number sudah terpakai: ' . implode(', ', $existingSerialNumbers)], 422);

		}



		$barangMasuk = BarangMasuk::create([

			'barang_id' => $request->barang_id,

			'jumlah' => count($request->serial_numbers),

			'keterangan' => $request->keterangan,

			'tanggal' => $request->tanggal,

		]);



		foreach ($request->serial_numbers as $index => $serialNumber) {

			$serial = SerialNumber::create([

				'serial_number' => $serialNumber,

				'barangmasuk_id' => $barangMasuk->id,

			]);

	

			DetailBarangMasuk::create([

				'barangmasuk_id' => $barangMasuk->id,

				'serial_number_id' => $serial->id,

				'status_barang_id' => $request->status_barangs[$index],

				'kelengkapan' => $request->kelengkapans[$index],

			]);

		}



		return response()->json(['success' => true, 'message' => 'Data berhasil ditambahkan!']);

	}

	public function storeExcel(Request $request): JsonResponse
	{
		$request->validate([
			'barang_id' => 'required|string',
			'keterangan' => 'nullable|string|max:255',
			'tanggal' => 'required|date:Y-m-d|before_or_equal:today',
			'serial_numbers.*' => 'required|string|max:255',
			'status_barangs.*' => 'required|exists:status_barang,nama',
			'kelengkapans.*' => 'nullable|string|max:255',
		], [
			'barang_id.required' => 'Barang harus dipilih.',
			'barang_id.string' => 'Barang harus dipilih.',
			'keterangan.string' => 'Keterangan harus berupa teks.',
			'keterangan.max' => 'Keterangan tidak boleh lebih dari 255 karakter.',
			'tanggal.required' => 'Tanggal harus diisi.',
			'tanggal.date' => 'Format tanggal harus YYYY-MM-DD.',
			'tanggal.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini.',
			'serial_numbers.*.required' => 'Serial Number harus diisi.',
			'serial_numbers.*.string' => 'Serial Number harus berupa teks.',
			'serial_numbers.*.max' => 'Serial Number tidak boleh lebih dari 255 karakter.',
			'status_barangs.*.required' => 'Kondisi Barang harus dipilih.',
			'status_barangs.*.exists' => 'Kondisi Barang yang dipilih tidak valid.',
			'kelengkapans.string' => 'Kelengkapan harus berupa teks.',
			'kelengkapans.max' => 'Kelengkapan tidak boleh lebih dari 255 karakter.',
		]); 

		$request->barang_id = DB::table('barang')->where('nama', $request->barang_id)->value('id');

		$existingSerialNumbers = SerialNumber::whereIn('serial_number', $request->serial_numbers)->pluck('serial_number')->toArray();

		if (!empty($existingSerialNumbers)) {
			return response()->json(['success' => false, 'message' => 'Serial number sudah terpakai: ' . implode(', ', $existingSerialNumbers)], 422);
		}

		$barangMasuk = BarangMasuk::create([
			'barang_id' => $request->barang_id,
			'jumlah' => count($request->serial_numbers),
			'keterangan' => $request->keterangan,
			'tanggal' => $request->tanggal,
		]);

		foreach ($request->serial_numbers as $index => $serialNumber) {
			$statusBarangId = DB::table('status_barang')->where('nama', $request->status_barangs[$index])->value('id');

			$serial = SerialNumber::create([
				'serial_number' => $serialNumber,
				'barangmasuk_id' => $barangMasuk->id,
			]);

			DetailBarangMasuk::create([
				'barangmasuk_id' => $barangMasuk->id,
				'serial_number_id' => $serial->id,
				'status_barang_id' => $statusBarangId,
				'kelengkapan' => $request->kelengkapans[$index],
			]);
		}

		return response()->json(['success' => true, 'message' => 'Data berhasil ditambahkan!']);
	}



	/*public function edit($id)

	{

		$supplier = DB::table('supplier')->select('id', 'nama')->get();

		$barang = DB::table('barang')->select('id', 'nama')->get();



		$data = DB::table('barang_masuk')

			->leftJoin('supplier', 'barang_masuk.supplier_id', '=', 'supplier.id')

			->leftJoin('barang', 'barang_masuk.barang_id', '=', 'barang.id')

			->select('barang_masuk.*', 'supplier.nama as nama_supplier', 'barang.nama as nama_barang')

			->where('barang_masuk.id', '=', $id)

			->first();



		return view('barangmasuk.edit', compact('supplier', 'barang', 'data'));

	}



	public function update($id, Request $request): RedirectResponse

	{

		$request->validate([

			'bm_kode' => 'required|string',

			'serial_number' => 'required|numeric',

			'supplier_id' => 'required|numeric',

			'barang_id' => 'required|numeric',

			//'jumlah' => 'required|numeric|max:255',

            'keterangan' => 'string|max:255',

            'tanggal' => 'required|date:Y-m-d H:i:s',

        ]);



		$data = Barang::find($id);



		$data->bm_kode = $request->bm_kode;

		$data->serial_number = $request->serial_number;

		$data->supplier_id = $request->supplier_id;

		$data->barang_id = $request->barang_id;

		//$data->jumlah = $request->jumlah;

		$data->keterangan = $request->keterangan;

		$data->tanggal = $request->tanggal;



		$data->save();



		return redirect('/barangmasuk')->with('success', 'Anda berhasil memperbarui data!');

	}

		*/



		public function delete($id)

		{

			$data = BarangMasuk::find($id);

		

			if ($data) {

				$serialNumbers = SerialNumber::where('barangmasuk_id', $data->id)->get();

		

				foreach ($serialNumbers as $serialNumber) {

					DetailBarangMasuk::where('serial_number_id', $serialNumber->id)->delete();

					$serialNumber->delete();

				}

		

				$data->delete();

		

				return response()->json(['success' => true, 'message' => 'Data berhasil dihapus!']);

			} else {

				return response()->json(['success' => false, 'message' => 'Data tidak ditemukan!']);

			}

		}



		public function deleteSelected(Request $request)

		{

			$ids = $request->input('ids');



			foreach ($ids as $id) {

				$data = BarangMasuk::find($id);



				if ($data) {

					$serialNumbers = SerialNumber::where('barangmasuk_id', $data->id)->get();



					foreach ($serialNumbers as $serialNumber) {

						DetailBarangMasuk::where('serial_number_id', $serialNumber->id)->delete();

						$serialNumber->delete();

					}



					$data->delete();

				}

			}



			return response()->json(['success' => true, 'message' => 'Data terpilih berhasil dihapus!']);

		}



}

