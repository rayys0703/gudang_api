<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Mendapatkan 7 hari terakhir
        $dates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dates->push(Carbon::today()->subDays($i)->toDateString());
        }

        // Query untuk barang 7 hari terakhir
        $startDate = now()->subDays(6)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        // 1. Inisialisasi running total dengan total barang sebelum $startDate
        $initialTotal = DB::table('barang')
            ->where('created_at', '<', $startDate)
            ->count();

        // 2. Menghasilkan daftar semua tanggal dari $startDate hingga $endDate dengan sintaks PostgreSQL
        $barangPerhari = DB::table(DB::raw("(WITH RECURSIVE all_dates AS (
            SELECT '$startDate'::timestamp AS tanggal
            UNION ALL
            SELECT (tanggal + INTERVAL '1 day')::timestamp
            FROM all_dates
            WHERE tanggal < '$endDate'::timestamp
            )
            SELECT ad.tanggal::date, COALESCE(COUNT(b.id), 0) AS total_jumlah
            FROM all_dates ad
            LEFT JOIN barang b ON DATE(b.created_at) = ad.tanggal::date
            GROUP BY ad.tanggal
            ORDER BY ad.tanggal ASC) AS barang_perhari"))
            ->select('tanggal', 'total_jumlah')
            ->get();

        // 3. Menghitung barang kumulatif
        $cumulativeBarang = [];
        $runningTotal = $initialTotal;

        foreach ($barangPerhari as $barang) {
            $runningTotal += $barang->total_jumlah;
            $cumulativeBarang[] = $runningTotal;
        }

        $counts_barang = $cumulativeBarang;

        // Query untuk barang masuk 7 hari terakhir        
        $counts_barang_masuk = DB::table('serial_number')
            ->join('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
            ->join('barang', 'barang_masuk.barang_id', '=', 'barang.id')
            ->select(DB::raw('DATE(barang_masuk.tanggal) as date'), DB::raw('COUNT(*) as count'))
            ->whereBetween('barang_masuk.tanggal', [Carbon::now()->subDays(6), Carbon::now()])
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get()
            ->keyBy('date');

        // Query untuk barang keluar 7 hari terakhir
        $counts_barang_keluar = DB::table('detail_permintaan_bk')
            ->join('permintaan_barang_keluar', 'detail_permintaan_bk.permintaan_barang_keluar_id', '=', 'permintaan_barang_keluar.id')
            ->join('barang_keluar', 'detail_permintaan_bk.permintaan_barang_keluar_id', '=', 'barang_keluar.permintaan_id')
            ->select(DB::raw('DATE(permintaan_barang_keluar.updated_at) as date'), DB::raw('SUM(detail_permintaan_bk.jumlah) as count'))
            ->whereBetween('permintaan_barang_keluar.updated_at', [Carbon::now()->subDays(6), Carbon::now()])
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get()
            ->keyBy('date');

        // Query untuk permintaan barang 7 hari terakhir
        $counts_permintaan = DB::table('permintaan_barang_keluar')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [Carbon::now()->subDays(6), Carbon::now()])
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get()
            ->keyBy('date');

        // Query untuk permintaan diterima 7 hari terakhir
        $counts_req_rejected = DB::table('permintaan_barang_keluar')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('status', 'Rejected')
            ->where('created_by', auth()->id())
            ->whereBetween('created_at', [Carbon::now()->subDays(6), Carbon::now()])
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get()
            ->keyBy('date');

        // Query untuk permintaan disetujui 7 hari terakhir
        $counts_req_approved = DB::table('permintaan_barang_keluar')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('status', 'Approved')
            ->where('created_by', auth()->id())
            ->whereBetween('created_at', [Carbon::now()->subDays(6), Carbon::now()])
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get()
            ->keyBy('date');

        // Query untuk permintaan pending 7 hari terakhir
        $counts_req_pending = DB::table('permintaan_barang_keluar')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('status', 'Pending')
            ->where('created_by', auth()->id())
            ->whereBetween('created_at', [Carbon::now()->subDays(6), Carbon::now()])
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get()
            ->keyBy('date');

        // Mendapatkan 6 bulan terakhir
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $months->push(Carbon::today()->startOfMonth()->subMonths($i)->format('Y-m'));
        }

        // Query untuk barang masuk 6 bulan terakhir
        $counts_barang_masuk_6months = DB::table('serial_number')
            ->join('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
            ->join('barang', 'barang_masuk.barang_id', '=', 'barang.id')
            ->select(DB::raw("to_char(barang_masuk.tanggal, 'YYYY-MM') as month"), DB::raw('COUNT(*) as count'))            
            ->whereBetween('barang_masuk.tanggal', [Carbon::now()->subMonths(5)->startOfMonth(), Carbon::now()->endOfMonth()])
            ->groupBy('month')
            ->orderBy('month', 'DESC')
            ->get()
            ->keyBy('month');

        // Query untuk barang keluar 6 bulan terakhir
        $counts_barang_keluar_6months = DB::table('detail_permintaan_bk')
            ->join('permintaan_barang_keluar', 'detail_permintaan_bk.permintaan_barang_keluar_id', '=', 'permintaan_barang_keluar.id')
            ->join('barang_keluar', 'detail_permintaan_bk.permintaan_barang_keluar_id', '=', 'barang_keluar.permintaan_id')
            ->select(DB::raw("to_char(permintaan_barang_keluar.created_at, 'YYYY-MM') as month"), DB::raw('SUM(detail_permintaan_bk.jumlah) as count'))          
            ->whereBetween('tanggal', [Carbon::now()->subMonths(5)->startOfMonth(), Carbon::now()->endOfMonth()])
            ->groupBy('month')
            ->orderBy('month', 'DESC')
            ->get()
            ->keyBy('month');

        // Mengambil total untuk setiap kategori
        $total_barang = DB::table('barang')->count();
        $total_barang_masuk = DB::table('detail_barang_masuk')->count();
        $total_barang_keluar = DB::table('serial_number_permintaan')->count();
        $total_permintaan = DB::table('permintaan_barang_keluar')->count();

        // Menyusun array hasil untuk setiap kategori
        $result = [
            'dates' => $dates,
            'counts_barang' => $cumulativeBarang,
            'counts_barang_masuk' => $dates->map(function ($date) use ($counts_barang_masuk) {
                return $counts_barang_masuk->get($date)->count ?? 0;
            }),
            'counts_barang_keluar' => $dates->map(function ($date) use ($counts_barang_keluar) {
                return $counts_barang_keluar->get($date)->count ?? 0;
            }),
            'counts_permintaan' => $dates->map(function ($date) use ($counts_permintaan) {
                return $counts_permintaan->get($date)->count ?? 0;
            }),
            'counts_req_rejected' => $dates->map(function ($date) use ($counts_req_rejected) {
                return $counts_req_rejected->get($date)->count ?? 0;
            }),
            'counts_req_approved' => $dates->map(function ($date) use ($counts_req_approved) {
                return $counts_req_approved->get($date)->count ?? 0;
            }),
            'counts_req_pending' => $dates->map(function ($date) use ($counts_req_pending) {
                return $counts_req_pending->get($date)->count ?? 0;
            }),
            'months' => $months,
            'counts_barang_masuk_6months' => $months->map(function ($month) use ($counts_barang_masuk_6months) {
                return $counts_barang_masuk_6months->get($month)->count ?? 0;
            }),
            'counts_barang_keluar_6months' => $months->map(function ($month) use ($counts_barang_keluar_6months) {
                return $counts_barang_keluar_6months->get($month)->count ?? 0;
            }),
            'total_barang' => $total_barang,
            'total_barang_masuk' => $total_barang_masuk,
            'total_barang_keluar' => $total_barang_keluar,
            'total_permintaan' => $total_permintaan,
            'req_rejected' => DB::table('permintaan_barang_keluar')->where('status', 'Rejected')->where('created_by', auth()->id())->count(),            
            'req_approved' => DB::table('permintaan_barang_keluar')->where('status', 'Approved')->where('created_by', auth()->id())->count(),
            'req_pending' => DB::table('permintaan_barang_keluar')->where('status', 'Pending')->where('created_by', auth()->id())->count(),
        ];

        if ($request->user()->can('item request.viewAll')) {
            $result['req_rejected'] = DB::table('permintaan_barang_keluar')->where('status', 'Rejected')->count();
            $result['req_approved'] = DB::table('permintaan_barang_keluar')->where('status', 'Approved')->count();
            $result['req_pending'] = DB::table('permintaan_barang_keluar')->where('status', 'Pending')->count();
        }
        
        // Mengirim data sebagai JSON
        return response()->json($result);
    }

    public function getDailyActivity(Request $request)
    {
        $activities = [];

        // Ambil barang keluar
        $barangKeluar = DB::table('detail_permintaan_bk')
            ->join('permintaan_barang_keluar', 'detail_permintaan_bk.permintaan_barang_keluar_id', '=', 'permintaan_barang_keluar.id')
            ->join('customer', 'permintaan_barang_keluar.customer_id', '=', 'customer.id')
            //->join('keperluan', 'permintaan_barang_keluar.keperluan_id', '=', 'keperluan.id')
            ->join('serial_number_permintaan', 'detail_permintaan_bk.id', '=', 'serial_number_permintaan.detail_permintaan_bk_id')
            ->join('serial_number', 'serial_number_permintaan.serial_number_id', '=', 'serial_number.id')
            ->join('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
            ->join('barang', 'barang_masuk.barang_id', '=', 'barang.id')
            ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
            ->join('supplier', 'barang.supplier_id', '=', 'supplier.id')
            ->whereDate('permintaan_barang_keluar.updated_at', today())
            ->when(!$request->user()->can('item request.viewAll'), function($query) {
                return $query->where('permintaan_barang_keluar.created_by', auth()->id());
            })
            ->select(
                'serial_number.serial_number', 
                'barang.nama as nama_barang', 
                'jenis_barang.nama as nama_jenis_barang', 
                'supplier.nama as nama_supplier',
                'customer.nama as nama_customer',
                //'keperluan.nama as nama_keperluan',
                'permintaan_barang_keluar.updated_at as tanggal_permintaan',
            )
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->tanggal_permintaan)->format('H:i');
            })
            ->map(function ($group) {
                $barangGrouped = [];
                foreach ($group as $item) {
                    $namaBarang = $item->nama_barang;
                    if (!isset($barangGrouped[$namaBarang])) {
                        $barangGrouped[$namaBarang] = [];
                    }
                    $barangGrouped[$namaBarang][] = $item->serial_number;
                }

                $description = ''. count($group) . ' Barang Keluar:';
                $details = [];
                foreach ($barangGrouped as $namaBarang => $serialNumbers) {
                    //$details[] = $namaBarang . ' (SN: ' . implode(', ', $serialNumbers) . ')';
                    $details[] = "\n- " . $namaBarang . ' (SN: ' . implode(', ', $serialNumbers) . ')';
                }
                return [
                    'time' => Carbon::parse($group->first()->tanggal_permintaan)->format('H:i'),
                    'badge_color' => 'bg-danger',
                    //'description' => $description . implode(', ', $details)
                    'description' => $description . implode('', $details)
                ];
            });      

        // Ambil permintaan barang keluar
        $permintaan = DB::table('permintaan_barang_keluar')
            ->join('customer', 'permintaan_barang_keluar.customer_id', '=', 'customer.id')
            ->join('keperluan', 'permintaan_barang_keluar.keperluan_id', '=', 'keperluan.id')
            ->whereDate('permintaan_barang_keluar.created_at', today())
            ->when(!$request->user()->can('item request.viewAll'), function($query) {
                return $query->where('permintaan_barang_keluar.created_by', auth()->id());
            })
            ->select('permintaan_barang_keluar.*', 'customer.nama as nama_customer', 'keperluan.nama as nama_keperluan')
            ->get()
            ->map(function ($item) {
                return [
                    'time' => Carbon::parse($item->created_at)->format('H:i'),
                    'badge_color' => 'bg-warning',
                    'description' => $item->jumlah . ' Permintaan Barang untuk ' . $item->nama_customer . ' dengan keperluan ' . $item->nama_keperluan,
                ];
            });
            
        // Ambil barang masuk
        $barangMasuk = DB::table('serial_number')
            ->join('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
            ->join('barang', 'barang_masuk.barang_id', '=', 'barang.id')
            ->whereDate('barang_masuk.created_at', today())
            ->when(!$request->user()->can('item request.viewAll'), function($query) {
                return $query->where('barang_masuk.created_by', auth()->id());
            })
            ->select('barang_masuk.created_at as bm_created_at', 'barang.nama', 'serial_number.serial_number')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->bm_created_at)->format('H:i');
            })
            ->map(function ($group) {
                $barangGrouped = [];
                foreach ($group as $item) {
                    $namaBarang = $item->nama;
                    if (!isset($barangGrouped[$namaBarang])) {
                        $barangGrouped[$namaBarang] = [];
                    }
                    $barangGrouped[$namaBarang][] = $item->serial_number;
                }


                $description = ''. count($group) . ' Barang Masuk:';
                $details = [];
                foreach ($barangGrouped as $namaBarang => $serialNumbers) {
                    $details[] = "\n- " . $namaBarang . ' (SN: ' . implode(', ', $serialNumbers) . ')';
                }
                return [
                    'time' => Carbon::parse($group->first()->bm_created_at)->format('H:i'),
                    'badge_color' => 'bg-success',
                    'description' => $description . implode('', $details)
                ];
            });        

        // // // // // // // // // // // // // // // // // // // // // // // //
            
        // Ambil data barang
        $dataBarang = DB::table('barang')
            ->whereDate('created_at', today())
            ->when(!$request->user()->can('item request.viewAll'), function($query) {
                return $query->where('created_by', auth()->id());
            })
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->created_at)->format('Y-m-d');
            })
            ->map(function ($group) {
                $barangList = [];
                foreach ($group as $item) {
                    $barangList[] = $item->nama;
                }
                
                return [
                    'time' => Carbon::parse($group->first()->created_at)->format('H:i'),
                    'badge_color' => 'bg-primary',
                    'description' => count($group) . ' Data Barang:' . "\n- " . implode("\n- ", $barangList)
                ];
            });

        // Menggabungkan semua aktivitas
        $activities = collect($activities)->merge($barangKeluar)
                                        ->merge($permintaan)
                                        ->merge($barangMasuk)
                                        ->merge($dataBarang);

        // Urutkan berdasarkan waktu
        $activities = $activities->sortByDesc('time')->values()->all();

        // Gabungkan aktivitas yang sama (berdasarkan waktu dan deskripsi)
        $groupedActivities = [];
        foreach ($activities as $activity) {
            $time = $activity['time'];
            $description = $activity['description'];

            // Cek apakah ini adalah Data Barang
            if (strpos($description, '100 Data Barang:') !== false) {
                $barangName = str_replace('1 Data Barang: ', '', $description);
                $key = $time . '-Data Barang';
                if (isset($groupedActivities[$key])) {
                    // Jika sudah ada di grup, tambahkan nama barang ke dalam list
                    $groupedActivities[$key]['barang_list'][] = $barangName;
                } else {
                    // Jika belum ada, buat entri baru
                    $groupedActivities[$key] = [
                        'time' => $time,
                        'badge_color' => $activity['badge_color'],
                        'barang_list' => [$barangName],
                    ];
                }
            } else {
                // Untuk aktivitas selain Data Barang
                $key = $time . '-' . $description;
                if (isset($groupedActivities[$key])) {
                    // Jika aktivitas dengan waktu dan deskripsi yang sama ada, tambahkan count
                    $groupedActivities[$key]['count'] += 1;
                } else {
                    // Jika belum ada, tambahkan sebagai aktivitas baru
                    $groupedActivities[$key] = [
                        'time' => $time,
                        'badge_color' => $activity['badge_color'],
                        'description' => $description,
                        'count' => 1
                    ];
                }
            }
        }

        $finalActivities = [];
        foreach ($groupedActivities as $activity) {
            if (isset($activity['barang_list'])) {
                // Jika ini adalah Data Barang, gabungkan nama barang
                $finalActivities[] = [
                    'time' => $activity['time'],
                    'badge_color' => $activity['badge_color'],
                    'description' => '' . count($activity['barang_list']) . ' Data Barang: ' . implode(', ', $activity['barang_list']),
                ];
            } else {
                // Jika ini adalah aktivitas lain, cek apakah perlu menambahkan jumlah
                if ($activity['count'] > 1) {
                    $description = '' . $activity['count'] . ' ' . ltrim($activity['description'], '1');
                } else {
                    $description = $activity['description'];
                }
                $finalActivities[] = [
                    'time' => $activity['time'],
                    'badge_color' => $activity['badge_color'],
                    'description' => $description,
                ];
            }
        }

        // Kembalikan hasil dalam format JSON
        return response()->json($finalActivities);
    }

}