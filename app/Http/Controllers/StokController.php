<?php

namespace App\Http\Controllers;

use App\Models\StokModel;
use App\Models\SupplierModel;
use App\Models\BarangModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class StokController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Stok Barang',
            'list' => ['Home', 'Stok']
        ];

        $page = (object) [
            'title' => 'Stok Barang dalam sistem'
        ];

        $activeMenu = 'stok';
        
        return view('stok.index', compact('breadcrumb', 'page', 'activeMenu'));
    }

    public function list()
    {
        $stok = StokModel::join('m_supplier', 't_stok.supplier_id', '=', 'm_supplier.supplier_id')
            ->join('m_barang', 't_stok.barang_id', '=', 'm_barang.barang_id')
            ->join('m_user', 't_stok.user_id', '=', 'm_user.user_id')
            ->select(
                't_stok.stok_id',
                'm_supplier.supplier_nama as supplier',
                'm_barang.barang-nama as barang',
                'm_user.username as user',
                't_stok.stok_tanggal',
                't_stok.stok_jumlah'
            );

        return DataTables::of($stok)
            ->addIndexColumn()
            ->addColumn('aksi', function ($stok) {
                return '<a href="'.url('/stok/'.$stok->stok_id.'/edit').'" class="btn btn-warning btn-sm">Edit</a> '
                    .'<form class="d-inline-block" method="POST" action="'.url('/stok/'.$stok->stok_id).'">'
                    .csrf_field().method_field('DELETE')
                    .'<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Yakin hapus data?\');">Hapus</button></form>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create()
    {
        $breadcrumb = (object) [
            'title' => 'Tambah Stok',
            'list' => ['Home', 'Stok Barang', 'Tambah']
        ];

        $page = (object) [
            'title' => 'Tambah stok baru'
        ];

        $supplier = SupplierModel::select('supplier_id', 'supplier_nama')->get();
        $barang = BarangModel::select('barang_id', 'barang-nama')->get();
        $user = UserModel::select('user_id', 'username')->get();

        $activeMenu = 'stok';
        
        return view('stok.create', compact('breadcrumb', 'page', 'supplier', 'barang', 'user', 'activeMenu'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:m_supplier,supplier_id',
            'barang_id' => 'required|exists:m_barang,barang_id',
            'user_id' => 'required|exists:m_user,user_id',
            'stok_jumlah' => 'required|integer|min:1'
        ]);
        
        StokModel::create([
            'supplier_id' => $request->supplier_id,
            'barang_id' => $request->barang_id,
            'user_id' => $request->user_id,
            'stok_tanggal' => now(), // Tetapkan stok_tanggal di sini
            'stok_jumlah' => $request->stok_jumlah,
        ]);
        
        return redirect('/stok')->with('success', 'Stok berhasil ditambahkan.');
        
    }

    public function show($id)
    {
        $stok = StokModel::join('m_supplier', 't_stok.supplier_id', '=', 'm_supplier.supplier_id')
            ->join('m_barang', 't_stok.barang_id', '=', 'm_barang.barang_id')
            ->join('m_user', 't_stok.user_id', '=', 'm_user.user_id')
            ->where('t_stok.stok_id', $id)
            ->select(
                't_stok.*',
                'm_supplier.supplier_nama as supplier',
                'm_barang.barang-nama as barang',
                'm_user.username as user'
            )->firstOrFail();

        $breadcrumb = (object) [
            'title' => 'Detail Stok',
            'list' => ['Home', 'Stok', 'Detail']
        ];

        $page = (object) [
            'title' => 'Detail stok'
        ];

        $activeMenu = 'stok';
        
        return view('stok.show', compact('breadcrumb', 'page', 'stok', 'activeMenu'));
    }

    public function edit($id)
    {
        $stok = StokModel::findOrFail($id);

        $breadcrumb = (object) [
            'title' => 'Edit Stok',
            'list' => ['Home', 'Stok', 'Edit']
        ];

        $page = (object) [
            'title' => 'Edit stok'
        ];

        $suppliers = SupplierModel::select('supplier_id', 'supplier_nama')->get();
        $barangs = BarangModel::select('barang_id', 'barang-nama')->get();
        $users = UserModel::select('user_id', 'username')->get();

        $activeMenu = 'stok';
        
        return view('stok.edit', compact('breadcrumb', 'page', 'stok', 'suppliers', 'barangs', 'users', 'activeMenu'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'required|exists:m_supplier,supplier_id',
            'barang_id' => 'required|exists:m_barang,barang_id',
            'user_id' => 'required|exists:m_user,user_id',
            'stok_tanggal' => 'required|date',
            'stok_jumlah' => 'required|integer|min:1'
        ]);

        StokModel::findOrFail($id)->update($request->only(['supplier_id', 'barang_id', 'user_id', 'stok_tanggal', 'stok_jumlah']));

        return redirect('/stok')->with('success', 'Stok berhasil diperbarui');
    }

    public function destroy($id)
    {
        StokModel::destroy($id);
        return redirect('/stok')->with('success', 'Stok berhasil dihapus');
    }
}
