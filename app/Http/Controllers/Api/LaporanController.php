<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Barang;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class LaporanController extends Controller
{

    public function stok(Request $request)
    {
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DB::table('barang_masuk')
            ->leftJoin('barang', 'barang_masuk.barang_id', '=', 'barang.id')
            ->leftJoin('supplier', 'barang.supplier_id', '=', 'supplier.id')
            ->leftJoin('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
            ->select(
                'barang.id as barang_id',
                'barang.nama as nama_barang',
                'jenis_barang.nama as nama_jenis_barang',
                'supplier.nama as nama_supplier',
                DB::raw('SUM(barang_masuk.jumlah) as jumlah'),
                'barang_masuk.tanggal'
            )
            ->groupBy('barang.id', 'barang.nama', 'jenis_barang.nama', 'supplier.nama', 'barang_masuk.tanggal')
            ->when($search, function ($query) use ($search) {
                return $query->where('barang.nama', 'like', '%' . $search . '%')
                    ->orWhere('jenis_barang.nama', 'like', '%' . $search . '%');
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('barang_masuk.tanggal', [$startDate, $endDate]);
            })
            ->orderBy('barang_masuk.tanggal', 'desc');

        $data = datatables($query);

        $stokKeseluruhan = DB::table('barang_masuk')
            ->sum('jumlah');

        // 1. Inisialisasi running total dengan total stok sebelum $startDate
        $initialTotal = DB::table('barang_masuk')
        ->where('tanggal', '<', $startDate)
        ->sum('jumlah');

        // 2. Menghasilkan daftar semua tanggal dari $startDate hingga $endDate
        $stokPerhari = DB::table(DB::raw('(WITH RECURSIVE all_dates AS (
        SELECT "' . $startDate . '" AS tanggal
        UNION ALL
        SELECT DATE_ADD(tanggal, INTERVAL 1 DAY)
        FROM all_dates
        WHERE tanggal < "' . $endDate . '"
        )
        SELECT ad.tanggal, COALESCE(SUM(bm.jumlah), 0) AS total_jumlah
        FROM all_dates ad
        LEFT JOIN barang_masuk bm ON ad.tanggal = bm.tanggal
        GROUP BY ad.tanggal
        ORDER BY ad.tanggal ASC) AS stok_perhari'))
        ->select('tanggal', 'total_jumlah')
        ->get();

        // 3. Menghitung stok kumulatif
        $cumulativeStok = [];
        $runningTotal = $initialTotal;

        foreach ($stokPerhari as $stok) {
        $runningTotal += $stok->total_jumlah;
        $cumulativeStok[] = [
            'tanggal' => $stok->tanggal,
            'jumlah' => $runningTotal
        ];
        }

        return $data->with([
            'stok_keseluruhan' => $stokKeseluruhan,
            'stok_perhari' => $cumulativeStok
        ])->toJson();
    }
    
    public function stokDetail($barangId)
    {
        $detail = DB::table('detail_barang_masuk')
            ->leftJoin('barang_masuk', 'detail_barang_masuk.barangmasuk_id', '=', 'barang_masuk.id')
            ->leftJoin('barang', 'barang_masuk.barang_id', '=', 'barang.id')
            ->leftJoin('serial_number', 'detail_barang_masuk.serial_number_id', '=', 'serial_number.id')
            ->leftJoin('status_barang', 'detail_barang_masuk.status_barang_id', '=', 'status_barang.id')
            ->select('serial_number.serial_number', 'status_barang.nama as status_barang', 'status_barang.warna as warna_status_barang', 'detail_barang_masuk.kelengkapan')
            ->where('barang.id', $barangId)
            ->orderBy('serial_number.serial_number', 'asc')
            ->get();

        return response()->json($detail);
    }
    
    public function barangmasuk(Request $request)
    {
		$search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

		$query = DB::table('barang_masuk')
			->leftJoin('barang', 'barang_masuk.barang_id', '=', 'barang.id')
			->leftJoin('supplier', 'barang.supplier_id', '=', 'supplier.id')
			->leftJoin('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
			->select(
				'barang_masuk.id as barang_masuk_id',
				'barang_masuk.keterangan',
				'barang_masuk.tanggal',
				'barang.nama as nama_barang',
				'jenis_barang.nama as nama_jenis_barang',
				'supplier.nama as nama_supplier',
				'barang_masuk.jumlah'
			)
			->when($search, function ($query) use ($search) {
				return $query->where('barang.nama', 'like', '%' . $search . '%')
					->orWhere('barang_masuk.keterangan', 'like', '%' . $search . '%')
                    ->orWhere('barang_masuk.tanggal', 'like', '%' . $search . '%');
			})
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('barang_masuk.tanggal', [$startDate, $endDate]);
            })
			->orderBy('barang_masuk.created_at', 'desc');

		return datatables($query)->toJson();
	}

    public function barangkeluarDRAFT(Request $request)
    {
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $data = DB::table('barang_keluar')
            ->leftJoin('permintaan_barang_keluar', 'barang_keluar.permintaan_id', '=', 'permintaan_barang_keluar.id')            
            ->leftJoin('customer', 'permintaan_barang_keluar.customer_id', '=', 'customer.id')
            ->leftJoin('keperluan', 'permintaan_barang_keluar.keperluan_id', '=', 'keperluan.id')
            ->select(
                'barang_keluar.*',
                'customer.nama as nama_customer', 
                'keperluan.nama as nama_keperluan',
                'permintaan_barang_keluar.id as permintaan_barang_keluar_id',
                'permintaan_barang_keluar.jumlah',
                'keperluan.extend as extend',
                DB::raw("REPLACE(keperluan.nama_tanggal_akhir, 'Tanggal ', '') as nama_tanggal_akhir")
            )
            ->selectRaw("TO_CHAR(permintaan_barang_keluar.tanggal_awal, 'DD Mon YYYY') as tanggal_awal")
            ->selectRaw("TO_CHAR(permintaan_barang_keluar.tanggal_akhir, 'DD Mon YYYY') as tanggal_akhir")
            ->when($search, function ($query) use ($search) {
                return $query->where('customer.nama', 'like', '%' . $search . '%')
                    ->orWhere('keperluan.nama', 'like', '%' . $search . '%')
                    ->orWhere('permintaan_barang_keluar.jumlah', 'like', '%' . $search . '%')
                    ->orWhere('barang_keluar.tanggal', 'like', '%' . $search . '%');
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('barang_keluar.tanggal', [$startDate, $endDate]);
            })
            ->orderBy('barang_keluar.created_at', 'desc')
            ->paginate(7);

        foreach ($data as $item) {
            $item->detail = DB::table('detail_permintaan_bk')
                ->leftJoin('serial_number_permintaan', 'detail_permintaan_bk.id', '=', 'serial_number_permintaan.detail_permintaan_bk_id')
                ->leftJoin('serial_number', 'serial_number_permintaan.serial_number_id', '=', 'serial_number.id')
                ->leftJoin('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
                ->leftJoin('barang', 'barang_masuk.barang_id', '=', 'barang.id')
                ->leftJoin('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
                ->leftJoin('supplier', 'barang.supplier_id', '=', 'supplier.id')
                ->select(
                    'serial_number.serial_number', 
                    'barang.nama as nama_barang', 
                    'jenis_barang.nama as nama_jenis_barang', 
                    'supplier.nama as nama_supplier'
                )
                ->where('detail_permintaan_bk.permintaan_barang_keluar_id', $item->permintaan_barang_keluar_id)
                ->orderBy('serial_number.serial_number', 'asc')
                ->get();
        }

        // Format tanggal untuk tampilan
        $data->getCollection()->transform(function ($item) {
            $item->tanggal_awal = \Carbon\Carbon::parse($item->tanggal_awal)->isoFormat('DD MMMM YYYY');
            $item->tanggal_akhir = \Carbon\Carbon::parse($item->tanggal_akhir)->isoFormat('DD MMMM YYYY');
            return $item;
        });

        return response()->json([
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    public function barangkeluar(Request $request)
    {
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DB::table('barang_keluar')
            ->leftJoin('permintaan_barang_keluar', 'barang_keluar.permintaan_id', '=', 'permintaan_barang_keluar.id')            
            ->leftJoin('customer', 'permintaan_barang_keluar.customer_id', '=', 'customer.id')
            ->leftJoin('keperluan', 'permintaan_barang_keluar.keperluan_id', '=', 'keperluan.id')
            ->select(
                'barang_keluar.*',
                'customer.nama as nama_customer', 
                'keperluan.nama as nama_keperluan',
                'permintaan_barang_keluar.id as permintaan_barang_keluar_id',
                'permintaan_barang_keluar.jumlah',
                'keperluan.extend as extend',
                DB::raw("REPLACE(keperluan.nama_tanggal_akhir, 'Tanggal ', '') as nama_tanggal_akhir")
            )
            ->selectRaw("TO_CHAR(permintaan_barang_keluar.tanggal_awal, 'DD Mon YYYY') as tanggal_awal")
            ->selectRaw("TO_CHAR(permintaan_barang_keluar.tanggal_akhir, 'DD Mon YYYY') as tanggal_akhir")
            ->when($search, function ($query) use ($search) {
                return $query->where('customer.nama', 'like', '%' . $search . '%')
                    ->orWhere('keperluan.nama', 'like', '%' . $search . '%')
                    ->orWhere('permintaan_barang_keluar.jumlah', 'like', '%' . $search . '%')
                    ->orWhere('barang_keluar.tanggal', 'like', '%' . $search . '%');
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                // Convert to proper date format if necessary
                $start = \Carbon\Carbon::parse($startDate)->startOfDay();
                $end = \Carbon\Carbon::parse($endDate)->endOfDay();
    
                return $query->whereBetween('barang_keluar.tanggal', [$start, $end]);
            })
            // ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            //     return $query->whereBetween('barang_keluar.tanggal', [$startDate, $endDate]);
            // })
            ->orderBy('barang_keluar.created_at', 'desc');

        return datatables($query)
            ->editColumn('tanggal_awal', function ($item) {
                return \Carbon\Carbon::parse($item->tanggal_awal)->isoFormat('DD MMMM YYYY');
            })
            ->editColumn('tanggal_akhir', function ($item) {
                return \Carbon\Carbon::parse($item->tanggal_akhir)->isoFormat('DD MMMM YYYY');
            })
            ->addColumn('detail', function ($item) {
                return DB::table('detail_permintaan_bk')
                    ->leftJoin('serial_number_permintaan', 'detail_permintaan_bk.id', '=', 'serial_number_permintaan.detail_permintaan_bk_id')
                    ->leftJoin('serial_number', 'serial_number_permintaan.serial_number_id', '=', 'serial_number.id')
                    ->leftJoin('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
                    ->leftJoin('barang', 'barang_masuk.barang_id', '=', 'barang.id')
                    ->leftJoin('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
                    ->leftJoin('supplier', 'barang.supplier_id', '=', 'supplier.id')
                    ->select(
                        'serial_number.serial_number', 
                        'barang.nama as nama_barang', 
                        'jenis_barang.nama as nama_jenis_barang', 
                        'supplier.nama as nama_supplier'
                    )
                    ->where('detail_permintaan_bk.permintaan_barang_keluar_id', $item->permintaan_barang_keluar_id)
                    ->orderBy('serial_number.serial_number', 'asc')
                    ->get();
            })
            ->toJson();
    }
    public function getDetailBarangKeluar($permintaan_barang_keluar_id)
    {
        $detail = DB::table('detail_permintaan_bk')
            ->leftJoin('serial_number_permintaan', 'detail_permintaan_bk.id', '=', 'serial_number_permintaan.detail_permintaan_bk_id')
            ->leftJoin('serial_number', 'serial_number_permintaan.serial_number_id', '=', 'serial_number.id')
            ->leftJoin('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
            ->leftJoin('barang', 'barang_masuk.barang_id', '=', 'barang.id')
            ->leftJoin('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
            ->leftJoin('supplier', 'barang.supplier_id', '=', 'supplier.id')
            ->select(
                'serial_number.serial_number', 
                'barang.nama as nama_barang', 
                'jenis_barang.nama as nama_jenis_barang', 
                'supplier.nama as nama_supplier'
            )
            ->where('detail_permintaan_bk.permintaan_barang_keluar_id', $permintaan_barang_keluar_id)
            ->orderBy('serial_number.serial_number', 'asc')
            ->get();

        return response()->json($detail);
    }

}