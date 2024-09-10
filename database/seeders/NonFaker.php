<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Rayya RR',
                'email' => 'rayya@gmail.com',
                'password' => Hash::make('123123123'),
            ],
        ];

        DB::table('users')->insert($users);

        ///

        $supplier = [
            [
                'nama' => 'PT Abdul Jaya Abadi',
                'alamat' => 'Kebon Jeruk',
                'telepon' => '082188888888',
            ],
            [
                'nama' => 'PT Agung Sedayu',
                'alamat' => 'Setiabudi',
                'telepon' => '087899527745',
            ],
            [
                'nama' => 'PT Pintu Kemana Saja',
                'alamat' => 'Tanah Abang',
                'telepon' => '085377277497',
            ],
        ];

        DB::table('supplier')->insert($supplier);

        ///
        
        $jenisbarang = [
            [
                'nama' => 'Makanan',
            ],
            [
                'nama' => 'Minuman',
            ],
        ];

        DB::table('jenis_barang')->insert($jenisbarang);

        ///
        
        $statusbarang = [
            [
                'nama' => 'Baik',
            ],
            [
                'nama' => 'Rusak',
            ],
        ];

        DB::table('status_barang')->insert($statusbarang);

        ///
        
        $barang = [
            [
                'jenis_barang_id' => 1,
                'nama' => 'Dodol Garut',
                'jumlah' => 0,
            ],
            [
                'jenis_barang_id' => 2,
                'nama' => 'Le Minerale',
                'jumlah' => 0,
            ],
        ];

        DB::table('barang')->insert($barang);
    }
}
