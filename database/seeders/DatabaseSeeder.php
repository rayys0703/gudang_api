<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Gudang',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('12345678'),
            ],
            [
                'name' => 'Rayya RR',
                'email' => 'rayya@gmail.com',
                'password' => Hash::make('123123123'),
            ],
        ];

        DB::table('users')->insert($users);

        ///

        $faker = Faker::create('id_ID');

        $supplier = [];
        for ($i = 0; $i < 50; $i++) {
            $supplier[] = [
                'nama' => $faker->company,
                'alamat' => $faker->address,
                'telepon' => $faker->phoneNumber,
            ];
        }

        DB::table('supplier')->insert($supplier);

        ///

        $customer = [];
        for ($i = 0; $i < 10; $i++) {
            $customer[] = [
                'nama' => $faker->company,
                'alamat' => $faker->address,
                'telepon' => $faker->phoneNumber,
            ];
        }

        DB::table('customer')->insert($customer);

        ///
        
        $jenisbarang = [
            [
                'nama' => 'Makanan',
            ],
            [
                'nama' => 'Minuman',
            ],
            [
                'nama' => 'Elektronik',
            ],
            [
                'nama' => 'Alat Rumah Tangga',
            ],
        ];

        DB::table('jenis_barang')->insert($jenisbarang);

        ///
        
        $statusbarang = [
            [
                'nama' => 'Baik',
                'warna' => '#00FF00'
            ],
            [
                'nama' => 'Rusak',
                'warna' => '#FF0000'
            ],
        ];

        DB::table('status_barang')->insert($statusbarang);

        ///

        $keperluan = [
            [
                'nama' => 'Untuk Dipinjam',
            ],
            [
                'nama' => 'Untuk Dipasang',
            ],
        ];

        DB::table('keperluan')->insert($keperluan);

        ///
        
        $barang = [];
        $jenis_barang = ['makanan', 'minuman', 'elektronik', 'alat rumah tangga'];
        $nama_barang = [
            'makanan' => ['Nasi Goreng', 'Rendang', 'Sate', 'Gado-gado', 'Bakso', 'Mie Ayam', 'Soto', 'Pempek', 'Martabak', 'Nasi Uduk'],
            'minuman' => ['Es Teh', 'Es Jeruk', 'Kopi', 'Jus Alpukat', 'Es Cendol', 'Es Cincau', 'Wedang Jahe', 'Soda Gembira', 'Es Kelapa Muda'],
            'elektronik' => ['Televisi', 'Kulkas', 'Mesin Cuci', 'AC', 'Kipas Angin', 'Rice Cooker', 'Blender', 'Setrika', 'Microwave', 'Dispenser'],
            'alat rumah tangga' => ['Sapu', 'Pel', 'Ember', 'Keset', 'Rak Piring', 'Gelas', 'Sendok', 'Gunting', 'Pisau']
        ];

        for ($i = 0; $i < 15; $i++) {
            $jenis = $faker->randomElement($jenis_barang);
            $jenis_barang_id = array_search($jenis, $jenis_barang) + 1;
            $barang[] = [
                'jenis_barang_id' => $jenis_barang_id,
                'nama' => $faker->randomElement($nama_barang[$jenis]),
                //'jumlah' => 0,
                'supplier_id' => $faker->numberBetween(1, 20),
            ];
        }

        DB::table('barang')->insert($barang);

        ///

        $barang_masuk = [
            [
                'barang_id' => 1,
                'jumlah' => 3,
                'tanggal' => '2024-09-10',
                'created_at' => now(),
            ],
            [
                'barang_id' => 2,
                'jumlah' => 1,
                'tanggal' => '2024-09-9',
                'created_at' => now(),
            ],
        ];

        DB::table('barang_masuk')->insert($barang_masuk);

        ///

        $serial_number = [
            [
                'serial_number' => 100100,
                'barangmasuk_id' => 1,
                'created_at' => now(),
            ],
            [
                'serial_number' => 100200,
                'barangmasuk_id' => 1,
                'created_at' => now(),
            ],
            [
                'serial_number' => 100300,
                'barangmasuk_id' => 1,
                'created_at' => now(),
            ],
            [
                'serial_number' => 200100,
                'barangmasuk_id' => 2,
                'created_at' => now(),
            ],
        ];

        DB::table('serial_number')->insert($serial_number);

        ///

        $detail_barang_masuk = [
            [
                'barangmasuk_id' => 1,
                'serial_number_id' => 1,
                'status_barang_id' => 1,
                'created_at' => now(),
            ],
            [
                'barangmasuk_id' => 1,
                'serial_number_id' => 2,
                'status_barang_id' => 1,
                'created_at' => now(),
            ],
            [
                'barangmasuk_id' => 1,
                'serial_number_id' => 3,
                'status_barang_id' => 2,
                'created_at' => now(),
            ],
            [
                'barangmasuk_id' => 2,
                'serial_number_id' => 4,
                'status_barang_id' => 1,
                'created_at' => now(),
            ],
        ];

        DB::table('detail_barang_masuk')->insert($detail_barang_masuk);
    }
}
