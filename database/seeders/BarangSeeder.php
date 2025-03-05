<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [];
        for ($i = 1; $i <= 3; $i++) { // 3 supplier
            for ($j = 1; $j <= 5; $j++) { // 5 barang per supplier
                $data[] = [
                    'kategori_id' => rand(1, 5),
                    'barang_kode' => 'BRG00' . (($i - 1) * 5 + $j),
                    'barang_nama' => 'Barang ' . (($i - 1) * 5 + $j),
                    'harga_beli' => rand(10000, 50000),
                    'harga_jual' => rand(60000, 100000),
                ];
            }
        }
        DB::table('m_barang')->insert($data);
    }
}
