<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenjualanModel;
use App\Models\PenjualanDetailModel;


use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function show_api($id)
    {
        // Ambil data penjualan utama beserta user/kasir
        $penjualan = PenjualanModel::with('user') // relasi ke m_user
            ->where('penjualan_id', $id)
            ->first();

        // Jika data penjualan tidak ditemukan
        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => 'Data Penjualan tidak ditemukan.',
            ], 404);
        }

        // Ambil detail penjualan + data barang
        $detail_penjualan = PenjualanDetailModel::with('barang') // relasi ke m_barang
            ->where('penjualan_id', $id)
            ->get();

        // Struktur JSON yang rapi
        return response()->json([
            'success' => true,
            'message' => 'Detail transaksi ditemukan.',
            'data' => [
                'penjualan' => $penjualan,
                'detail' => $detail_penjualan,
            ]
        ], 200);
    }

}
