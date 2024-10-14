<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


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
        $counts_barang = DB::table('barang')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [Carbon::now()->subDays(6), Carbon::now()])
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get()
            ->keyBy('date');

        // Query untuk barang masuk 7 hari terakhir
        $counts_barang_masuk = DB::table('barang_masuk')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [Carbon::now()->subDays(6), Carbon::now()])
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get()
            ->keyBy('date');

        // Query untuk barang keluar 7 hari terakhir
        $counts_barang_keluar = DB::table('barang_keluar')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [Carbon::now()->subDays(6), Carbon::now()])
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get()
            ->keyBy('date');

        // Query untuk permintaan barang 7 hari terakhir
        $counts_permintaan = DB::table('permintaan_barang_keluar')
            ->select(DB::raw('DATE(tanggal_awal) as date'), DB::raw('COUNT(*) as count'))
            ->whereBetween('tanggal_awal', [Carbon::now()->subDays(6), Carbon::now()])
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
        $counts_barang_masuk_6months = DB::table('barang_masuk')
            ->select(DB::raw('DATE_FORMAT(tanggal, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
            ->whereBetween('tanggal', [Carbon::now()->subMonths(5)->startOfMonth(), Carbon::now()->endOfMonth()])
            ->groupBy('month')
            ->orderBy('month', 'DESC')
            ->get()
            ->keyBy('month');

        // Query untuk barang keluar 6 bulan terakhir
        $counts_barang_keluar_6months = DB::table('barang_keluar')
            ->select(DB::raw('DATE_FORMAT(tanggal, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
            ->whereBetween('tanggal', [Carbon::now()->subMonths(5)->startOfMonth(), Carbon::now()->endOfMonth()])
            ->groupBy('month')
            ->orderBy('month', 'DESC')
            ->get()
            ->keyBy('month');

        // Mengambil total untuk setiap kategori
        $total_barang = DB::table('barang')->count();
        $total_barang_masuk = DB::table('barang_masuk')->count();
        $total_barang_keluar = DB::table('barang_keluar')->count();
        $total_permintaan = DB::table('permintaan_barang_keluar')->count();

        // Menyusun array hasil untuk setiap kategori
        $result = [
            'dates' => $dates,
            'counts_barang' => $dates->map(function ($date) use ($counts_barang) {
                return $counts_barang->get($date)->count ?? 0;
            }),
            'counts_barang_masuk' => $dates->map(function ($date) use ($counts_barang_masuk) {
                return $counts_barang_masuk->get($date)->count ?? 0;
            }),
            'counts_barang_keluar' => $dates->map(function ($date) use ($counts_barang_keluar) {
                return $counts_barang_keluar->get($date)->count ?? 0;
            }),
            'counts_permintaan' => $dates->map(function ($date) use ($counts_permintaan) {
                return $counts_permintaan->get($date)->count ?? 0;
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
        ];

        // Mengirim data sebagai JSON
        return response()->json($result);
    }

    public function getDailyActivity()
    {
        $activities = [];

        // Ambil permintaan barang keluar
        $permintaan = DB::table('permintaan_barang_keluar')
            ->where('created_at', '>=', now()->subDay())
            ->get()
            ->map(function ($item) {
                return [
                    'time' => Carbon::parse($item->created_at)->format('H:i'),
                    'badge_color' => 'bg-primary',
                    'description' => '+1 Permintaan Barang Keluar'
                ];
            });

        // Ambil barang masuk
        $barangMasuk = DB::table('serial_number')
            ->join('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
            ->where('barang_masuk.created_at', '>=', now()->subDay())
            ->get()
            ->map(function ($item) {
                return [
                    'time' => Carbon::parse($item->created_at)->format('H:i'),
                    'badge_color' => 'bg-success',
                    'description' => '+1 Barang Masuk', // dengan SN: ' . $item->serial_number
                ];
            });

        // Ambil detail barang masuk
        $detailBarangMasuk = DB::table('detail_barang_masuk')
            ->join('barang_masuk', 'detail_barang_masuk.barangmasuk_id', '=', 'barang_masuk.id')
            ->join('barang', 'barang_masuk.barang_id', '=', 'barang.id')
            ->where('detail_barang_masuk.created_at', '>=', now()->subDay())
            ->select('detail_barang_masuk.*', 'barang.nama')
            ->get()
            ->map(function ($item) {
                return [
                    'time' => Carbon::parse($item->created_at)->format('H:i'),
                    'badge_color' => 'bg-warning',
                    'description' => '+1 Data Barang: ' . $item->nama
                ];
            });
        // Menggabungkan semua aktivitas
        $activities = collect($activities)->merge($permintaan)
                                          ->merge($barangMasuk)
                                          ->merge($detailBarangMasuk);

        // Urutkan berdasarkan waktu
        $activities = $activities->sortByDesc('time')->values()->all();

        return response()->json($activities);
    }
}