<?php

namespace App\Http\Controllers;

use App\Models\KategoriModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class KategoriController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Kategori Barang',
            'list' => ['Home', 'Kategori']
        ];

        $page = (object) [
            'title' => 'Kategori Barang dalam sistem'
        ];

        $activeMenu = 'kategori';
        
        return view('kategori.index', compact('breadcrumb', 'page', 'activeMenu'));
    }

    public function list()
    {
        $kategori = KategoriModel::select('kategori_id', 'kategori_kode', 'kategori_nama');
        
        return DataTables::of($kategori)
            ->addIndexColumn()
            ->addColumn('aksi', function ($kategori) {
                return '<a href="'.url('/kategori/'.$kategori->kategori_id).'" class="btn btn-info btn-sm">Detail</a> '
                    .'<a href="'.url('/kategori/'.$kategori->kategori_id.'/edit').'" class="btn btn-warning btn-sm">Edit</a> '
                    .'<form class="d-inline-block" method="POST" action="'.url('/kategori/'.$kategori->kategori_id).'">'
                    .csrf_field().method_field('DELETE')
                    .'<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Yakin hapus data?\');">Hapus</button></form>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create()
    {
        $breadcrumb = (object) [
            'title' => 'Tambah Kategori',
            'list' => ['Home', 'Kategori Barang', 'Tambah']
        ];

        $page = (object) [
            'title' => 'Tambah kategori baru'
        ];

        $activeMenu = 'kategori';
        
        return view('kategori.create', compact('breadcrumb', 'page', 'activeMenu'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_kode' => 'required|string|max:10|unique:m_kategori,kategori_kode',
            'kategori_nama' => 'required|string|max:100'
        ]);

        KategoriModel::create([
            'kategori_kode' => $request->kategori_kode, // Pastikan input ini ada di form
            'kategori_nama' => $request->kategori_nama,
        ]);

        return redirect('/kategori')->with('success', 'Kategori berhasil ditambahkan');
    }

    public function show($id)
    {
        $kategori = KategoriModel::findOrFail($id);

        $breadcrumb = (object) [
            'title' => 'Detail Kategori',
            'list' => ['Home', 'Kategori', 'Detail']
        ];

        $page = (object) [
            'title' => 'Detail kategori'
        ];

        $activeMenu = 'kategori';
        
        return view('kategori.show', compact('breadcrumb', 'page', 'kategori', 'activeMenu'));
    }

    public function edit($id)
    {
        $kategori = KategoriModel::findOrFail($id);

        $breadcrumb = (object) [
            'title' => 'Edit Kategori',
            'list' => ['Home', 'Kategori', 'Edit']
        ];

        $page = (object) [
            'title' => 'Edit kategori'
        ];

        $activeMenu = 'kategori';
        
        return view('kategori.edit', compact('breadcrumb', 'page', 'kategori', 'activeMenu'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kategori_kode' => 'required|string|max:10|unique:m_kategori,kategori_kode,'.$id.',kategori_id',
            'kategori_nama' => 'required|string|max:100'
        ]);

        KategoriModel::findOrFail($id)->update($request->only(['kategori_kode', 'kategori_nama']));

        return redirect('/kategori')->with('success', 'Kategori berhasil diperbarui');
    }

    public function destroy($id)
    {
        $level = KategoriModel::find($id);
        if (!$level) {
            return redirect('/kategori')->with('error', 'Data kategori tidak ditemukan');
        }

        try {
            KategoriModel::destroy($id);
            return redirect('/kategori')->with('success', 'Data kategori berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/kategori')->with('error', 'Data kategori gagal dihapus karena masih terkait dengan data lain');
        }
    }

    // public function destroy($id)
    // {
    //     KategoriModel::destroy($id);
    //     return redirect('/kategori')->with('success', 'Kategori berhasil dihapus');
    // }
    // public function index()
    // {
        // $data = [
        //     'kategori_kode'=>'SNK',
        //     'kategori_nama'=>'Snack/Makanan Ringan',
        //     'created_at'=>now()
        // ];
        // DB::table('m_kategori')->insert($data);
        // return 'insert data baru berhasil';
        
        // $row= DB::table("m_kategori")->where('kategori_kode','SNK')->update(['kategori_nama'=>'Camilan']);
        // return 'Update data berhasil. Jumlah data yang diupdate: '.$row.' baris';

        // $row=DB::table("m_kategori")->where("kategori_kode",'SNK')->delete();
        // return 'Delete data berhasil. Jumlah data yang dihapus: '.$row.' baris';

    //     $data=DB::table('m_kategori')->get();
    //     return view('kategori',['data'=>$data]);
    // }
}
