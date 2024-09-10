<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $total_supplier = DB::table('supplier')->count();
        $total_customer = DB::table('customer')->count();
        $total_barang = DB::table('barang')->count();
        $total_barang_masuk = DB::table('barang_masuk')->count();
        $total_barang_keluar = DB::table('barang_keluar')->count();

        return response()->json([
            'total_supplier' => $total_supplier,
            'total_customer' => $total_customer,
            'total_barang' => $total_barang,
            'total_barang_masuk' => $total_barang_masuk,
            'total_barang_keluar' => $total_barang_keluar,
        ]);
    }
}
