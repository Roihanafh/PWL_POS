<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\KategoriModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Barang',
            'list' => ['Home', 'Barang']
        ];

        $page = (object) [
            'title' => 'Barang dalam sistem'
        ];

        $activeMenu = 'barang';
        
        return view('barang.index', compact('breadcrumb', 'page', 'activeMenu'));
    }

    public function list()
    {
        $barang = DB::table('m_barang')
            ->select(
                'm_barang.barang_id',
                'm_barang.barang_kode',
                'm_barang.barang-nama',
                'm_barang.harga_beli',
                'm_barang.harga_jual',
                'm_kategori.kategori_nama as kategori'
            )
            ->join('m_kategori', 'm_kategori.kategori_id', '=', 'm_barang.kategori_id');

        return DataTables::of($barang)
            ->addIndexColumn()
            ->addColumn('aksi', function ($barang) {
                return '<a href="'.url('/barang/'.$barang->barang_id).'" class="btn btn-info btn-sm">Detail</a> '
                    .'<a href="'.url('/barang/'.$barang->barang_id.'/edit').'" class="btn btn-warning btn-sm">Edit</a> '
                    .'<form class="d-inline-block" method="POST" action="'.url('/barang/'.$barang->barang_id).'">'
                    .csrf_field().method_field('DELETE')
                    .'<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Yakin hapus data?\');">Hapus</button></form>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }



    public function create()
    {
        $kategori = KategoriModel::all(); // Ambil semua kategori untuk dropdown
        $breadcrumb = (object) [
            'title' => 'Tambah Barang',
            'list' => ['Home', 'Barang', 'Tambah']
        ];

        $page = (object) [
            'title' => 'Tambah barang baru'
        ];

        $activeMenu = 'barang';
        
        return view('barang.create', compact('breadcrumb', 'page','kategori', 'activeMenu'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barang_kode' => 'required|string|max:10|unique:m_barang,barang_kode',
            'barang-nama' => 'required|string|max:100',
            'harga_jual' => 'required|numeric',
            'harga_beli' => 'required|numeric',
            'kategori_id' => 'required|integer'
        ]);

        BarangModel::create($request->only(['barang_kode', 'barang-nama', 'harga_jual', 'harga_beli', 'kategori_id']));

        return redirect('/barang')->with('success', 'Barang berhasil ditambahkan');
    }

    public function show($id)
    {
        $barang = DB::table('m_barang')
            ->join('m_kategori', 'm_kategori.kategori_id', '=', 'm_barang.kategori_id')
            ->select(
                'm_barang.barang_id',
                'm_barang.barang_kode',
                'm_barang.barang-nama',
                'm_barang.harga_beli',
                'm_barang.harga_jual',
                'm_kategori.kategori_nama as kategori'
            )
            ->where('m_barang.barang_id', $id)
            ->first(); // atau firstOrFail()


        $breadcrumb = (object) [
            'title' => 'Detail Barang',
            'list' => ['Home', 'Barang', 'Detail']
        ];

        $page = (object) [
            'title' => 'Detail barang'
        ];

        $activeMenu = 'barang';
        
        return view('barang.show', compact('breadcrumb', 'page', 'barang', 'activeMenu'));
    }

    public function edit($id)
    {
        $barang = BarangModel::findOrFail($id);
        $kategori = KategoriModel::all(); // Ambil semua kategori untuk dropdown

        $breadcrumb = (object) [
            'title' => 'Edit Barang',
            'list' => ['Home', 'Barang', 'Edit']
        ];

        $page = (object) [
            'title' => 'Edit barang'
        ];

        $activeMenu = 'barang';
        
        return view('barang.edit', compact('breadcrumb', 'page', 'barang', 'kategori', 'activeMenu'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'barang_kode' => 'required|string|max:10|unique:m_barang,barang_kode,'.$id.',barang_id',
            'barang-nama' => 'required|string|max:100',
            'harga_jual' => 'required|numeric',
            'harga_jual' => 'required|numeric',
            'kategori_id'=> 'required|integer',
        ]);

        BarangModel::findOrFail($id)->update($request->only(['barang_kode', 'barang-nama', 'harga_jual', 'harga_beli', 'kategori_id']));

        return redirect('/barang')->with('success', 'Barang berhasil diperbarui');
    }

    public function destroy($id)
    {
        BarangModel::destroy($id);
        return redirect('/barang')->with('success', 'Barang berhasil dihapus');
    }
}
