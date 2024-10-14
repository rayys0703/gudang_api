<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Barang;
use App\Models\SerialNumber;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SerialNumberController extends Controller
{

	public function index()
	{
		$data = DB::table('serial_number')
			->leftJoin('barang', 'serial_number.barangmasuk_id', '=', 'barang.id')
			->select('serial_number.id as serial_number_id', 'serial_number.serial_number as serial_number', 'serial_number.status as status_sn', 'barang.id as barang_id', 'barang.nama as nama_barang')
			->orderBy('serial_number.serial_number', 'asc')
			->get();
		
		return response()->json($data);
	}

	public function cekByBarang($id)
	{
		$data = DB::table('serial_number')
			->leftJoin('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
			->leftJoin('barang', 'barang_masuk.barang_id', '=', 'barang.id')
			->select('serial_number.*', 'barang.id as barang_id', 'barang.nama as nama_barang')
			->where('barang.id', $id)
			->orderBy('serial_number.serial_number', 'asc')
			->get();
		
		return response()->json($data);
	}

	public function cekBySN($id)
	{
		$data = DB::table('serial_number')
			->leftJoin('barang_masuk', 'serial_number.barangmasuk_id', '=', 'barang_masuk.id')
			->leftJoin('barang', 'barang_masuk.barang_id', '=', 'barang.id')
			->select('serial_number.*', 'barang.id as barang_id', 'barang.nama as nama_barang')
			->where('serial_number.serial_number', $id)
			->orderBy('serial_number.serial_number', 'asc')
			->get();
		
		return response()->json($data);
	}

	public function create()
	{
		$jenis_barang = DB::table('jenis_barang')->select('id', 'nama')->orderBy('nama', 'asc')->get();
		$barang = DB::table('barang')->select('id', 'nama')->get();
		return view('serialnumber.create', compact('jenis_barang','barang'));
	}

	public function getBarangByJenis($id)
	{
		$barang = DB::table('barang')->where('jenis_barang_id', $id)->orderBy('nama', 'asc')->get();
		return response()->json($barang);
	}

	public function store(Request $request): RedirectResponse
	{
		$request->validate([
			'barangmasuk_id' => 'required|numeric',
			'serial_number' => 'required|numeric',
		], [
			'barangmasuk_id.required' => 'Barang harus dipilih.',
			'barangmasuk_id.numeric' => 'Barang harus dipilih.',
			'serial_number.required' => 'SN harus diisi.',
			'serial_number.numeric' => 'SN harus berupa angka.',
		]);
		
		$data = SerialNumber::create([
			'serial_number' => $request->serial_number,
			'barangmasuk_id' => $request->barangmasuk_id,
		]);

		return redirect('/serialnumber')->with('success', 'Anda berhasil menambahkan data!');
	}

	public function edit($id)
	{
		$barang = DB::table('barang')->select('id', 'nama')->get();
		$data = Barang::find($id);
		return view('serialnumber.edit', compact('data','barang'));
	}

	public function update($id, Request $request): RedirectResponse
	{
		$request->validate([
			'barangmasuk_id' => 'required|numeric',
			'serial_number' => 'required|numeric',
		], [
			'barangmasuk_id.required' => 'Barang harus dipilih.',
			'barangmasuk_id.numeric' => 'Barang harus dipilih.',
			'serial_number.required' => 'SN harus diisi.',
			'serial_number.numeric' => 'SN harus berupa angka.',
		]);

		$data = SerialNumber::find($id);

		$data->barangmasuk_id = $request->barangmasuk_id;
		$data->serial_number = $request->serial_number;

		$data->save();

		return redirect('/serialnumber')->with('success', 'Anda berhasil memperbarui data!');
	}

	public function delete($id)
	{
		$data = SerialNumber::find($id);
		$data->delete();
		return redirect('/serialnumber')->with('success', 'Anda berhasil menghapus data!');
	}

	public function deleteSelected(Request $request)
	{
		$ids = $request->input('ids');
		foreach ($ids as $id) {
			$data = SerialNumber::find($id);
			if ($data) {
				$data->delete();
			}
		}
		return response()->json(['success' => 'Data berhasil dihapus']);
	}
}
