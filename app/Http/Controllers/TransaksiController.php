<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TransaksiController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Transaksi',
            'list' => ['Home', 'Transaksi']
        ];

        $page = (object) [
            'title' => 'Data Transaksi'
        ];

        $activeMenu = 'transaksi';

        return view('transaksi.index', compact('breadcrumb', 'page', 'activeMenu'));
    }

    public function list()
    {
        $penjualan = DB::table('t_penjualan')
            ->join('m_user', 'm_user.user_id', '=', 't_penjualan.user_id')
            ->select(
                't_penjualan.penjualan_id',
                'm_user.username as user',
                't_penjualan.pembeli',
                't_penjualan.penjualan_kode',
                't_penjualan.penjualan_tanggal'
            );

        return DataTables::of($penjualan)
            ->addIndexColumn()
            ->addColumn('aksi', function ($penjualan) {
                return '<a href="'.url('/transaksi/'.$penjualan->penjualan_id).'" class="btn btn-info btn-sm">Detail</a>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show($id)
    {
        $penjualan = DB::table('t_penjualan')
            ->join('m_user', 'm_user.user_id', '=', 't_penjualan.user_id')
            ->select(
                't_penjualan.penjualan_id',
                'm_user.username as username',
                't_penjualan.pembeli',
                't_penjualan.penjualan_kode',
                't_penjualan.penjualan_tanggal'
            )
            ->where('t_penjualan.penjualan_id', $id)
            ->first();

        $detail_penjualan = DB::table('t_penjualan_detail')
            ->join('m_barang', 'm_barang.barang_id', '=', 't_penjualan_detail.barang_id')
            ->select(
                't_penjualan_detail.detail_id',
                'm_barang.barang-nama',
                't_penjualan_detail.harga',
                't_penjualan_detail.jumlah'
            )
            ->where('t_penjualan_detail.penjualan_id', $id)
            ->get();

        $breadcrumb = (object) [
            'title' => 'Detail Transaksi',
            'list' => ['Home', 'Transaksi', 'Detail']
        ];

        $page = (object) [
            'title' => 'Detail Transaksi'
        ];

        $activeMenu = 'transaksi';

        return view('transaksi.show', compact('breadcrumb', 'page', 'penjualan', 'detail_penjualan', 'activeMenu'));
    }
}
