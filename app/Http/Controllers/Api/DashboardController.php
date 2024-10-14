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

        // Mengambil total untuk setiap kategori
        $total_barang = DB::table('barang')->count();
        $total_barang_masuk = DB::table('barang_masuk')->count();
        $total_barang_keluar = DB::table('barang_keluar')->count();
        $total_permintaan = DB::table('permintaan_barang_keluar')->count();

        // Menyusun array hasil untuk setiap kategori
        $result = [
            'dates' => $dates,
            'counts_barang' => $dates->map(function ($date) use ($counts_barang) {
                return $counts_barang->get($date)->count ?? 0; // Mengambil count atau 0 jika tidak ada
            }),
            'counts_barang_masuk' => $dates->map(function ($date) use ($counts_barang_masuk) {
                return $counts_barang_masuk->get($date)->count ?? 0; // Mengambil count atau 0 jika tidak ada
            }),
            'counts_barang_keluar' => $dates->map(function ($date) use ($counts_barang_keluar) {
                return $counts_barang_keluar->get($date)->count ?? 0; // Mengambil count atau 0 jika tidak ada
            }),
            'counts_permintaan' => $dates->map(function ($date) use ($counts_permintaan) {
                return $counts_permintaan->get($date)->count ?? 0; // Mengambil count atau 0 jika tidak ada
            }),
            'total_barang' => $total_barang,
            'total_barang_masuk' => $total_barang_masuk,
            'total_barang_keluar' => $total_barang_keluar,
            'total_permintaan' => $total_permintaan,
        ];

        // Mengirim data sebagai JSON
        return response()->json($result);
    }
}