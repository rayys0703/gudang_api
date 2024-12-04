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
                'password' => Hash::make('admin'),
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
        $users = [];
        for ($i = 0; $i < 5; $i++) {
            $customerName = $faker->company;
            $customer[] = [
                'nama' => $customerName,
                'alamat' => $faker->address,
                'telepon' => $faker->phoneNumber,
            ];
        }

        DB::table('customer')->insert($customer);

        $customers = DB::table('customer')->get();
        foreach ($customers as $cust) {
            $users[] = [
                'name' => $cust->nama,
                'email' => $faker->email,
                'password' => Hash::make('customer123'),
                'customer_id' => $cust->id,
            ];
        }

        DB::table('users')->insert($users);        
        
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
            [
                'nama' => 'Peralatan Kantor',
            ],
            [
                'nama' => 'Pakaian',
            ],
            [
                'nama' => 'Olahraga',
            ],
            [
                'nama' => 'Kosmetik',
            ],
            [
                'nama' => 'Alat Laboratorium',
            ],
            [
                'nama' => 'Mainan',
            ],
            [
                'nama' => 'Buku',
            ],
            [
                'nama' => 'Perhiasan',
            ],
            [
                'nama' => 'Furnitur',
            ],
            [
                'nama' => 'Peralatan Dapur',
            ],
            [
                'nama' => 'Alat Musik',
            ],
            [
                'nama' => 'Kendaraan',
            ],
            [
                'nama' => 'Alat Pertukangan',
            ],
            [
                'nama' => 'Alat Berkebun',
            ]
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
                'nama' => 'Untuk Dipasang',
                'extend' => 0,
            ],
            [
                'nama' => 'Untuk Dipinjam',
                'extend' => 1,
            ],
        ];

        DB::table('keperluan')->insert($keperluan);

        ///
        
        $barang = [];
        $jenis_barang = ['makanan', 'minuman', 'elektronik', 'alat rumah tangga', 'peralatan kantor', 'pakaian', 'olahraga', 'kosmetik', 'alat laboratorium', 'mainan', 'buku', 'perhiasan', 'furnitur', 'peralatan dapur', 'alat musik', 'kendaraan', 'alat pertukangan', 'alat berkebun'];
        $nama_barang = [
            'makanan' => ['Ayam Geprek', 'Nasi Goreng', 'Rendang', 'Sate', 'Gado-gado', 'Bakso', 'Mie Ayam', 'Soto', 'Pempek', 'Seblak', 'Nasi Uduk', 'Sop Buntut', 'Nasi Kuning', 'Lontong Sayur', 'Ketoprak', 'Bubur Ayam', 'Nasi Campur', 'Sop Iga', 'Rawon', 'Gudeg'],
            'minuman' => ['Es Rempah Rempah', 'Es Teh', 'Es Jeruk', 'Kopi', 'Jus Alpukat', 'Es Cendol', 'Es Cincau', 'Wedang Jahe', 'Es Kelapa Muda', 'Teh Tarik', 'Bandrek', 'Sekoteng', 'Jus Mangga', 'Es Doger', 'Es Teler', 'Susu Kedelai', 'Es Campur', 'Cendol'],
            'elektronik' => ['Televisi', 'Kulkas LG 2 Pintu Minat Inbox', 'Mesin Cuci', 'AC', 'Kipas Angin', 'Rice Cooker', 'Blender', 'Setrika', 'Microwave', 'Dispenser', 'Laptop', 'Smartphone', 'Tablet', 'Speaker', 'Headphone', 'Printer', 'Scanner', 'Vacuum Cleaner', 'Hair Dryer'],
            'alat rumah tangga' => ['Sapu', 'Pel', 'Ember', 'Keset', 'Rak Piring', 'Gelas', 'Sendok', 'Gunting', 'Pisau', 'Panci', 'Wajan', 'Talenan', 'Garpu', 'Mangkuk', 'Toples', 'Serbet', 'Tempat Sampah', 'Sikat WC', 'Gayung', 'Rak Sepatu'],
            'peralatan kantor' => ['Pulpen', 'Pensil', 'Penghapus', 'Penggaris', 'Stapler', 'Clip', 'Sticky Notes', 'Buku Catatan', 'Map', 'Amplop', 'Kertas HVS', 'Tinta Printer', 'Kalkulator', 'Papan Tulis', 'Spidol', 'Gunting', 'Cutter', 'Lem', 'Pembolong Kertas', 'Tempat Pensil'],
            'pakaian' => ['Kemeja', 'Celana Jeans', 'Kaos', 'Jaket', 'Rok', 'Dress', 'Celana Pendek', 'Sweater', 'Jas', 'Topi', 'Sarung Tangan', 'Kaus Kaki', 'Dasi', 'Piyama'],
            'olahraga' => ['Bola Sepak', 'Raket Tenis', 'Bola Basket', 'Sepeda', 'Barbel', 'Matras Yoga', 'Sepatu Lari', 'Bola Voli', 'Shuttlecock', 'Treadmill', 'Skipping Rope', 'Sarung Tinju', 'Pelampung', 'Papan Selancar', 'Tongkat Golf', 'Bola Tenis', 'Helm Sepeda', 'Pelindung Lutut'],
            'kosmetik' => ['Lipstik', 'Bedak', 'Maskara', 'Krim Wajah', 'Parfum', 'Shampoo', 'Conditioner', 'Sabun Mandi', 'Deodoran', 'Pelembab', 'Eyeliner', 'Blush On', 'Foundation', 'Kuku Palsu', 'Penghilang Makeup', 'Masker Wajah', 'Serum Wajah', 'Sikat Makeup', 'Pensil Alis', 'Pewarna Rambut'],
            'alat laboratorium' => ['Mikroskop', 'Jarum Suntik', 'Tabung Reaksi', 'Gelas Ukur', 'Termometer', 'pH Meter', 'Sentrifugal', 'Inkubator', 'Oven Laboratorium', 'Pipet Tetes'],
            'mainan' => ['Boneka', 'Mobil Remote Control', 'Lego', 'Puzzle', 'Kartu UNO', 'Rubik', 'Yoyo', 'Action Figure', 'Bola Karet', 'Monopoli', 'Playdoh', 'Balon', 'Kelereng', 'Papan Catur', 'Scrabble', 'Tamiya', 'Gundam', 'Beyblade', 'Barbie', 'Hot Wheels'],
            'buku' => ['Novel', 'Kamus', 'Ensiklopedia', 'Komik', 'Majalah', 'Buku Resep', 'Buku Pelajaran', 'Buku Cerita Anak', 'Buku Motivasi', 'Buku Sejarah', 'Buku Biografi', 'Buku Sains', 'Buku Agama', 'Buku Hukum', 'Buku Ekonomi', 'Buku Psikologi', 'Buku Filsafat', 'Buku Sastra', 'Buku Teknologi', 'Buku Kesehatan'],
            'perhiasan' => ['Cincin', 'Kalung', 'Gelang', 'Anting', 'Bros', 'Jam Tangan', 'Mahkota'],
            'furnitur' => ['Sofa', 'Meja Makan', 'Lemari Pakaian', 'Tempat Tidur', 'Kursi', 'Rak Buku', 'Meja Kerja', 'Meja Kopi', 'Kursi Goyang', 'Lemari Sepatu', 'Meja Rias', 'Meja TV', 'Gantungan Baju'],
            'peralatan dapur' => ['Kompor', 'Oven', 'Mixer', 'Toaster', 'Juicer', 'Pemanggang Roti', 'Slow Cooker', 'Pemanas Air', 'Penggorengan', 'Panci Presto', 'Parutan', 'Saringan', 'Spatula', 'Sendok Sayur', 'Talenan'],
            'alat musik' => ['Gitar', 'Piano', 'Drum', 'Biola', 'Seruling', 'Harmonika', 'Saksofon', 'Ukulele', 'Keyboard', 'Terompet', 'Tamborin', 'Marakas', 'Angklung', 'Gamelan', 'Rebana', 'Suling', 'Harpa', 'Akordeon', 'Klarinet'],
            'kendaraan' => ['Mobil', 'Motor', 'Sepeda', 'Skuter', 'Truk', 'Bus', 'Perahu', 'Jet Ski', 'ATV', 'Skateboard', 'Kereta Api', 'Pesawat', 'Helikopter'],
            'alat pertukangan' => ['Palu', 'Obeng', 'Gergaji', 'Bor', 'Kunci Inggris', 'Meteran', 'Waterpass', 'Tang', 'Pahat', 'Amplas', 'Kuas Cat', 'Solder', 'Gerinda', 'Paku'],
            'alat berkebun' => ['Sekop', 'Cangkul', 'Gunting Tanaman', 'Selang Air', 'Pot Tanaman', 'Pupuk', 'Benih', 'Sarung Tangan Kebun', 'Sprayer', 'Gerobak Dorong', 'Penyiram Tanaman', 'Jaring Burung', 'Tali Rambat', 'Garpu Tanah', 'Pemotong Rumput'],
        ];

        $used_items = [];
        for ($i = 0; $i < 100; $i++) {
            $jenis = $faker->randomElement($jenis_barang);
            $jenis_barang_id = array_search($jenis, $jenis_barang) + 1;
            
            $available_items = array_diff($nama_barang[$jenis], $used_items);
            if (empty($available_items)) {
                continue;
            }
            
            $nama = $faker->randomElement($available_items);
            $used_items[] = $nama;
            
            $barang[] = [
                'jenis_barang_id' => $jenis_barang_id,
                'nama' => $nama,
                'supplier_id' => $faker->numberBetween(1, 20),
                'created_at' => $faker->dateTimeBetween('-7 days', 'now'),
            ];
        }

        DB::table('barang')->insert($barang);
        ///

        $barang_masuk = [
            [
                'barang_id' => 1,
                'jumlah' => 4,
                'tanggal' => $faker->dateTimeBetween('-3 days', 'now'),
                'created_at' => now(),
            ],
            [
                'barang_id' => 2,
                'jumlah' => 1,
                'tanggal' => $faker->dateTimeBetween('-5 days', 'now'),
                'created_at' => $faker->dateTimeBetween('-7 days', 'now'),
            ],
            [
                'barang_id' => 3,
                'jumlah' => 5,
                'tanggal' => now()->subDays(30),
                'created_at' => now()->subDays(30),            
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
                'serial_number' => 100400,
                'barangmasuk_id' => 1,
                'created_at' => now(),
            ],
            ///
            [
                'serial_number' => 200100,
                'barangmasuk_id' => 2,
                'created_at' => now(),
            ],
            ///
            [
                'serial_number' => 300100,
                'barangmasuk_id' => 3,
                'created_at' => now(),
            ],
            [
                'serial_number' => 300200,
                'barangmasuk_id' => 3,
                'created_at' => now(),
            ],
            [
                'serial_number' => 300300,
                'barangmasuk_id' => 3,
                'created_at' => now(),
            ],
            [
                'serial_number' => 300400,
                'barangmasuk_id' => 3,
                'created_at' => now(),
            ],
            [
                'serial_number' => 300500,
                'barangmasuk_id' => 3,
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
                'kelengkapan' => NULL,
                'created_at' => now(),
            ],
            [
                'barangmasuk_id' => 1,
                'serial_number_id' => 2,
                'status_barang_id' => 1,
                'kelengkapan' => NULL,
                'created_at' => now(),
            ],
            [
                'barangmasuk_id' => 1,
                'serial_number_id' => 3,
                'status_barang_id' => 2,
                'kelengkapan' => "Rusak",
                'created_at' => now(),
            ],
            [
                'barangmasuk_id' => 1,
                'serial_number_id' => 4,
                'status_barang_id' => 1,
                'kelengkapan' => NULL,
                'created_at' => now(),
            ],
            ///
            [
                'barangmasuk_id' => 2,
                'serial_number_id' => 5,
                'status_barang_id' => 1,
                'kelengkapan' => NULL,
                'created_at' => now(),
            ],
            ///
            [
                'barangmasuk_id' => 3,
                'serial_number_id' => 6,
                'status_barang_id' => 1,
                'kelengkapan' => NULL,
                'created_at' => now(),
            ],
            [
                'barangmasuk_id' => 3,
                'serial_number_id' => 7,
                'status_barang_id' => 1,
                'kelengkapan' => NULL,
                'created_at' => now(),
            ],
            [
                'barangmasuk_id' => 3,
                'serial_number_id' => 8,
                'status_barang_id' => 1,
                'kelengkapan' => NULL,
                'created_at' => now(),
            ],
            [
                'barangmasuk_id' => 3,
                'serial_number_id' => 9,
                'status_barang_id' => 2,
                'kelengkapan' => "Rusak",
                'created_at' => now(),
            ],
            [
                'barangmasuk_id' => 3,
                'serial_number_id' => 10,
                'status_barang_id' => 1,
                'kelengkapan' => NULL,
                'created_at' => now(),
            ],
        ];

        DB::table('detail_barang_masuk')->insert($detail_barang_masuk);

        ///

        $permintaan_barang_keluar = [
            [
                'customer_id' => 1,
                'keperluan_id' => 1,
                'jumlah' => 3,
                'tanggal_awal' => now(),
                'tanggal_akhir' => now()->addDays(10),
                'status' => "Pending",
                'alasan' => NULL,
                'created_at' => now(),
            ],
            [
                'customer_id' => 1,
                'keperluan_id' => 2,
                'jumlah' => 1,
                'tanggal_awal' => now(),
                'tanggal_akhir' => now()->addDays(10),
                'status' => "Rejected",
                'alasan' => "Karena tidak ada",
                'created_at' => $faker->dateTimeBetween('-7 days', 'now'),
            ],
        ];

        DB::table('permintaan_barang_keluar')->insert($permintaan_barang_keluar);

        ///

        $detail_permintaan_bk = [
            [
                'permintaan_barang_keluar_id' => 1,
                'barang_id' => 1,
                'jumlah' => 2,
            ],
            [
                'permintaan_barang_keluar_id' => 1,
                'barang_id' => 2,
                'jumlah' => 1,
            ],
            ///
            [
                'permintaan_barang_keluar_id' => 2,
                'barang_id' => 3,
                'jumlah' => 1,
            ],
        ];

        DB::table('detail_permintaan_bk')->insert($detail_permintaan_bk);

        $this->call(RolePermissionSeeder::class);
    }
}
